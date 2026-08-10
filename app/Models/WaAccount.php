<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'session_id',
        'status',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function pipelineStages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order', 'asc');
    }

    public function stageTriggers()
    {
        return $this->hasMany(StageTrigger::class);
    }

    public function ensureDefaultStages()
    {
        if ($this->pipelineStages()->count() === 0) {
            $s1 = PipelineStage::create(['wa_account_id' => $this->id, 'name' => 'Lead Masuk', 'order' => 1, 'color' => 'purple', 'is_default' => true]);
            $s2 = PipelineStage::create(['wa_account_id' => $this->id, 'name' => 'Meeting Call', 'order' => 2, 'color' => 'blue']);
            $s3 = PipelineStage::create(['wa_account_id' => $this->id, 'name' => 'Kirim Penawaran', 'order' => 3, 'color' => 'yellow']);
            $s4 = PipelineStage::create(['wa_account_id' => $this->id, 'name' => 'Deal', 'order' => 4, 'color' => 'green']);

            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s2->id, 'keyword' => 'meeting']);
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s2->id, 'keyword' => 'call']);
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s3->id, 'keyword' => 'penawaran']);
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s3->id, 'keyword' => 'proposal']);
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s4->id, 'keyword' => 'deal']);
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s4->id, 'keyword' => 'lunas']);
        } else {
            if (!$this->pipelineStages()->where('is_default', true)->exists()) {
                $firstStage = $this->pipelineStages()->first();
                if ($firstStage) {
                    $firstStage->is_default = true;
                    $firstStage->save();
                }
            }
        }
    }
}
