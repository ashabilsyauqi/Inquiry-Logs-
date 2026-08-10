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
    ];

    public function waAccount()
    {
        return $this->belongsTo(WaAccount::class);
    }
}

