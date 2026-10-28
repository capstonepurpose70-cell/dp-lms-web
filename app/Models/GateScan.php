<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GateScan extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'result',
        'score',
        'device',
        'attendance_id',
        'scanned_at',
    ];

    protected $casts = [
        'score'      => 'float',
        'scanned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
