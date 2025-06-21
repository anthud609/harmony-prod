<?php
namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends BaseModel
{
    protected $fillable = [
        'sender_id',
        'recipient_id', 
        'subject',
        'body',
        'is_read',
        'is_archived',
        'read_at',
        'type'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'is_archived' => 'boolean',
        'read_at' => 'datetime'
    ];
    
    /**
     * Get the sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    /**
     * Get the recipient
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    /**
     * Get preview text
     */
    public function getPreviewAttribute(): string
    {
        $text = strip_tags($this->body);
        return strlen($text) > 100 ? substr($text, 0, 100) . '...' : $text;
    }
    
    /**
     * Get time display
     */
    public function getTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
    
    /**
     * Get avatar data
     */
    public function getAvatarAttribute(): array
    {
        if (!$this->sender) {
            return [
                'initials' => 'SY',
                'gradient' => 'from-gray-400 to-gray-600'
            ];
        }
        
        $gradients = [
            'from-green-400 to-blue-500',
            'from-purple-400 to-pink-500',
            'from-orange-400 to-red-500',
            'from-indigo-400 to-purple-500',
        ];
        
        $index = crc32($this->sender->id) % count($gradients);
        
        return [
            'initials' => $this->sender->initials,
            'gradient' => $gradients[$index]
        ];
    }
}
