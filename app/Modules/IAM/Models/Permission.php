<?php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    protected $fillable = [
        'name', 'display_name', 'description',
        'module', 'action', 'is_system'
    ];
    
    protected $casts = [
        'is_system' => 'boolean'
    ];
    
    /**
     * Roles relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withPivot('granted_at', 'granted_by')
            ->withTimestamps();
    }
    
    /**
     * Users with direct permission
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('is_granted', 'granted_at', 'granted_by', 'expires_at')
            ->wherePivot('is_granted', true);
    }
}