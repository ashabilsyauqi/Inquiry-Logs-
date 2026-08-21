<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLeadComparison extends Model
{
    protected $table = 'ai_lead_comparisons';

    protected $fillable = [
        'report_date',
        'real_stage_counts',
        'ai_stage_counts',
        'differences',
    ];

    protected $casts = [
        'real_stage_counts' => 'array',
        'ai_stage_counts' => 'array',
        'differences' => 'array',
        'report_date' => 'date',
    ];
}
