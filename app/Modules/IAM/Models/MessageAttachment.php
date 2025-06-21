<?php

// File: app/Modules/IAM/Models/MessageAttachment.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends BaseModel
{
    protected $table = 'message_attachments';

    protected $fillable = [
        'message_id',
        'type', // 'image', 'file', 'video', 'audio'
        'name',
        'url',
        'size',
        'mime_type',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get message
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get human-readable size
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}