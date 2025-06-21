<?php
// File: app/Core/Database/BaseModel.php

namespace App\Core\Database;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

abstract class BaseModel extends Model
{
    // Disable auto-incrementing
    public $incrementing = false;
    
    // Set key type to string for UUID
    protected $keyType = 'string';
    
    // UUID as primary key
    protected $primaryKey = 'id';
    
    /**
     * Boot function to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            }
        });
    }
    
    /**
     * Get formatted created_at for display
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->format('M d, Y H:i');
    }
    
    /**
     * Get formatted updated_at for display
     */
    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at->format('M d, Y H:i');
    }
}