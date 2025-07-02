<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_conformities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_response_id')->constrained()->onDelete('cascade');
            $table->text('descriere');
            $table->text('actiune_corectiva')->nullable();
            $table->enum('stare', ['open', 'in_progress', 'closed'])->default('open');
            $table->foreignId('user_responsabil_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('termen_limita')->nullable();
            $table->foreignId('follow_up_audit_id')->nullable()->constrained('audits')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_conformities');
    }
};
