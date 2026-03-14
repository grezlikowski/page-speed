<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_speed_tests', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('strategy')->default('mobile');
            $table->unsignedTinyInteger('performance_score')->nullable();
            $table->unsignedTinyInteger('accessibility_score')->nullable();
            $table->unsignedTinyInteger('best_practices_score')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->json('metrics')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['url', 'strategy', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_speed_tests');
    }
};
