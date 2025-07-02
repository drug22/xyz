<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Invoice identifiers
            $table->string('invoice_number')->unique();
            $table->enum('type', ['proforma', 'final'])->default('proforma');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');

            // Related entities
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            // Customer information (copied from order for consistency)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_country', 2);
            $table->json('customer_address')->nullable();
            $table->boolean('is_business')->default(false);
            $table->string('customer_vat_number')->nullable();

            // Company information (for business customers)
            $table->json('company_details')->nullable(); // company_name, registration_number, address, phone

            // Financial details
            $table->decimal('base_amount', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->boolean('reverse_vat_applied')->default(false);
            $table->text('tax_note')->nullable();

            // VAT validation
            $table->boolean('vat_number_validated')->default(false);
            $table->json('vat_validation_result')->nullable();
            $table->timestamp('vat_validated_at')->nullable();

            // Package details (snapshot)
            $table->json('package_details'); // package name, description, billing cycle

            // Invoice dates
            $table->date('invoice_date');
            $table->date('due_date');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // PDF & File management
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'due_date']);
            $table->index(['customer_email']);
            $table->index(['invoice_date']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
