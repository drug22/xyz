<?php

namespace App\Services;

use App\Models\Country;

class TaxCalculationService
{
    public function calculateTax($sellerCountry, $buyerCountry, $amount, $isBusiness = false, $vatNumber = null)
    {
        // Validare input
        if (empty($sellerCountry) || empty($buyerCountry)) {
            return [
                'amount' => $amount,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'reverse_vat_applied' => false,
                'tax_note' => 'Missing country information - no tax applied'
            ];
        }

        $seller = Country::where('code', $sellerCountry)->first();
        $buyer = Country::where('code', $buyerCountry)->first();

        if (!$seller || !$buyer) {
            return [
                'amount' => $amount,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'reverse_vat_applied' => false,
                'tax_note' => "Invalid country codes: seller({$sellerCountry}), buyer({$buyerCountry})"
            ];
        }

        // 1. Same country - apply local VAT
        if ($sellerCountry === $buyerCountry) {
            $taxRate = $seller->vat_rate;
            $taxAmount = $amount * ($taxRate / 100);

            return [
                'amount' => $amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $amount + $taxAmount,
                'reverse_vat_applied' => false,
                'tax_note' => "Domestic sale - {$seller->name} VAT rate applied"
            ];
        }

        // 2. Both EU countries
        if ($seller->is_eu_member && $buyer->is_eu_member) {
            // 2a. B2B with valid VAT number - Reverse VAT (0%)
            if ($isBusiness && !empty($vatNumber)) {
                return [
                    'amount' => $amount,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $amount,
                    'reverse_vat_applied' => true,
                    'tax_note' => "EU B2B reverse VAT - customer liable for VAT in {$buyer->name}"
                ];
            }

            // 2b. B2C or B2B without VAT - apply seller's VAT
            $taxRate = $seller->vat_rate;
            $taxAmount = $amount * ($taxRate / 100);

            return [
                'amount' => $amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $amount + $taxAmount,
                'reverse_vat_applied' => false,
                'tax_note' => "EU B2C or invalid VAT - {$seller->name} VAT rate applied"
            ];
        }

        // 3. Export outside EU - No VAT (0%)
        if ($seller->is_eu_member && !$buyer->is_eu_member) {
            return [
                'amount' => $amount,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'reverse_vat_applied' => false,
                'tax_note' => "Export outside EU - no VAT applied"
            ];
        }

        // 4. Import from outside EU - apply buyer's VAT
        if (!$seller->is_eu_member && $buyer->is_eu_member) {
            $taxRate = $buyer->vat_rate;
            $taxAmount = $amount * ($taxRate / 100);

            return [
                'amount' => $amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $amount + $taxAmount,
                'reverse_vat_applied' => false,
                'tax_note' => "Import to EU - {$buyer->name} VAT rate applied"
            ];
        }

        // 5. Neither EU - No VAT
        return [
            'amount' => $amount,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => $amount,
            'reverse_vat_applied' => false,
            'tax_note' => "Non-EU transaction - no VAT applied"
        ];
    }

    public function getVatExemptions()
    {
        return [
            'reverse_vat_countries' => Country::where('is_eu_member', true)
                ->where('vies_validation_required', true)
                ->pluck('code')
                ->toArray(),

            'zero_vat_exports' => Country::where('is_eu_member', false)
                ->pluck('code')
                ->toArray()
        ];
    }
}
