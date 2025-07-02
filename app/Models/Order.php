<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ViesValidationService;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'package_id', 'billing_cycle',
        'customer_email', 'customer_name', 'customer_country', 'is_business', 'customer_vat_number',
        'customer_address', 'base_amount', 'tax_rate', 'tax_amount', 'total_amount', 'currency',
        'reverse_vat_applied', 'tax_note', 'vat_number_validated', 'vat_validation_result',
        'vat_validated_at', 'status', 'stripe_payment_intent_id', 'stripe_customer_id',
        'paid_at', 'proforma_invoice_number', 'proforma_invoice_path', 'final_invoice_number',
        'final_invoice_path', 'company_id', 'created_by', 'metadata'
    ];

    protected $casts = [
        'customer_address' => 'array',
        'vat_validation_result' => 'array',
        'metadata' => 'array',
        'is_business' => 'boolean',
        'reverse_vat_applied' => 'boolean',
        'vat_number_validated' => 'boolean',
        'base_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'vat_validated_at' => 'datetime',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerCountry()
    {
        return $this->belongsTo(Country::class, 'customer_country', 'code');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function proformaInvoice()
    {
        return $this->invoices()->where('type', 'proforma')->first();
    }

    public function finalInvoice()
    {
        return $this->invoices()->where('type', 'final')->first();
    }

    public function hasProformaInvoice(): bool
    {
        return $this->invoices()->where('type', 'proforma')->exists();
    }

    public function hasFinalInvoice(): bool
    {
        return $this->invoices()->where('type', 'final')->exists();
    }
    // Methods
    public function validateVatNumber()
    {
        if (!$this->is_business || !$this->customer_vat_number) {
            return ['valid' => false, 'note' => 'Not a business or no VAT number'];
        }

        $viesService = app(ViesValidationService::class);
        $result = $viesService->validateVatNumber($this->customer_country, $this->customer_vat_number);

        $this->update([
            'vat_number_validated' => $result['valid'],
            'vat_validation_result' => $result,
            'vat_validated_at' => now(),
        ]);

        return $result;
    }

    public function generateOrderNumber()
    {
        return 'ORD-' . now()->format('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isPaid()
    {
        return $this->status === 'paid' && $this->paid_at;
    }

    public function canRefund()
    {
        return $this->isPaid() && $this->stripe_payment_intent_id;
    }
    protected static function booted()
    {
        static::updated(function ($order) {
            // Auto-create proforma invoice when order becomes published
            if ($order->isDirty('status') && $order->status === 'published' && !$order->hasProformaInvoice()) {
                $invoiceService = app(\App\Services\InvoiceService::class);
                $invoiceService->createProformaFromOrder($order);
            }
        });
    }
}
