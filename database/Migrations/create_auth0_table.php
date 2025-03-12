<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth0_table', function (Blueprint $table) {
            $table->string('buyer_id')->primary();
            $table->boolean('is_active')->default(false);
            $table->boolean('auth_0_enabled')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('auth0_table');
    }
};