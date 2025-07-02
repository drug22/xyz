<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('name');
            $table->string('name_official')->nullable();
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->boolean('is_eu_member')->default(false);
            $table->boolean('vies_validation_required')->default(false);
            $table->string('currency_code', 3)->nullable();
            $table->string('continent', 50)->nullable();
            $table->json('vat_rules')->nullable(); // Reguli speciale VAT
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['is_eu_member', 'vies_validation_required']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
