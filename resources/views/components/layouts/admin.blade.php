<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('admin.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Administration')" class="grid">
            <flux:navlist.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>
            <flux:navlist.item icon="building-office" :href="route('admin.companies.index')" :current="request()->routeIs('admin.companies.*')" wire:navigate>
                {{ __('Companies') }}
            </flux:navlist.item>
            <flux:navlist.item icon="shopping-bag" :href="route('admin.billing.orders.index')" :current="request()->routeIs('admin.billing.orders.*')" wire:navigate>
                {{ __('Orders') }}
            </flux:navlist.item>
            <flux:navlist.item icon="document-text" :href="route('admin.billing.invoices.index')" :current="request()->routeIs('admin.billing.invoices.*')" wire:navigate>
                {{ __('Invoices') }}
            </flux:navlist.item>
            <flux:navlist.item icon="clipboard-document-list" href="#" disabled>
                {{ __('Checklists') }}
            </flux:navlist.item>
            <flux:navlist.item icon="cube" :href="route('admin.packages.index')" :current="request()->routeIs('admin.packages.*')" wire:navigate>
                {{ __('Packages') }}
            </flux:navlist.item>

            <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                {{ __('Users') }}
            </flux:navlist.item>

            <flux:navlist.group expandable heading="Support" class="hidden lg:grid">
                <flux:navlist.item icon="lifebuoy" href="#" disabled>
                    {{ __('Support Tichets') }}
                </flux:navlist.item>
                <flux:navlist.item icon="envelope-open" href="#" disabled>
                    {{ __('Emails') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

    <flux:spacer />

    <!-- Flux Native Theme Switcher -->
    <flux:navlist.group expandable heading="Settings" class="hidden lg:grid">
        <flux:navlist.item icon="cog-6-tooth" :href="route('admin.settings.index')" :current="request()->routeIs('admin.settings.*')" wire:navigate>
            {{ __('App Settings') }}
        </flux:navlist.item>
        <flux:navlist.item icon="credit-card" :href="route('admin.billing.stripe')" :current="request()->routeIs('admin.billing.stripe.*')" wire:navigate>
            {{ __('Stripe Settings') }}
        </flux:navlist.item>
        <flux:navlist.item icon="key" :href="route('admin.api-settings.tokens')"  :current="request()->routeIs('admin.api-settings.tokens.*')" wire:navigate>
            {{ __('Api Tokens') }}
        </flux:navlist.item>
    </flux:navlist.group>
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="w-full">
        <flux:radio value="light" icon="sun"></flux:radio>
        <flux:radio value="dark" icon="moon"></flux:radio>
        <flux:radio value="system" icon="computer-desktop"></flux:radio>
    </flux:radio.group>
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    <flux:dropdown position="top" align="end">
        <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
        <flux:menu>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:main>
    {{ $slot }}
</flux:main>
@persist('toast')
<flux:toast />
@endpersist
@fluxScripts
</body>
</html>
