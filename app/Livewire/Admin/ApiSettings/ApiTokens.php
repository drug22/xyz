<?php

namespace App\Livewire\Admin\ApiSettings;

use App\Models\ApiToken;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class ApiTokens extends Component
{
    use WithPagination;

    public $name = '';
    public $abilities = ['orders:create', 'orders:read'];
    public $allowed_ips = '';
    public $expires_in_days = 365;

    public $showToken = false;
    public $generatedToken = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'abilities' => 'required|array',
        'allowed_ips' => 'nullable|string',
        'expires_in_days' => 'nullable|integer|min:1|max:3650',
    ];

    public function generateToken()
    {
        $this->validate();

        $allowedIps = $this->allowed_ips
            ? array_map('trim', explode(',', $this->allowed_ips))
            : null;

        $result = ApiToken::generateToken(
            $this->name,
            $this->abilities,
            $this->expires_in_days
        );

        $result['api_token']->update(['allowed_ips' => $allowedIps]);

        $this->generatedToken = $result['plain_token'];
        $this->showToken = true;

        // Reset form
        $this->reset(['name', 'allowed_ips']);

        Flux::toast(
            heading: 'API Token Generated',
            text: 'Copy the token now - you won\'t be able to see it again!',
            variant: 'success'
        );
    }

    public function revokeToken($tokenId)
    {
        $token = ApiToken::findOrFail($tokenId);
        $token->update(['is_active' => false]);

        Flux::toast(
            heading: 'Token Revoked',
            text: "API token '{$token->name}' has been revoked.",
            variant: 'success'
        );
    }

    public function deleteToken($tokenId)
    {
        $token = ApiToken::findOrFail($tokenId);
        $tokenName = $token->name;
        $token->delete();

        Flux::toast(
            heading: 'Token Deleted',
            text: "API token '{$tokenName}' has been permanently deleted.",
            variant: 'success'
        );
    }

    public function closeTokenModal()
    {
        $this->showToken = false;
        $this->generatedToken = '';
    }

    public function render()
    {
        $tokens = ApiToken::with('creator')
            ->latest()
            ->paginate(10);

        $availableAbilities = [
            'orders:create' => 'Create Orders',
            'orders:read' => 'Read Orders',
            'orders:update' => 'Update Orders',
            'orders:delete' => 'Delete Orders',
            'packages:read' => 'Read Packages',
            'tax:calculate' => 'Calculate Tax',
            'tax:validate' => 'Validate VAT',
        ];

        return view('livewire.admin.api-settings.api-tokens', [
            'tokens' => $tokens,
            'availableAbilities' => $availableAbilities,
        ])->layout('components.layouts.admin');
    }
}
