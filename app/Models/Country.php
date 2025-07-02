<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ViesValidationService;

class Country extends Model
{
    protected $fillable = [
        'code', 'name', 'name_official', 'vat_rate',
        'is_eu_member', 'vies_validation_required', 'currency_code',
        'continent', 'vat_rules', 'is_active'
    ];

    protected $casts = [
        'vat_rate' => 'decimal:2',
        'is_eu_member' => 'boolean',
        'vies_validation_required' => 'boolean',
        'is_active' => 'boolean',
        'vat_rules' => 'array',
    ];

    public function validateVatNumber($vatNumber)
    {
        if (!$this->vies_validation_required) {
            return ['valid' => true, 'note' => 'VIES validation not required'];
        }

        $viesService = app(ViesValidationService::class);
        return $viesService->validateVatNumber($this->code, $vatNumber);
    }

    public function shouldApplyReverseVat($sellerCountry, $isB2B = false, $vatNumber = null)
    {
        $seller = static::where('code', $sellerCountry)->first();

        if (!$seller || !$this->is_eu_member || !$seller->is_eu_member) {
            return false;
        }

        if ($sellerCountry === $this->code) {
            return false; // Same country
        }

        if (!$isB2B || !$vatNumber) {
            return false; // B2C or no VAT number
        }

        $validation = $this->validateVatNumber($vatNumber);
        return $validation['valid'] ?? false;
    }
}
