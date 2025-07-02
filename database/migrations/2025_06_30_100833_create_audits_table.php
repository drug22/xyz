<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('checklist_template_id')->constrained()->onDelete('cascade');
            $table->timestamp('data_audit');
            $table->enum('stare', ['draft', 'completed', 'follow_up'])->default('draft');
            $table->foreignId('follow_up_of_audit_id')->nullable()->constrained('audits')->onDelete('set null');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
