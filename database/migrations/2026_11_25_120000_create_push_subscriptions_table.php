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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('endpoint');
            $table->string('endpoint_hash', 64)->index();
            $table->string('public_key', 255)->nullable(); // p256dh key
            $table->string('auth_token', 255)->nullable(); // auth key
            $table->string('content_encoding', 30)->default('aesgcm');
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            // Index composite for lookup and uniqueness per user
            $table->unique(['user_id', 'endpoint_hash'], 'user_push_sub_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
