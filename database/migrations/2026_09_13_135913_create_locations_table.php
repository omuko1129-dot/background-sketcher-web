<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            // 登録したユーザーのID（ユーザー削除時に連動削除）
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // スポット名やピンのタイトル
            $table->string('title');
            // 詳細説明やメモ（任意入力）
            $table->text('description')->nullable();

            // 位置情報データ（Point型: 緯度・経度）
            // SRID: 4326 (WGS84: 世界測地系)
            $table->geometry('location', subtype: 'point', srid: 4326);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
