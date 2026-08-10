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
