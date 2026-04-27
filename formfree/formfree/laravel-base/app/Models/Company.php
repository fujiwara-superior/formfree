<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $keyType      = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id', 'name', 'email', 'plan',
        'monthly_job_limit', 'stripe_customer_id', 'stripe_sub_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function conversionJobs()
    {
        return $this->hasMany(ConversionJob::class);
    }

    public function outputDefinitions()
    {
        return $this->hasMany(OutputDefinition::class);
    }

    // ─── ヘルパー ──────────────────────────────────────────
    public function isPaid(): bool
    {
        return in_array($this->plan, ['standard', 'pro']);
    }

    public function planLabel(): string
    {
        return match($this->plan) {
            'standard' => 'Standard',
            'pro'      => 'Pro',
            default    => '無料',
        };
    }
}
