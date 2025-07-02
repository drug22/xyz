<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for small teams getting started with safety management',
                'monthly_price' => 149.99, // RON (din Settings default currency)
                'yearly_price' => 1499.99,
                'max_users' => 5,
                'max_checklists' => 10,
                'features' => ['Basic checklists', 'User management', 'Email support'],
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'description' => 'Advanced features for growing businesses',
                'monthly_price' => 399.99, // RON
                'yearly_price' => 3999.99,
                'max_users' => 25,
                'max_checklists' => 50,
                'features' => ['Advanced checklists', 'Analytics', 'Priority support', 'Custom branding'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Complete solution for large organizations',
                'monthly_price' => 999.99, // RON
                'yearly_price' => 9999.99,
                'max_users' => null,
                'max_checklists' => null,
                'features' => ['Unlimited everything', 'API access', 'Dedicated support', 'Custom integrations'],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $packageData) {
            $package = Package::create($packageData);
            $package->updateCurrencyPrices(); // Auto-calculate cross-currency prices
        }
    }
}
