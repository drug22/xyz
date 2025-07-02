<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Token name/description
            $table->string('token_hash')->unique(); // Hashed token
            $table->string('token_preview', 12); // First 12 chars for display
            $table->json('abilities')->nullable(); // Permissions
            $table->json('allowed_ips')->nullable(); // IP restrictions
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['token_hash', 'is_active']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
