<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiToken extends Model
{
    protected $fillable = [
        'name', 'token_hash', 'token_preview', 'abilities', 'allowed_ips',
        'expires_at', 'last_used_at', 'is_active', 'created_by'
    ];

    protected $casts = [
        'abilities' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateToken($name, $abilities = [], $expiresInDays = 365)
    {
        $token = 'hw360_' . Str::random(40);
        $hashedToken = hash('sha256', $token);

        $apiToken = static::create([
            'name' => $name,
            'token_hash' => $hashedToken,
            'token_preview' => substr($token, 0, 12) . '...',
            'abilities' => $abilities,
            'expires_at' => $expiresInDays ? Carbon::now()->addDays($expiresInDays) : null,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return [
            'api_token' => $apiToken,
            'plain_token' => $token
        ];
    }

    public function isValid($token, $ability = null, $ip = null)
    {
        // Check if token matches
        if (!hash_equals($this->token_hash, hash('sha256', $token))) {
            return false;
        }

        // Check if active
        if (!$this->is_active) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check abilities
        if ($ability && !in_array($ability, $this->abilities ?? [])) {
            return false;
        }

        // Check IP restrictions
        if (!empty($this->allowed_ips) && $ip && !in_array($ip, $this->allowed_ips)) {
            return false;
        }

        return true;
    }

    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon($days = 7)
    {
        return $this->expires_at && $this->expires_at->diffInDays(now()) <= $days;
    }
}
