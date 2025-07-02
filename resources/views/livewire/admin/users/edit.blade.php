<div>
    <flux:heading size="xl">Edit User</flux:heading>
    <flux:text class="mt-2">Update {{ $user->name }} information and settings</flux:text>

    <form wire:submit="update" class="mt-6 space-y-6">
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
                        <flux:label>Role *</flux:label>
                        <flux:select wire:model="role" placeholder="Select user role">
                            @foreach($roles as $roleOption)
                                <flux:select.option value="{{ $roleOption->name }}">
                                    {{ ucfirst($roleOption->name) }}
                                    @if($roleOption->name === 'super-admin') - Full System Access
                                    @elseif($roleOption->name === 'owner') - Company Owner
                                    @elseif($roleOption->name === 'admin') - Company Admin
                                    @elseif($roleOption->name === 'user') - Regular User
                                    @endif
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="role" />
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
                Update User
            </flux:button>
        </div>
    </form>
</div>
