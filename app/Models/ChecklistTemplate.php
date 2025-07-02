<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'prefix_numerotare',
        'category',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function audits()
    {
        return $this->hasMany(Audit::class);
    }

    public function userAccess()
    {
        return $this->belongsToMany(User::class, 'user_checklist_access');
    }
}
