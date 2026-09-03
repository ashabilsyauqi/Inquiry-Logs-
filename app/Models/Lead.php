<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'stage',
        'ai_suggested_stage',
        'ai_concluded_stage',
        'ai_suggested_keyword',
        'ai_suggestion_reason',
        'ai_suggested_at',
        'notes',
        'priority',
        'wa_account_id',
        'assigned_user_id',
    ];

    public function waAccount()
    {
        return $this->belongsTo(WaAccount::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages()
    {
        return $this->hasMany(LeadMessage::class);
    }

    public function getTemperatureAttribute(): array
    {
        return \App\Services\LeadTemperatureService::getTemperature($this);
    }

    public function getFollowUpDataAttribute(): array
    {
        return \App\Services\LeadFollowUpService::getFollowUpData($this);
    }
}

