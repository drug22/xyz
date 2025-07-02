<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin (nu are companie)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@hazwatch360.com',
            'password' => Hash::make('password'),
            'company_id' => null,
            'is_owner' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Owners pentru fiecare companie
        $companies = Company::all();
        foreach ($companies as $company) {
            $owner = User::create([
                'name' => $company->name . ' Owner',
                'email' => 'owner@' . str_replace(' ', '', strtolower($company->name)) . '.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'is_owner' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $owner->assignRole('owner');

            // Manager pentru fiecare companie
            $manager = User::create([
                'name' => $company->name . ' Manager',
                'email' => 'manager@' . str_replace(' ', '', strtolower($company->name)) . '.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'is_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $manager->assignRole('manager');

            // Inspector pentru fiecare companie
            $inspector = User::create([
                'name' => $company->name . ' Inspector',
                'email' => 'inspector@' . str_replace(' ', '', strtolower($company->name)) . '.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'is_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $inspector->assignRole('inspector');

            // User pentru fiecare companie
            $user = User::create([
                'name' => $company->name . ' User',
                'email' => 'user@' . str_replace(' ', '', strtolower($company->name)) . '.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'is_owner' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('user');
        }

        $this->command->info('Users seeded successfully');
    }
}
