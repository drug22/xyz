<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_template_id',
        'text_intrebare',
        'requires_comment',
        'requires_image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'requires_comment' => 'boolean',
        'requires_image' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function auditResponses()
    {
        return $this->hasMany(AuditResponse::class);
    }
}
