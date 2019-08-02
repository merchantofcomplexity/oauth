<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOAuth2Client extends Migration
{
    public function up(): void
    {
        Schema::create('oauth2_client', function (Blueprint $table) {
            $table->string('identifier',32)->primary();
            $table->string('secret',128);
            $table->uuid('identity_id')->index()->nullable();
            $table->string('app_name',100);
            $table->json('scopes')->nullable();
            $table->json('redirect_uris')->nullable();
            $table->json('grants')->nullable();

            $table->boolean('active')->default(0);

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth2_client');
    }
}
