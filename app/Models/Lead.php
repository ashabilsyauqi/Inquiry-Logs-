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
}

