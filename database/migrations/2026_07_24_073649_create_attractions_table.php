<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 景點名稱
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // 分類 ID (關聯 categories)
            $table->string('city'); // 縣市
            $table->decimal('rating', 3, 1)->default(5.0); // 評分 (預設 5.0)
            $table->text('image_url')->nullable(); // 圖片網址 (允許空白)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attractions');
    }
};
