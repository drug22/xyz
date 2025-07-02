<x-layouts.admin :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Header -->
        <div>
            <flux:heading size="xl">Admin Dashboard</flux:heading>
            <flux:text class="mt-2">Welcome to Hazwatch360° Administration Panel</flux:text>
        </div>

        <!-- Stats Grid -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <!-- Total Companies -->
            <flux:card>
                <div class="relative z-10 p-6 h-full flex flex-col justify-center">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                            <flux:icon name="building-office" class="h-6 w-6" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ \App\Models\Company::count() }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Total Companies</flux:text>
                        </div>
                    </div>
                </div>
            </flux:card>
            <!-- Active Companies -->
            <flux:card>
                <div class="relative z-10 p-6 h-full flex flex-col justify-center">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                            <flux:icon name="check-circle" class="h-6 w-6" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ \App\Models\Company::where('is_active', true)->count() }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Active Companies</flux:text>
                        </div>
                    </div>
                </div>
            </flux:card>

            <!-- Total Users -->
            <flux:card>
                <div class="relative z-10 p-6 h-full flex flex-col justify-center">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-purple-500 flex items-center justify-center text-white">
                            <flux:icon name="users" class="h-6 w-6" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ \App\Models\User::count() }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Total Users</flux:text>
                        </div>
                    </div>
                </div>
            </flux:card>

            <!-- Checklists -->
            <flux:card>
                <div class="relative z-10 p-6 h-full flex flex-col justify-center">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-amber-500 flex items-center justify-center text-white">
                            <flux:icon name="clipboard-document-list" class="h-6 w-6" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ \App\Models\ChecklistTemplate::count() }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Checklists</flux:text>
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Chart Placeholder -->
            <flux:card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">Company Growth</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Companies registered over time</flux:text>
                    </div>
                    <div class="h-64 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                        <div class="text-center">
                            <flux:icon name="chart-bar" class="h-12 w-12 mx-auto mb-2" />
                            <flux:text>Chart coming soon</flux:text>
                        </div>
                    </div>
                </div>
            </flux:card>

            <!-- Recent Activity -->
            <flux:card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">Recent Activity</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Latest system activities</flux:text>
                    </div>

                    <div class="space-y-3">
                        @foreach(\App\Models\Company::latest()->take(5)->get() as $company)
                            <div class="flex items-center gap-3 py-2">
                                <flux:icon name="plus-circle" class="h-4 w-4 text-emerald-500 flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <flux:text size="sm" class="font-medium">Company "{{ $company->name }}" created</flux:text>
                                </div>
                                <flux:badge color="zinc" size="sm">
                                    {{ $company->created_at->diffForHumans() }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:card>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-4">
            <flux:heading size="lg">Quick Actions</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <flux:card class="group hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="building-office" class="h-6 w-6 text-blue-500" />
                            <flux:heading size="md">Companies</flux:heading>
                        </div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Manage companies and subscriptions</flux:text>
                        <flux:button :href="route('admin.companies.index')" wire:navigate variant="primary" class="w-full">
                            Go to Companies
                        </flux:button>
                    </div>
                </flux:card>

                <flux:card class="group hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="cube" class="h-6 w-6 text-purple-500" />
                            <flux:heading size="md">Packages</flux:heading>
                        </div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Manage pricing packages and features</flux:text>
                        <flux:button :href="route('admin.packages.index')" wire:navigate variant="primary" class="w-full">
                            Go to Packages
                        </flux:button>
                    </div>
                </flux:card>

                <flux:card class="group hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="users" class="h-6 w-6 text-emerald-500" />
                            <flux:heading size="md">Users</flux:heading>
                        </div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Oversee user accounts and permissions</flux:text>
                        <flux:button :href="route('admin.users.index')" wire:navigate variant="primary" class="w-full">
                            Go to Users
                        </flux:button>
                    </div>
                </flux:card>

                <flux:card class="group hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="cog-6-tooth" class="h-6 w-6 text-amber-500" />
                            <flux:heading size="md">Settings</flux:heading>
                        </div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Configure system settings and currencies</flux:text>
                        <flux:button :href="route('admin.settings.index')" wire:navigate variant="primary" class="w-full">
                            Go to Settings
                        </flux:button>
                    </div>
                </flux:card>
            </div>
        </div>

        <!-- System Status -->
        <flux:card>
            <div class="space-y-4">
                <flux:heading size="lg">System Status</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex items-center gap-3">
                        <flux:icon name="server" class="h-5 w-5 text-green-500" />
                        <div>
                            <flux:text size="sm" class="font-medium">Server Status</flux:text>
                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">Online</flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:icon name="currency-dollar" class="h-5 w-5 text-blue-500" />
                        <div>
                            <flux:text size="sm" class="font-medium">Exchange Rates</flux:text>
                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">
                                Updated {{ \App\Models\Settings::get('exchange_rates_updated_at') ? \Carbon\Carbon::parse(\App\Models\Settings::get('exchange_rates_updated_at'))->diffForHumans() : 'Never' }}
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:icon name="shield-check" class="h-5 w-5 text-purple-500" />
                        <div>
                            <flux:text size="sm" class="font-medium">Security</flux:text>
                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">Protected</flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:icon name="clock" class="h-5 w-5 text-amber-500" />
                        <div>
                            <flux:text size="sm" class="font-medium">Scheduler</flux:text>
                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">Running</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts.admin>
