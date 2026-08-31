<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'wa_account_id',
        'name',
        'order',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function waAccount()
    {
        return $this->belongsTo(WaAccount::class);
    }

    public function triggers()
    {
        return $this->hasMany(StageTrigger::class);
    }

    public function getTemperatureAttribute(): array
    {
        if (\App\Services\LeadTemperatureService::isDeadStage($this->name)) {
            return \App\Services\LeadTemperatureService::getDeadLeadData($this->name);
        }

        $allStages = self::where('wa_account_id', $this->wa_account_id)->orderBy('order', 'asc')->get();
        $activeStages = $allStages->filter(fn($st) => !\App\Services\LeadTemperatureService::isDeadStage($st->name))->values();

        $totalActive = $activeStages->count();
        if ($totalActive === 0) {
            $totalActive = max(1, $allStages->count());
        }

        $index = $activeStages->search(fn($st) => $st->id === $this->id);
        if ($index === false) {
            $index = max(0, $this->order - 1);
        }

        return \App\Services\LeadTemperatureService::getStageTemperature($index, $totalActive, $this->name);
    }
}
