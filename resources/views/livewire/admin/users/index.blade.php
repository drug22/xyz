<div>
    <!-- Header Section -->
    <div class="mb-6">
        <flux:heading size="xl">Users Management</flux:heading>
        <flux:text class="mt-2">Manage all users across companies</flux:text>

        <div class="mt-4 flex justify-end">
            <flux:button :href="route('admin.users.create')" wire:navigate variant="primary" icon="plus" color="green">
                Add Company
            </flux:button>
        </div>
    </div>

    <!-- Search & Filters -->
    <flux:card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
            <!-- Search - 100% doar pe 478px și mai jos -->
            <flux:field class="w-full sm:flex-1">
                <flux:label>Search</flux:label>
                <flux:input wire:model.live="search" placeholder="Search users..." />
            </flux:field>

            <!-- Company Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Company</flux:label>
                <flux:select wire:model.live="companyFilter" placeholder="All Companies">
                    <flux:select.option value="">All Companies</flux:select.option>
                    @foreach($companies as $company)
                        <flux:select.option value="{{ $company->id }}">{{ $company->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <!-- Role Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Role</flux:label>
                <flux:select wire:model.live="roleFilter" placeholder="All Roles">
                    <flux:select.option value="">All Roles</flux:select.option>
                    @foreach($roles as $role)
                        <flux:select.option value="{{ $role->name }}">{{ ucfirst($role->name) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <!-- Status Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Status</flux:label>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="1">Active</flux:select.option>
                    <flux:select.option value="0">Inactive</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <!-- Users Table -->
    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column>User</flux:table.column>
            <flux:table.column>Company</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Owner</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-purple-500 flex items-center justify-center text-white text-sm font-medium">
                            {{ $user->initials() }}
                        </div>
                        <div>
                            <flux:text class="font-medium">{{ $user->name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $user->email }}</flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($user->company)
                            <flux:text class="font-medium">{{ $user->company->name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500">{{ $user->company->package->name }}</flux:text>
                        @else
                            <flux:text class="font-medium text-zinc-400">No Company</flux:text>
                            <flux:text size="sm" class="text-zinc-400">
                                @if($user->hasRole('super-admin'))
                                    System Administrator
                                @else
                                    Independent User
                                @endif
                            </flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @foreach($user->roles as $role)
                            <flux:badge
                                color="{{ match($role->name) {
                                    'super-admin' => 'red',
                                    'owner' => 'amber',
                                    'admin' => 'blue',
                                    'user' => 'green',
                                    default => 'zinc'
                                } }}"
                                size="sm"
                                inset="top bottom"
                            >
                                {{ ucfirst($role->name) }}
                            </flux:badge>
                        @endforeach
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($user->is_active)
                            <flux:badge color="green" size="sm" inset="top bottom">
                                <flux:icon name="check-circle" class="h-3 w-3 mr-1" />
                                Active
                            </flux:badge>
                        @else
                            <flux:badge color="red" size="sm" inset="top bottom">
                                <flux:icon name="x-circle" class="h-3 w-3 mr-1" />
                                Inactive
                            </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($user->is_owner)
                            <flux:badge color="amber" size="sm" inset="top bottom">
                                <flux:icon name="star" class="h-3 w-3 mr-1" />
                                Owner
                            </flux:badge>
                        @else
                            <flux:text size="sm" class="text-zinc-400">-</flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :href="route('admin.users.edit', $user)"
                                    wire:navigate
                                    icon="pencil"
                                >
                                    Edit User
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item wire:click="openPasswordModal({{ $user->id }})" icon="key">
                                    Set Password
                                </flux:menu.item>

                                <flux:menu.item
                                    wire:click="resetPassword({{ $user->id }})"
                                    wire:confirm="Are you sure you want to send a password reset email?"
                                    icon="envelope"
                                >
                                    Send Reset Link
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="toggleStatus({{ $user->id }})"
                                    wire:confirm="Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?"
                                    icon="{{ $user->is_active ? 'eye-slash' : 'eye' }}"
                                >
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                                    icon="trash"
                                    variant="danger"
                                >
                                    Delete User
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="text-center py-12 flex flex-col items-center">
                            <flux:icon name="users" class="h-12 w-12 text-zinc-400 mb-4" />
                            <flux:heading size="lg">No users found</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Get started by creating your first user</flux:text>
                            <div class="mt-4">
                                <flux:button :href="route('admin.users.create')" wire:navigate variant="primary" icon="plus" color="green">
                                    Add Company
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Password Modal -->
    <flux:modal wire:model="showPasswordModal" class="md:w-[500px] lg:w-[600px]">
        <form wire:submit="setManualPassword">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Set New Password</flux:heading>
                    <flux:text class="mt-2">
                        Enter password for: <strong>{{ $selectedUserName ?? 'Unknown User' }}</strong>
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>New Password</flux:label>
                    <flux:input wire:model="newPassword" type="password" />
                    <flux:error name="newPassword" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirm Password</flux:label>
                    <flux:input wire:model="newPasswordConfirmation" type="password" />
                    <flux:error name="newPasswordConfirmation" />
                </flux:field>

                <div class="flex gap-4">
                    <flux:spacer />
                    <flux:button wire:click="closePasswordModal" variant="danger">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" color="green">Set Password</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
