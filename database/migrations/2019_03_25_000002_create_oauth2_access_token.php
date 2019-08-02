<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOAuth2AccessToken extends Migration
{
    public function up(): void
    {
        Schema::create('oauth2_access_token', function (Blueprint $table) {
            $table->string('identifier',80)->primary();
            $table->string('client_id',32)->index();
            $table->uuid('identity_id')->nullable();
            $table->json('scopes')->nullable();
            $table->boolean('revoked')->default(0);

            $table->dateTimeTz('expires_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth2_access_token');
    }
}
