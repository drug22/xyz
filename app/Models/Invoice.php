<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'type',
        'status',
        'order_id',
        'company_id',
        'created_by',
        'customer_name',
        'customer_email',
        'customer_country',
        'customer_address',
        'is_business',
        'customer_vat_number',
        'company_details',
        'base_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'currency',
        'reverse_vat_applied',
        'tax_note',
        'vat_number_validated',
        'vat_validation_result',
        'vat_validated_at',
        'package_details',
        'invoice_date',
        'due_date',
        'sent_at',
        'paid_at',
        'pdf_path',
        'pdf_generated_at',
        'notes',
        'terms',
        'metadata',
    ];

    protected $casts = [
        'customer_address' => 'array',
        'company_details' => 'array',
        'package_details' => 'array',
        'vat_validation_result' => 'array',
        'metadata' => 'array',
        'is_business' => 'boolean',
        'reverse_vat_applied' => 'boolean',
        'vat_number_validated' => 'boolean',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'vat_validated_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'base_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'customer_country', 'code');
    }

    // Scopes
    public function scopeProforma($query)
    {
        return $query->where('type', 'proforma');
    }

    public function scopeFinal($query)
    {
        return $query->where('type', 'final');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    // Helpers
    public function isProforma(): bool
    {
        return $this->type === 'proforma';
    }

    public function isFinal(): bool
    {
        return $this->type === 'final';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date < now();
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft']);
    }

    public function canDelete(): bool
    {
        return in_array($this->status, ['draft']) && !$this->sent_at;
    }

    public function canSend(): bool
    {
        return in_array($this->status, ['draft']) && !$this->sent_at;
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->is_business && isset($this->company_details['company_name'])) {
            return $this->company_details['company_name'];
        }
        return $this->customer_name;
    }
}
