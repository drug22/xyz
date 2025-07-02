<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crearea permisiunilor
        $permissions = [
            // Admin permissions
            'admin.companies.view',
            'admin.companies.create',
            'admin.companies.edit',
            'admin.companies.delete',
            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.packages.view',
            'admin.packages.create',
            'admin.packages.edit',
            'admin.packages.delete',
            'admin.checklists.view',
            'admin.checklists.create',
            'admin.checklists.edit',
            'admin.checklists.delete',
            'admin.invoices.view',
            'admin.invoices.create',
            'admin.invoices.edit',

            // Company permissions
            'company.users.view',
            'company.users.create',
            'company.users.edit',
            'company.users.delete',
            'company.audits.view',
            'company.audits.create',
            'company.audits.edit',
            'company.invoices.view',
            'company.subscription.manage',

            // Audit permissions
            'audits.create',
            'audits.edit',
            'audits.view.own',
            'audits.view.company',
            'audits.conduct',
            'audits.follow-up',

            // Profile permissions
            'profile.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crearea rolurilor și asignarea permisiunilor
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $owner = Role::create(['name' => 'owner']);
        $owner->givePermissionTo([
            'company.users.view',
            'company.users.create',
            'company.users.edit',
            'company.users.delete',
            'company.audits.view',
            'company.audits.create',
            'company.audits.edit',
            'company.invoices.view',
            'company.subscription.manage',
            'audits.create',
            'audits.edit',
            'audits.view.own',
            'audits.view.company',
            'audits.conduct',
            'audits.follow-up',
            'profile.edit',
        ]);

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'company.audits.view',
            'company.audits.create',
            'audits.create',
            'audits.edit',
            'audits.view.own',
            'audits.view.company',
            'audits.conduct',
            'audits.follow-up',
            'profile.edit',
        ]);

        $inspector = Role::create(['name' => 'inspector']);
        $inspector->givePermissionTo([
            'audits.create',
            'audits.edit',
            'audits.view.own',
            'audits.conduct',
            'profile.edit',
        ]);

        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'audits.create',
            'audits.view.own',
            'audits.conduct',
            'profile.edit',
        ]);

        $this->command->info('Roles and permissions seeded successfully');
    }
}
