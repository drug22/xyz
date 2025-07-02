<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::where('is_active', true)
            ->orderBy('monthly_price')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'monthly_price' => $package->monthly_price,
                    'yearly_price' => $package->yearly_price,
                    'currency' => 'GBP',
                    'features' => $package->features,
                    'limits' => $package->limits,
                ];
            });

        return response()->json([
            'success' => true,
            'packages' => $packages
        ]);
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Package not available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'package' => [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'monthly_price' => $package->monthly_price,
                'yearly_price' => $package->yearly_price,
                'currency' => 'GBP',
                'features' => $package->features,
                'limits' => $package->limits,
            ]
        ]);
    }
}
