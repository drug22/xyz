<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Dacă utilizatorul este autentificat
    if (auth()->check()) {
        $user = auth()->user();

        // Redirectionează în funcție de rol
        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('app.dashboard');
        }
    }

    // Dacă nu e autentificat, redirectionează la login
    return redirect()->route('login');
})->name('home');

// Autentificare
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');

    // Redirecționare după login în funcție de rol
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('app.dashboard');
        }
    })->name('dashboard');
});

// Rute ADMIN (doar super-admin)
Route::middleware(['auth', 'check.role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Companies CRUD
    Route::get('/companies', App\Livewire\Admin\Companies\Index::class)->name('companies.index');
    Route::get('/companies/create', App\Livewire\Admin\Companies\Create::class)->name('companies.create');
    Route::get('/companies/{company}/edit', App\Livewire\Admin\Companies\Edit::class)->name('companies.edit');

    // Users CRUD
    Route::get('/users', App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/users/create', App\Livewire\Admin\Users\Create::class)->name('users.create');
    Route::get('/users/{user}/edit', App\Livewire\Admin\Users\Edit::class)->name('users.edit');

    // Packages CRUD
    Route::get('/packages', App\Livewire\Admin\Packages\Index::class)->name('packages.index');
    Route::get('/packages/create', App\Livewire\Admin\Packages\Create::class)->name('packages.create');
    Route::get('/packages/{package}/edit', App\Livewire\Admin\Packages\Edit::class)->name('packages.edit');
    Route::get('/settings', App\Livewire\Admin\Settings\Index::class)->name('settings.index');

    //Stripe
    Route::get('/billing/stripe', App\Livewire\Admin\Billing\StripeSettings::class)->name('billing.stripe');

    //Orders
    Route::prefix('billing/orders')->name('billing.orders.')->group(function () {
        Route::get('/', App\Livewire\Admin\Billing\Orders\Index::class)->name('index');
        Route::get('/create', App\Livewire\Admin\Billing\Orders\Create::class)->name('create');
        Route::get('/{order}', App\Livewire\Admin\Billing\Orders\Show::class)->name('show');
        Route::get('/{order}/edit', \App\Livewire\Admin\Billing\Orders\Edit::class)->name('edit');
    });
    // Invoice routes
    Route::prefix('billing/invoices')->name('billing.invoices.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Billing\Invoices\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Admin\Billing\Invoices\Create::class)->name('create');
        Route::get('/{invoice}', \App\Livewire\Admin\Billing\Invoices\Show::class)->name('show');
        Route::get('/{invoice}/edit', \App\Livewire\Admin\Billing\Invoices\Edit::class)->name('edit');
    });

    //API
    Route::prefix('api-settings')->name('api-settings.')->group(function () {
        Route::get('/tokens', App\Livewire\Admin\ApiSettings\ApiTokens::class)->name('tokens');
    });

    Route::get('/invoices/{invoice}/pdf/download', [App\Http\Controllers\InvoicePdfController::class, 'download'])->name('billing.invoices.pdf.download');
    Route::get('/invoices/{invoice}/pdf/stream', [App\Http\Controllers\InvoicePdfController::class, 'stream'])->name('billing.invoices.pdf.stream');
});

// Rute APP (utilizatori normali)
Route::middleware(['auth', 'check.company'])->prefix('app')->name('app.')->group(function () {
    Route::get('/dashboard', function () {
        return view('app.dashboard');
    })->name('dashboard');

    // Aici vor fi adăugate rutele pentru utilizatori în ETAPA 3
});

require __DIR__.'/auth.php';
