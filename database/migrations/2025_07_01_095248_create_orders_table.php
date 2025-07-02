<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // Package & Billing
            $table->foreignId('package_id')->constrained();
            $table->enum('billing_cycle', ['monthly', 'yearly']);

            // Customer Info
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('customer_country', 2); // Buyer country
            $table->boolean('is_business')->default(false);
            $table->string('customer_vat_number')->nullable();
            $table->json('customer_address')->nullable();

            // Pricing & Tax
            $table->decimal('base_amount', 10, 2); // Amount before tax
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // Final amount
            $table->string('currency', 3);
            $table->boolean('reverse_vat_applied')->default(false);
            $table->string('tax_note')->nullable();

            // VIES Validation
            $table->boolean('vat_number_validated')->default(false);
            $table->json('vat_validation_result')->nullable();
            $table->timestamp('vat_validated_at')->nullable();

            // Payment Status
            $table->enum('status', ['draft', 'pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded']);
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Invoices
            $table->string('proforma_invoice_number')->nullable();
            $table->string('proforma_invoice_path')->nullable();
            $table->string('final_invoice_number')->nullable();
            $table->string('final_invoice_path')->nullable();

            // Relations
            $table->foreignId('company_id')->nullable()->constrained();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            // Metadata
            $table->json('metadata')->nullable(); // Extra fields from API

            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('customer_email');
            $table->index('stripe_payment_intent_id');
            $table->index(['customer_country', 'is_business']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
