<div>
    <flux:heading size="xl">Create New User</flux:heading>
    <flux:text class="mt-2">Add a new user to the system</flux:text>

    <form wire:submit="store" class="mt-6 space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">User Information</flux:heading>

                    <flux:field>
                        <flux:label>Full Name *</flux:label>
                        <flux:input wire:model="name" placeholder="Enter full name" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email Address *</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="user@company.com" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Password *</flux:label>
                        <flux:input wire:model="password" type="password" placeholder="Minimum 8 characters" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Confirm Password *</flux:label>
                        <flux:input wire:model="password_confirmation" type="password" placeholder="Confirm password" />
                        <flux:error name="password_confirmation" />
                    </flux:field>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Assignment & Permissions</flux:heading>

                    <flux:field>
                        <flux:label>Company</flux:label>
                        <flux:select wire:model="company_id" placeholder="Select company (optional)">
                            <flux:select.option value="">No Company (System User)</flux:select.option>
                            @foreach($companies as $company)
                                <flux:select.option value="{{ $company->id }}">
                                    {{ $company->name }} ({{ $company->package->name }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:description>Leave empty for system administrators</flux:description>
                        <flux:error name="company_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Role *</flux:label>
                        <flux:select wire:model="role" placeholder="Select user role">
                            @foreach($roles as $role)
                                <flux:select.option value="{{ $role->name }}">
                                    {{ ucfirst($role->name) }}
                                    @if($role->name === 'super-admin') - Full System Access
                                    @elseif($role->name === 'owner') - Company Owner
                                    @elseif($role->name === 'admin') - Company Admin
                                    @elseif($role->name === 'user') - Regular User
                                    @endif
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_owner" />
                            Company Owner
                        </flux:label>
                        <flux:description>Only one owner per company is allowed</flux:description>
                        <flux:error name="is_owner" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_active" />
                            Active User
                        </flux:label>
                        <flux:description>Inactive users cannot access the system</flux:description>
                        <flux:error name="is_active" />
                    </flux:field>
                </div>
            </flux:card>
        </div>

        <div class="flex justify-between">
            <flux:button :href="route('admin.users.index')" wire:navigate variant="danger">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary" color="green">
                Create User
            </flux:button>
        </div>
    </form>
</div>
