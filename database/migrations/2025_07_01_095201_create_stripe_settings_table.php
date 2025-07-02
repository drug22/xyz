<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_settings', function (Blueprint $table) {
            $table->id();

            // API Configuration
            $table->string('public_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->enum('mode', ['test', 'live'])->default('test');

            // Payment Configuration
            $table->boolean('auto_capture')->default(true);
            $table->json('supported_payment_methods')->nullable();
            $table->decimal('application_fee_percent', 5, 2)->default(0);

            // Tax Configuration
            $table->boolean('auto_tax_calculation')->default(true);
            $table->boolean('vies_validation_enabled')->default(true);
            $table->json('tax_exemption_countries')->nullable();

            // Invoice Configuration
            $table->string('invoice_prefix', 10)->default('INV');
            $table->integer('invoice_next_number')->default(1);
            $table->string('proforma_prefix', 10)->default('PRO');
            $table->integer('proforma_next_number')->default(1);

            // Webhook Configuration
            $table->json('webhook_events')->nullable();
            $table->string('webhook_endpoint_id')->nullable();

            // Status & Testing
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->json('test_results')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_settings');
    }
};
