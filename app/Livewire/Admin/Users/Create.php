<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $company_id = '';
    public $role = '';
    public $is_owner = false;
    public $is_active = true;

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|exists:roles,name',
            'is_owner' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // CONVERȚIE CORECTĂ: string gol la null
        $companyId = $this->company_id === '' ? null : $this->company_id;

        // Verifică dacă încearcă să creeze owner pentru o companie care deja are owner
        if ($this->is_owner && $companyId) {
            $existingOwner = User::where('company_id', $companyId)
                ->where('is_owner', true)
                ->first();
            if ($existingOwner) {
                Flux::toast(
                    heading: 'Cannot Create Owner',
                    text: 'This company already has an owner.',
                    variant: 'danger'
                );
                return;
            }
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'company_id' => $companyId, // FOLOSEȘTE VERSIUNEA CONVERTITĂ
            'is_owner' => $this->is_owner,
            'is_active' => $this->is_active,
        ]);

        $user->assignRole($this->role);

        Flux::toast(
            heading: 'User Created',
            text: "'{$this->name}' has been successfully created.",
            variant: 'success'
        );

        return redirect()->route('admin.users.index');
    }


    public function render()
    {
        $companies = Company::all();
        $roles = Role::all();

        return view('livewire.admin.users.create', [
            'companies' => $companies,
            'roles' => $roles,
        ])->layout('components.layouts.admin');
    }
}
