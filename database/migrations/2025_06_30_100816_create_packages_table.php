<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Starter, Professional, Enterprise
            $table->text('description')->nullable();

            // PRICING (în default currency din Settings)
            $table->decimal('monthly_price', 8, 2)->default(0);
            $table->decimal('yearly_price', 8, 2)->default(0);

            // AUTO-CALCULATED currency prices (read-only, calculated from exchange rates)
            $table->json('monthly_currency_prices')->nullable();
            $table->json('yearly_currency_prices')->nullable();

            // LIMITS
            $table->integer('max_users')->nullable(); // null = unlimited
            $table->integer('max_checklists')->nullable(); // null = unlimited
            $table->integer('max_audits_per_month')->nullable(); // null = unlimited
            $table->integer('max_visible_audits')->nullable(); // null = unlimited

            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
