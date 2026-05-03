<?php

namespace App\Http\Controllers;

use App\Mail\ConversionCompletedMail;
use App\Services\PythonConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ConversionController extends Controller
{
    public function __construct(
        private readonly PythonConverterService $converter
    ) {}

    // ─── アップロード画面 ───────────────────────────────────────
    public function index()
    {
        $company    = auth()->user()->company;
        $usedCount  = $this->getMonthlyUsedCount($company->id);
        $definitions = DB::table('output_definitions')
            ->where('company_id', $company->id)
            ->orderByDesc('use_count')
            ->get();

        return view('conversion.index', compact('company', 'usedCount', 'definitions'));
    }

    // ─── PDFアップロード ────────────────────────────────────────
    public function upload(Request $request)
    {
        $request->validate([
            'pdf'                  => 'required|file|mimes:pdf|max:20480',
            'output_definition_id' => 'nullable|uuid',
            'columns'              => 'nullable|array',
            'columns.*.name'       => 'nullable|string|max:50',
            'columns.*.description'=> 'nullable|string|max:200',
            'csv_encoding'         => 'in:utf8,sjis',
            'save_definition'      => 'nullable',
            'definition_name'      => 'nullable|string|max:100',
        ]);

        $company = auth()->user()?->company;
        if (!$company) {
            return response()->json(['error' => 'ログインし直してください（会社情報が見つかりません）'], 401);
        }

        // ① 月次上限チェック
        $usedCount = $this->getMonthlyUsedCount($company->id);
        if ($usedCount >= $company->monthly_job_limit) {
            return response()->json([
                'error'     => '今月の変換上限（' . $company->monthly_job_limit . '件）に達しています。',
                'limit_reached' => true,
            ], 429);
        }

        // ② 列定義の解決
        $columns = $this->resolveColumns($request, $company->id);

        // ③ 出力定義の保存（オプション）
        $definitionId = $request->output_definition_id;
        if ($request->boolean('save_definition') && !$definitionId) {
            $definitionId = (string) Str::uuid();
            DB::table('output_definitions')->insert([
                'id'         => $definitionId,
                'company_id' => $company->id,
                'name'       => $request->definition_name,
                'columns'    => json_encode($columns),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ④ 使用カウントを更新
        if ($definitionId) {
            DB::table('output_definitions')
                ->where('id', $definitionId)
                ->increment('use_count');
        }

        // ⑤ SupabaseにPDFを保存（REST API 直接）
        $file        = $request->file('pdf');
        $jobId       = (string) Str::uuid();
        $storagePath = "pdfs/{$company->id}/{$jobId}.pdf";
        $pdfType     = $this->detectPdfType($file);

        $supabaseUrl = config('services.supabase.url');
        $supabaseKey = config('services.supabase.key');

        try {
            $pdfContent = file_get_contents($file->getRealPath());
            $uploadResponse = Http::withHeaders([
                'apikey'        => $supabaseKey,
                'Authorization' => "Bearer {$supabaseKey}",
                'Content-Type'  => 'application/octet-stream',
            ])->withBody($pdfContent, 'application/octet-stream')
              ->post("{$supabaseUrl}/storage/v1/object/pdfs/{$storagePath}");
        } catch (\Exception $e) {
            logger()->error('Supabase upload exception: ' . $e->getMessage());
            return response()->json(['error' => 'PDFのアップロード中に例外が発生しました: ' . $e->getMessage()], 500);
        }

        if (!$uploadResponse->successful()) {
            $body = $uploadResponse->body();
            logger()->error('Supabase upload failed: ' . $body);
            return response()->json([
                'error' => 'PDFのアップロードに失敗しました。(' . $uploadResponse->status() . ') ' . substr($body, 0, 100),
            ], 500);
        }

        // ⑥ conversion_jobsにレコード作成
        DB::table('conversion_jobs')->insert([
            'id'                   => $jobId,
            'company_id'           => $company->id,
            'user_id'              => auth()->id(),
            'output_definition_id' => $definitionId,
            'pdf_filename'         => $file->getClientOriginalName(),
            'pdf_storage_path'     => $storagePath,
            'pdf_type'             => $pdfType,
            'status'               => 'pending',
            'csv_encoding'         => $request->csv_encoding ?? 'sjis',
            'columns_snapshot'     => json_encode($columns),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // ⑦ Pythonサービスに変換依頼（非同期）
        $this->converter->requestConversion(
            jobId:       $jobId,
            companyId:   $company->id,
            storagePath: $storagePath,
            pdfType:     $pdfType,
            columns:     $columns,
            csvEncoding: $request->csv_encoding ?? 'sjis',
        );

        return response()->json([
            'job_id'  => $jobId,
            'message' => '変換処理を開始しました',
        ]);
    }

    // ─── ジョブのステータス確認（ポーリング用） ─────────────────
    public function status(string $jobId)
    {
        $job = DB::table('conversion_jobs')
            ->where('id', $jobId)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        if (!$job) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $response = [
            'status'    => $job->status,
            'row_count' => $job->row_count,
        ];

        if ($job->status === 'completed') {
            $rows = DB::table('conversion_rows')
                ->where('job_id', $jobId)
                ->orderBy('row_index')
                ->get()
                ->map(fn($r) => json_decode($r->data, true));

            $columns = json_decode($job->columns_snapshot, true);

            $response['rows']    = $rows;
            $response['columns'] = $columns;
        }

        if ($job->status === 'failed') {
            $response['error'] = $job->error_message;
        }

        return response()->json($response);
    }

    // ─── プレビュー画面 ─────────────────────────────────────────
    public function preview(string $jobId)
    {
        $job = DB::table('conversion_jobs')
            ->where('id', $jobId)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_if(!$job, 404);
        abort_unless($job->status === 'completed', 400, '変換が完了していません');

        $rows = DB::table('conversion_rows')
            ->where('job_id', $jobId)
            ->orderBy('row_index')
            ->get();

        $columns = json_decode($job->columns_snapshot, true);

        return view('conversion.preview', compact('job', 'rows', 'columns'));
    }

    // ─── 行データの修正 ─────────────────────────────────────────
    public function updateRow(Request $request, string $jobId, int $rowIndex)
    {
        $request->validate(['data' => 'required|array']);

        $job = DB::table('conversion_jobs')
            ->where('id', $jobId)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_if(!$job, 404);

        DB::table('conversion_rows')
            ->where('job_id', $jobId)
            ->where('row_index', $rowIndex)
            ->update([
                'data'      => json_encode($request->data),
                'is_edited' => true,
            ]);

        return response()->json(['ok' => true]);
    }

    // ─── CSVダウンロード ────────────────────────────────────────
    public function download(string $jobId)
    {
        $job = DB::table('conversion_jobs')
            ->where('id', $jobId)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_if(!$job || $job->status !== 'completed', 404);

        $rows    = DB::table('conversion_rows')
            ->where('job_id', $jobId)
            ->orderBy('row_index')
            ->get();

        $columns = json_decode($job->columns_snapshot, true);
        $colNames = array_column($columns, 'name');

        // CSV生成
        $csv  = implode(',', array_map(fn($c) => '"' . $c . '"', $colNames)) . "\n";
        foreach ($rows as $row) {
            $data = json_decode($row->data, true);
            $line = array_map(function ($col) use ($data) {
                $val = $data[$col] ?? '';
                return '"' . str_replace('"', '""', (string) $val) . '"';
            }, $colNames);
            $csv .= implode(',', $line) . "\n";
        }

        // 文字コード変換
        if ($job->csv_encoding === 'sjis') {
            $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        }

        $filename = pathinfo($job->pdf_filename, PATHINFO_FILENAME) . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=' . ($job->csv_encoding === 'sjis' ? 'Shift_JIS' : 'UTF-8'))
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ─── 変換履歴 ───────────────────────────────────────────────
    public function history(Request $request)
    {
        $company = auth()->user()->company;

        $query = DB::table('conversion_jobs')
            ->where('company_id', $company->id)
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->definition_id) {
            $query->where('output_definition_id', $request->definition_id);
        }

        $jobs        = $query->paginate(20);
        $definitions = DB::table('output_definitions')
            ->where('company_id', $company->id)
            ->get();

        return view('conversion.history', compact('jobs', 'definitions'));
    }

    // ─── Private Helpers ────────────────────────────────────────
    private function getMonthlyUsedCount(string $companyId): int
    {
        return DB::table('conversion_jobs')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', '!=', 'failed')
            ->count();
    }

    private function resolveColumns(Request $request, string $companyId): array
    {
        if ($request->output_definition_id) {
            $def = DB::table('output_definitions')
                ->where('id', $request->output_definition_id)
                ->where('company_id', $companyId)
                ->firstOrFail();
            return json_decode($def->columns, true);
        }

        $columns = $request->columns ?? [];
        return array_values(array_filter($columns, fn($col) =>
            !empty($col['name']) || !empty($col['description'])
        ));
    }

    private function detectPdfType($file): string
    {
        // pdfplumber相当の簡易判定：テキスト抽出できればtext、できなければscan
        // 実際の判定はPython側で行う（ここではデフォルトtextとして渡す）
        return 'text';
    }
}
