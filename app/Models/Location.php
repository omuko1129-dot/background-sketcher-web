<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 位置情報を GeoJSON フォーマット等で取得・検索しやすくするためのスコープ
    public function scopeWithCoordinates($query)
    {
        return $query->select(
            'id',
            'user_id',
            'title',
            'description',
            'created_at',
            'updated_at',
            DB::raw('ST_X(location::geometry) as longitude'),
            DB::raw('ST_Y(location::geometry) as latitude')
        );
    }
}
