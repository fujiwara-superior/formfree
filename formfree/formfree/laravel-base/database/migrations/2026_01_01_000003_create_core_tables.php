<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 出力CSV定義
        Schema::create('output_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('name');
            $table->jsonb('columns'); // [{name, description}]
            $table->integer('use_count')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index('company_id');
        });

        // 変換ジョブ
        Schema::create('conversion_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('user_id');
            $table->uuid('output_definition_id')->nullable();
            $table->string('pdf_filename');
            $table->string('pdf_storage_path');
            $table->string('pdf_type')->default('text'); // text / scan
            $table->string('status')->default('pending'); // pending / processing / completed / failed
            $table->text('error_message')->nullable();
            $table->integer('row_count')->nullable();
            $table->string('csv_storage_path')->nullable();
            $table->string('csv_encoding')->default('sjis'); // utf8 / sjis
            $table->jsonb('columns_snapshot')->nullable(); // 変換時の列定義スナップショット
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['company_id', 'created_at']);
            $table->index('status');
        });

        // 変換行データ（プレビュー・修正用）
        Schema::create('conversion_rows', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('job_id');
            $table->integer('row_index');
            $table->jsonb('data'); // {列名: 値}
            $table->boolean('is_edited')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_id')->references('id')->on('conversion_jobs')->onDelete('cascade');
            $table->index(['job_id', 'row_index']);
        });

        // Webhook冪等性管理
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->string('stripe_event_id')->primary();
            $table->string('type');
            $table->timestamp('processed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('conversion_rows');
        Schema::dropIfExists('conversion_jobs');
        Schema::dropIfExists('output_definitions');
    }
};
