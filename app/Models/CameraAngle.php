<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CameraAngle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'zoom',
        'pitch',
        'bearing',
        'center',
    ];

    protected $casts = [
        'center' => 'array',
    ];
}
