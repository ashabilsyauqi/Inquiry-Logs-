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
}
