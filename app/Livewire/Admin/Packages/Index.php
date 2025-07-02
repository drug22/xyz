<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use App\Models\Settings;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($packageId)
    {
        $package = Package::findOrFail($packageId);
        $package->update(['is_active' => !$package->is_active]);

        Flux::toast(
            heading: 'Package Updated',
            text: "'{$package->name}' has been " . ($package->is_active ? 'activated' : 'deactivated') . '.',
            variant: 'success'
        );
    }

    public function updateCurrencyPrices($packageId)
    {
        $package = Package::findOrFail($packageId);
        $package->updateCurrencyPrices();

        Flux::toast(
            heading: 'Currencies Updated',
            text: "'{$package->name}' currency prices have been recalculated.",
            variant: 'success'
        );
    }

    public function deletePackage($packageId)
    {
        $package = Package::findOrFail($packageId);

        if ($package->companies()->count() > 0) {
            Flux::toast(
                heading: 'Cannot Delete',
                text: 'This package is used by companies and cannot be deleted.',
                variant: 'warning'
            );
            return;
        }

        $packageName = $package->name;
        $package->delete();

        Flux::toast(
            heading: 'Package Deleted',
            text: "'{$packageName}' has been permanently deleted.",
            variant: 'success'
        );
    }

    public function getCurrencyIcon()
    {
        $defaultCurrency = Settings::getDefaultCurrency();

        return match($defaultCurrency) {
            'USD' => 'currency-dollar',
            'EUR' => 'currency-euro',
            'GBP' => 'currency-pound',
            'RON' => 'fire',
            default => 'credit-card'
        };
    }

    public function render()
    {
        $query = Package::query()->withCount('companies');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', (bool) $this->statusFilter);
        }

        $packages = $query->latest()->paginate(10);
        $defaultCurrency = Settings::getDefaultCurrency();
        $supportedCurrencies = Settings::getSupportedCurrencies();
        $currencyIcon = $this->getCurrencyIcon();

        return view('livewire.admin.packages.index', [
            'packages' => $packages,
            'defaultCurrency' => $defaultCurrency,
            'supportedCurrencies' => $supportedCurrencies,
            'currencyIcon' => $currencyIcon,
        ])->layout('components.layouts.admin');
    }
}
