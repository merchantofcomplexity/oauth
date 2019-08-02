<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOAuth2RefreshToken extends Migration
{
    public function up(): void
    {
        Schema::create('oauth2_refresh_token', function (Blueprint $table) {
            $table->string('identifier',80)->primary();
            $table->string('access_token',80)->nullable();
            $table->boolean('revoked')->default(0);
            $table->dateTimeTz('expires_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth2_refresh_token');
    }
}
