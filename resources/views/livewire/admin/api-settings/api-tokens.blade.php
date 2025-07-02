<div>
    <flux:heading size="xl">API Tokens Management</flux:heading>
    <flux:text class="mt-2">Create and manage API tokens for secure access to the API endpoints</flux:text>

    <!-- Generate New Token -->
    <flux:card class="mt-6">
        <div class="space-y-6">
            <flux:heading size="lg" icon="key">Generate New API Token</flux:heading>

            <form wire:submit="generateToken" class="space-y-4">
                <flux:field>
                    <flux:label>Token Name *</flux:label>
                    <flux:input wire:model="name" placeholder="WordPress Integration Token" />
                    <flux:description>A descriptive name to identify this token</flux:description>
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Permissions *</flux:label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($availableAbilities as $ability => $label)
                            <flux:label class="flex items-center gap-2">
                                <flux:checkbox
                                    wire:model="abilities"
                                    value="{{ $ability }}"
                                />
                                {{ $label }}
                            </flux:label>
                        @endforeach
                    </div>
                    <flux:error name="abilities" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Allowed IPs (Optional)</flux:label>
                        <flux:input wire:model="allowed_ips" placeholder="192.168.1.1, 10.0.0.1" />
                        <flux:description>Comma-separated list of allowed IP addresses</flux:description>
                        <flux:error name="allowed_ips" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Expires In (Days)</flux:label>
                        <flux:input wire:model="expires_in_days" type="number" min="1" max="3650" />
                        <flux:description>Leave empty for no expiration</flux:description>
                        <flux:error name="expires_in_days" />
                    </flux:field>
                </div>

                <flux:button type="submit" variant="primary" icon="plus">
                    Generate Token
                </flux:button>
            </form>
        </div>
    </flux:card>

    <!-- Generated Token Modal -->
    @if($showToken)
        <flux:modal wire:model="showToken">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">API Token Generated Successfully</flux:heading>
                    <flux:text class="text-red-600 dark:text-red-400 mt-2">
                        ⚠️ Copy this token now - you won't be able to see it again!
                    </flux:text>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <flux:text class="font-mono text-sm break-all">{{ $generatedToken }}</flux:text>
                </div>

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                        <strong>API Base URL:</strong> {{ url('/bridge/v1') }}<br>
                        <strong>Authentication:</strong> Bearer {{ $generatedToken }}<br>
                        <strong>Example:</strong> POST {{ url('/bridge/v1/orders/create') }}
                    </flux:text>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="closeTokenModal" variant="outline">
                        I've Copied the Token
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Existing Tokens -->
    <flux:card class="mt-6">
        <div class="space-y-6">
            <flux:heading size="lg" icon="list-bullet">Active API Tokens</flux:heading>

            <flux:table :paginate="$tokens">
                <flux:table.columns>
                    <flux:table.column>Token Info</flux:table.column>
                    <flux:table.column>Permissions</flux:table.column>
                    <flux:table.column>Usage</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($tokens as $token)
                        <flux:table.row :key="$token->id">
                            <flux:table.cell>
                                <div>
                                    <flux:text class="font-medium">{{ $token->name }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $token->token_preview }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                        Created {{ $token->created_at->format('M d, Y') }} by {{ $token->creator->name }}
                                    </flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($token->abilities as $ability)
                                        <flux:badge size="xs" color="blue">{{ $ability }}</flux:badge>
                                    @endforeach
                                </div>
                                @if($token->allowed_ips)
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">
                                        IPs: {{ implode(', ', $token->allowed_ips) }}
                                    </flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div>
                                    @if($token->last_used_at)
                                        <flux:text size="sm">Last used: {{ $token->last_used_at->diffForHumans() }}</flux:text>
                                    @else
                                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Never used</flux:text>
                                    @endif

                                    @if($token->expires_at)
                                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                            Expires: {{ $token->expires_at->format('M d, Y') }}
                                        </flux:text>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if(!$token->is_active)
                                    <flux:badge color="red" size="sm">Revoked</flux:badge>
                                @elseif($token->isExpired())
                                    <flux:badge color="orange" size="sm">Expired</flux:badge>
                                @elseif($token->isExpiringSoon())
                                    <flux:badge color="yellow" size="sm">Expires Soon</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm">Active</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                                    <flux:menu>
                                        @if($token->is_active && !$token->isExpired())
                                            <flux:menu.item
                                                wire:click="revokeToken({{ $token->id }})"
                                                wire:confirm="Are you sure you want to revoke this token?"
                                                icon="no-symbol"
                                                variant="danger"
                                            >
                                                Revoke Token
                                            </flux:menu.item>
                                        @endif

                                        <flux:menu.item
                                            wire:click="deleteToken({{ $token->id }})"
                                            wire:confirm="Are you sure you want to permanently delete this token?"
                                            icon="trash"
                                            variant="danger"
                                        >
                                            Delete Token
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <div class="text-center py-12 flex flex-col items-center">
                                    <flux:icon name="key" class="h-12 w-12 text-zinc-400 mb-4" />
                                    <flux:heading size="lg">No API tokens</flux:heading>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Generate your first API token to get started</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    <!-- API Documentation -->
    <flux:card class="mt-6">
        <div class="space-y-4">
            <flux:heading size="lg" icon="book-open">API Documentation</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="font-medium">Base URL:</flux:text>
                    <flux:text class="font-mono text-sm">{{ url('/bridge/v1') }}</flux:text>
                </div>

                <div>
                    <flux:text class="font-medium">Authentication:</flux:text>
                    <flux:text class="font-mono text-sm">Authorization: Bearer {token}</flux:text>
                </div>
            </div>

            <div class="space-y-2">
                <flux:text class="font-medium">Available Endpoints:</flux:text>
                <div class="space-y-1 text-sm font-mono">
                    <div>POST /bridge/v1/orders/create</div>
                    <div>GET /bridge/v1/orders/{id}/show</div>
                    <div>GET /bridge/v1/orders/{id}/status</div>
                    <div>GET /bridge/v1/packages</div>
                    <div>POST /bridge/v1/tax/calculate</div>
                    <div>POST /bridge/v1/tax/validate-vat</div>
                    <div>GET /bridge/v1/tax/countries</div>
                </div>
            </div>
        </div>
    </flux:card>
</div>
