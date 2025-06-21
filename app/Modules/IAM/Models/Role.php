<?php
// File: app/Modules/IAM/Models/Role.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    protected $fillable = [
        'name', 'display_name', 'description', 
        'priority', 'is_system', 'is_active'
    ];
    
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];
    
    /**
     * Users relationship
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }
    
    /**
     * Permissions relationship
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withPivot('granted_at', 'granted_by')
            ->withTimestamps();
    }
    
    /**
     * Grant permission to role
     */
    public function grantPermission(string $permissionName, ?string $grantedBy = null): void
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        
        if (!$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id, [
                'granted_at' => date('Y-m-d H:i:s'),
                'granted_by' => $grantedBy
            ]);
        }
    }
    
    /**
     * Revoke permission from role
     */
    public function revokePermission(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }
}