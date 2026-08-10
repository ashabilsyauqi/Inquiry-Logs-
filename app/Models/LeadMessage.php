<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'sender',
        'message',
        'is_from_me',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
