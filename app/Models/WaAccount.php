<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'phone',
        'session_id',
        'status',
        'approval_status',
        'rejection_reason',
        'supervisor_id',
        'disconnect_email_enabled',
        'disconnect_email_interval',
        'disconnect_alert_emails',
        'last_disconnect_email_sent_at',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'brand_supervisors', 'wa_account_id', 'user_id')->withTimestamps();
    }

    public function csTeam()
    {
        return $this->hasMany(User::class, 'wa_account_id')->where('role', 'SALES_ADMIN');
    }

    public function isConnected(): bool
    {
        if ($this->status === 'CONNECTED') {
            return true;
        }
        $cs = $this->relationLoaded('csTeam') ? $this->csTeam : $this->csTeam()->get();
        return $cs && $cs->where('wa_status', 'CONNECTED')->isNotEmpty();
    }

    public function getActivePhone(): ?string
    {
        if ($this->phone) {
            return $this->phone;
        }
        $cs = $this->relationLoaded('csTeam') ? $this->csTeam : $this->csTeam()->get();
        $connectedCs = $cs ? $cs->where('wa_status', 'CONNECTED')->first() : null;
        return $connectedCs ? $connectedCs->wa_phone : null;
    }

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
            StageTrigger::create(['wa_account_id' => $this->id, 'pipeline_stage_id' => $s4->id, 'keyword' => 'transfer']);
        }
    }
}
