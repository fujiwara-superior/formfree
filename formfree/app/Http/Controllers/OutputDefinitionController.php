<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OutputDefinitionController extends Controller
{
    // ─── 一覧 ───────────────────────────────────────────────
    public function index()
    {
        $company     = auth()->user()->company;
        $definitions = DB::table('output_definitions')
            ->where('company_id', $company->id)
            ->orderByDesc('use_count')
            ->get()
            ->map(function ($def) {
                $def->columns_decoded = json_decode($def->columns, true);
                return $def;
            });

        return view('definitions.index', compact('definitions', 'company'));
    }

    // ─── 作成 ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'columns'               => 'required|array|min:1|max:20',
            'columns.*.name'        => 'required|string|max:50',
            'columns.*.description' => 'required|string|max:200',
        ], [
            'name.required'                  => '定義名を入力してください',
            'columns.required'               => '列を1つ以上設定してください',
            'columns.*.name.required'        => '列名を入力してください',
            'columns.*.description.required' => '抽出ルールを入力してください',
        ]);

        $companyId = auth()->user()->company_id;

        // 同名チェック
        $exists = DB::table('output_definitions')
            ->where('company_id', $companyId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'この名前の定義はすでに存在します']);
        }

        DB::table('output_definitions')->insert([
            'id'         => (string) Str::uuid(),
            'company_id' => $companyId,
            'name'       => $request->name,
            'columns'    => json_encode($request->columns, JSON_UNESCAPED_UNICODE),
            'use_count'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('definitions.index')
            ->with('success', '出力定義「' . $request->name . '」を作成しました');
    }

    // ─── 更新 ───────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'columns'               => 'required|array|min:1|max:20',
            'columns.*.name'        => 'required|string|max:50',
            'columns.*.description' => 'required|string|max:200',
        ]);

        $companyId = auth()->user()->company_id;

        $def = DB::table('output_definitions')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        DB::table('output_definitions')
            ->where('id', $id)
            ->update([
                'name'       => $request->name,
                'columns'    => json_encode($request->columns, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    // ─── 削除 ───────────────────────────────────────────────
    public function destroy(string $id)
    {
        $companyId = auth()->user()->company_id;

        $def = DB::table('output_definitions')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // 使用中のジョブがある場合は定義IDをnullにして削除
        DB::table('conversion_jobs')
            ->where('output_definition_id', $id)
            ->update(['output_definition_id' => null]);

        DB::table('output_definitions')
            ->where('id', $id)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
