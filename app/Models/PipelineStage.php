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
}
