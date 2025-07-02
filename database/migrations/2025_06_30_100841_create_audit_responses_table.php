<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->enum('status_raspuns', ['compliant', 'non_compliant', 'not_applicable', 'fixed']);
            $table->text('comentarii')->nullable();
            $table->json('imagini_path')->nullable(); // Array de paths către imagini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_responses');
    }
};
