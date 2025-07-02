<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use App\Models\Package;
use App\Models\Settings;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $packageFilter = '';
    public $statusFilter = '';
    public $countryFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPackageFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($companyId)
    {
        $company = Company::findOrFail($companyId);
        $company->update(['is_active' => !$company->is_active]);

        Flux::toast(
            heading: 'Company Updated',
            text: "'{$company->name}' has been " . ($company->is_active ? 'activated' : 'deactivated') . '.',
            variant: 'success'
        );
    }

    public function extendTrial($companyId)
    {
        $company = Company::findOrFail($companyId);
        $company->update([
            'trial_ends_at' => now()->addDays(30),
            'subscription_expires_at' => now()->addDays(30)
        ]);

        Flux::toast(
            heading: 'Trial Extended',
            text: "'{$company->name}' trial has been extended by 30 days.",
            variant: 'success'
        );
    }

    public function deleteCompany($companyId)
    {
        $company = Company::findOrFail($companyId);

        if ($company->users()->count() > 0) {
            Flux::toast(
                heading: 'Cannot Delete',
                text: 'This company has users and cannot be deleted.',
                variant: 'warning'
            );
            return;
        }

        $companyName = $company->name;
        $company->delete();

        Flux::toast(
            heading: 'Company Deleted',
            text: "'{$companyName}' has been permanently deleted.",
            variant: 'success'
        );
    }

    public function render()
    {
        $query = Company::query()
            ->with(['package', 'users'])
            ->withCount('users');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('contact_email', 'like', '%' . $this->search . '%')
                    ->orWhere('registration_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->packageFilter) {
            $query->where('package_id', $this->packageFilter);
        }

        if ($this->statusFilter !== '') {
            if ($this->statusFilter === 'trial') {
                $query->where('is_trial', true)->where('trial_ends_at', '>', now());
            } elseif ($this->statusFilter === 'expired') {
                $query->where('subscription_expires_at', '<', now());
            } else {
                $query->where('is_active', (bool) $this->statusFilter);
            }
        }

        if ($this->countryFilter) {
            $query->where('country', $this->countryFilter);
        }

        $companies = $query->latest()->paginate(10);
        $packages = Package::where('is_active', true)->get();
        $countries = Company::distinct('country')->pluck('country')->sort();

        return view('livewire.admin.companies.index', [
            'companies' => $companies,
            'packages' => $packages,
            'countries' => $countries,
        ])->layout('components.layouts.admin');
    }
}
