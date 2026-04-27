<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $company   = auth()->user()->company;
        $usedCount = DB::table('conversion_jobs')
            ->where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', '!=', 'failed')
            ->count();

        $successRate = $this->calcSuccessRate($company->id);

        $recentJobs = DB::table('conversion_jobs')
            ->where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $definitionCount = DB::table('output_definitions')
            ->where('company_id', $company->id)
            ->count();

        return view('dashboard.index', compact(
            'company', 'usedCount', 'successRate',
            'recentJobs', 'definitionCount'
        ));
    }

    private function calcSuccessRate(string $companyId): int
    {
        $total = DB::table('conversion_jobs')
            ->where('company_id', $companyId)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($total === 0) return 100;

        $success = DB::table('conversion_jobs')
            ->where('company_id', $companyId)
            ->whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->count();

        return (int) round($success / $total * 100);
    }
}
