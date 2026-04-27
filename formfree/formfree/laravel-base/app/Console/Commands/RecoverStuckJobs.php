<?php

namespace App\Console\Commands;

use App\Services\PythonConverterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoverStuckJobs extends Command
{
    protected $signature   = 'jobs:recover-stuck';
    protected $description = '10分以上processingのままのジョブを検出してリトライする';

    public function handle(PythonConverterService $converter): void
    {
        $stuckJobs = DB::table('conversion_jobs')
            ->where('status', 'processing')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->get();

        if ($stuckJobs->isEmpty()) {
            $this->info('スタックしたジョブはありませんでした');
            return;
        }

        foreach ($stuckJobs as $job) {
            Log::warning("Stuck job detected, retrying: {$job->id}");

            $columns = json_decode($job->columns_snapshot, true) ?? [];

            // pendingに戻してから再送信
            DB::table('conversion_jobs')
                ->where('id', $job->id)
                ->update(['status' => 'pending', 'updated_at' => now()]);

            $converter->requestConversion(
                jobId:       $job->id,
                companyId:   $job->company_id,
                storagePath: $job->pdf_storage_path,
                pdfType:     $job->pdf_type,
                columns:     $columns,
                csvEncoding: $job->csv_encoding,
            );

            $this->info("Retried job: {$job->id}");
        }

        $this->info("Recovered {$stuckJobs->count()} stuck jobs");
    }
}
