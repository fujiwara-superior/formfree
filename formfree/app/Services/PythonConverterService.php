<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonConverterService
{
    private string $baseUrl;
    private string $secret;

    public function __construct()
    {
        $this->baseUrl = config('services.python_converter.url');
        $this->secret  = config('services.python_converter.secret');
    }

    public function requestConversion(
        string $jobId,
        string $companyId,
        string $storagePath,
        string $pdfType,
        array  $columns,
        string $csvEncoding
    ): void {
        try {
            Http::withHeaders([
                'X-Api-Secret' => $this->secret,
                'Content-Type' => 'application/json',
            ])
            ->timeout(10)
            ->post("{$this->baseUrl}/convert", [
                'job_id'           => $jobId,
                'company_id'       => $companyId,
                'pdf_storage_path' => $storagePath,
                'pdf_type'         => $pdfType,
                'columns'          => $columns,
                'csv_encoding'     => $csvEncoding,
            ]);
        } catch (\Exception $e) {
            // 即時レスポンスを返す設計のためエラーは記録のみ
            // RecoverStuckJobsコマンドが10分後に検出してリトライする
            Log::error('Python converter request failed', [
                'job_id' => $jobId,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public function checkHealth(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/health");
            return $response->ok();
        } catch (\Exception) {
            return false;
        }
    }
}
