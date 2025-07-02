<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Basic Company Info
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country');

            // Contact Info
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();

            // Legal/Billing Info (ca la Settings)
            $table->string('registration_number')->nullable(); // CUI/VAT
            $table->string('tax_number')->nullable(); // Nr. înregistrare fiscală
            $table->string('trade_register')->nullable(); // J40/12345/2024
            $table->boolean('vat_payer')->default(false); // Plătitor de TVA

            // Banking Info
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_swift')->nullable();

            // Package & Subscription
            $table->foreignId('package_id')->constrained()->onDelete('restrict');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->string('preferred_currency', 3)->default('USD');
            $table->timestamp('subscription_started_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial')->default(false);
            $table->timestamp('trial_ends_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Extra fields

            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'subscription_expires_at']);
            $table->index('registration_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
