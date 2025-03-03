<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id');
            $table->string('url');
            $table->timestamp('timestamp');
            $table->unsignedInteger('reviews_filled')->default(0);
            $table->unsignedInteger('images_attached')->default(0);
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
