<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $companyFilter = '';
    public $roleFilter = '';
    public $statusFilter = '';

    // Modal properties
    public $showPasswordModal = false;
    public $selectedUserId = null;
    public $selectedUserName = null;
    public $newPassword = '';
    public $newPasswordConfirmation = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'companyFilter' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openPasswordModal($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            Flux::toast(
                heading: 'Error',
                text: 'User not found.',
                variant: 'danger'
            );
            return;
        }

        $this->selectedUserId = $userId;
        $this->selectedUserName = $user->name;
        $this->showPasswordModal = true;
        $this->reset(['newPassword', 'newPasswordConfirmation']);
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->reset(['newPassword', 'newPasswordConfirmation']);
    }

    public function setManualPassword()
    {
        if (!$this->selectedUserId) {
            Flux::toast(
                heading: 'Error',
                text: 'No user selected.',
                variant: 'danger'
            );
            return;
        }

        $this->validate([
            'newPassword' => 'required|string|min:8',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->closePasswordModal();

        Flux::toast(
            heading: 'Password Updated',
            text: "Password set for {$user->name}",
            variant: 'success'
        );
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            Flux::toast(
                heading: 'Reset Link Sent',
                text: "Password reset link has been sent to {$user->email}",
                variant: 'success'
            );
        } else {
            Flux::toast(
                heading: 'Reset Failed',
                text: 'Unable to send password reset email.',
                variant: 'danger'
            );
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        Flux::toast(
            heading: 'User Status Updated',
            text: "The user has been {$status} successfully.",
            variant: 'success'
        );
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is_owner && $user->company && $user->company->users()->count() === 1) {
            Flux::toast(
                heading: 'Cannot Delete User',
                text: 'Cannot delete the only owner of a company.',
                variant: 'danger'
            );
            return;
        }

        $user->delete();

        Flux::toast(
            heading: 'User Deleted',
            text: 'The user has been permanently removed from the system.',
            variant: 'success'
        );
    }

    public function render()
    {
        $query = User::with(['company.package', 'roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->companyFilter, function ($query) {
                $query->where('company_id', $this->companyFilter);
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->latest();

        $users = $query->paginate(10);
        $companies = Company::all();
        $roles = Role::all();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'companies' => $companies,
            'roles' => $roles,
        ])->layout('components.layouts.admin');
    }
}
