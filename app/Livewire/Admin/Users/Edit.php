<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Flux\Flux;

class Edit extends Component
{
    public User $user;
    public $name;
    public $email;
    public $company_id;
    public $role;
    public $is_owner;
    public $is_active;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->company_id = $user->company_id;
        $this->role = $user->roles->first()?->name;
        $this->is_owner = $user->is_owner;
        $this->is_active = $user->is_active;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|exists:roles,name',
            'is_owner' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Converție corectă pentru company_id
        $companyId = $this->company_id === '' ? null : $this->company_id;

        // Verifică dacă încearcă să creeze owner pentru o companie care deja are owner
        if ($this->is_owner && $companyId) {
            $existingOwner = User::where('company_id', $companyId)
                ->where('is_owner', true)
                ->where('id', '!=', $this->user->id)
                ->first();
            if ($existingOwner) {
                Flux::toast(
                    heading: 'Cannot Set Owner',
                    text: 'This company already has an owner.',
                    variant: 'danger'
                );
                return;
            }
        }

        // Update data (NO PASSWORD!)
        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'company_id' => $companyId,
            'is_owner' => $this->is_owner,
            'is_active' => $this->is_active,
        ]);

        // Update role
        $this->user->syncRoles([$this->role]);

        Flux::toast(
            heading: 'User Updated',
            text: "'{$this->name}' has been successfully updated.",
            variant: 'success'
        );

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $companies = Company::all();
        $roles = Role::all();

        return view('livewire.admin.users.edit', [
            'companies' => $companies,
            'roles' => $roles,
        ])->layout('components.layouts.admin');
    }
}
