<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Services\TaxCalculationService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
            'customer_country' => 'required|exists:countries,code',
            'is_business' => 'boolean',
            'customer_vat_number' => 'nullable|string',
        ]);

        // Creează order și calculează tax
        $package = Package::find($validated['package_id']);
        $baseAmount = $validated['billing_cycle'] === 'monthly'
            ? $package->monthly_price
            : $package->yearly_price;

        $taxService = app(TaxCalculationService::class);
        $businessCountry = \App\Models\Settings::get('company_country', 'GB');

        $taxCalculation = $taxService->calculateTax(
            $businessCountry,
            $validated['customer_country'],
            $baseAmount,
            $validated['is_business'] ?? false,
            $validated['customer_vat_number'] ?? null
        );

        $order = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
            'package_id' => $validated['package_id'],
            'billing_cycle' => $validated['billing_cycle'],
            'customer_email' => $validated['customer_email'],
            'customer_name' => $validated['customer_name'],
            'customer_country' => $validated['customer_country'],
            'is_business' => $validated['is_business'] ?? false,
            'customer_vat_number' => $validated['customer_vat_number'],
            'base_amount' => $baseAmount,
            'tax_rate' => $taxCalculation['tax_rate'],
            'tax_amount' => $taxCalculation['tax_amount'],
            'total_amount' => $taxCalculation['total_amount'],
            'currency' => 'USD',
            'reverse_vat_applied' => $taxCalculation['reverse_vat_applied'],
            'tax_note' => $taxCalculation['tax_note'],
            'status' => 'pending',
        ]);

        // Creează Stripe Payment Intent
        $stripeService = app(StripePaymentService::class);
        $paymentIntent = $stripeService->createPaymentIntent([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'buyer_country' => $order->customer_country,
            'is_b2b' => $order->is_business,
            'buyer_vat_number' => $order->customer_vat_number,
            'customer_email' => $order->customer_email,
            'customer_name' => $order->customer_name,
        ]);

        $order->update([
            'stripe_payment_intent_id' => $paymentIntent['payment_intent']->id,
        ]);

        return response()->json([
            'order' => $order,
            'payment_intent' => $paymentIntent,
        ]);
    }
}
