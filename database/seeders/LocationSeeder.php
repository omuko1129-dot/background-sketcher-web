<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 既存のユーザーを取得。存在しない場合のみ新規作成（重複エラーを防止）
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $spots = [
            [
                'title' => '東京タワー',
                'description' => '日本のシンボルタワー',
                'lat' => 35.658581,
                'lng' => 139.745433,
            ],
            [
                'title' => '通天閣',
                'description' => '大阪の象徴的な展望タワー',
                'lat' => 34.652499,
                'lng' => 135.506306,
            ],
            [
                'title' => '清水寺',
                'description' => '京都の歴史ある名所',
                'lat' => 34.994856,
                'lng' => 135.785046,
            ],
        ];

        foreach ($spots as $spot) {
            Location::create([
                'user_id' => $user->id,
                'title' => $spot['title'],
                'description' => $spot['description'],
                'location' => DB::raw("ST_GeomFromText('POINT({$spot['lng']} {$spot['lat']})', 4326)"),
            ]);
        }
    }
}
