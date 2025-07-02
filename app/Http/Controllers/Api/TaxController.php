<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\TaxCalculationService;
use App\Services\ViesValidationService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'customer_country' => 'required|exists:countries,code',
            'is_business' => 'boolean',
            'customer_vat_number' => 'nullable|string',
        ]);

        try {
            $taxService = app(TaxCalculationService::class);
            $businessCountry = \App\Models\Settings::get('company_country', 'GB');

            $taxCalculation = $taxService->calculateTax(
                $businessCountry,
                $validated['customer_country'],
                $validated['amount'],
                $validated['is_business'] ?? false,
                $validated['customer_vat_number'] ?? null
            );

            return response()->json([
                'success' => true,
                'tax_calculation' => $taxCalculation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Tax calculation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function validateVat(Request $request)
    {
        $validated = $request->validate([
            'vat_number' => 'required|string',
            'country_code' => 'required|exists:countries,code',
        ]);

        try {
            $viesService = app(ViesValidationService::class);
            $result = $viesService->validateVatNumber(
                $validated['country_code'],
                $validated['vat_number']
            );

            return response()->json([
                'success' => true,
                'vat_validation' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'VAT validation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function countries()
    {
        $countries = Country::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($country) {
                return [
                    'code' => $country->code,
                    'name' => $country->name,
                    'vat_rate' => $country->vat_rate,
                    'is_eu_member' => $country->is_eu_member,
                    'vies_validation_required' => $country->vies_validation_required,
                    'currency_code' => $country->currency_code,
                ];
            });

        return response()->json([
            'success' => true,
            'countries' => $countries
        ]);
    }
}
