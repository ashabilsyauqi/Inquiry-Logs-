<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'wa_account_id',
        'pipeline_stage_id',
        'keyword',
    ];

    public function waAccount()
    {
        return $this->belongsTo(WaAccount::class);
    }

    public function pipelineStage()
    {
        return $this->belongsTo(PipelineStage::class);
    }
}
