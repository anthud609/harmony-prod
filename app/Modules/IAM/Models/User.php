<?php
// File: app/Modules/IAM/Models/User.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends BaseModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'username', 'email', 'password', 'employee_id',
        'first_name', 'last_name', 'middle_name', 'display_name',
        'job_title', 'department_id', 'manager_id', 'hire_date',
        'phone', 'mobile', 'work_email',
        'preferred_theme', 'locale', 'timezone',
        'status', 'is_verified', 'verified_at',
        'settings', 'favorites', 'notification_count', 'message_count',
        'last_login_at', 'password_changed_at'
    ];
    
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];
    
    protected $casts = [
        'settings' => 'array',
        'favorites' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * Roles relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }
    
    /**
     * Direct permissions relationship
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('is_granted', 'granted_at', 'granted_by', 'expires_at')
            ->wherePivot('is_granted', true)
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                    ->orWhere('user_permissions.expires_at', '>', date('Y-m-d H:i:s'));
            });
    }
    
    /**
     * Department relationship
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Manager relationship
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Subordinates relationship
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }
    
    /**
     * Activity logs
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }


     /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        
        return strtoupper(
            substr($firstName, 0, 1) . 
            substr($lastName, 0, 1)
        ) ?: 'U';
    }
    
    /**
     * Messages sent by user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    /**
     * Messages received by user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }
    
    /**
     * User's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
/**
 * Get unread message count
 */
public function getUnreadMessageCountAttribute(): int
{
    return $this->receivedMessages()->unread()->count();
}

/**
 * Get unread notification count
 */
public function getUnreadNotificationCountAttribute(): int
{
    return $this->notifications()->unread()->count();
}

    
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
    
    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }
    
    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        // Check direct permissions
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }
        
        // Check role permissions
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }
    
    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get all permissions (from roles and direct)
     */
    public function getAllPermissions()
    {
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereHas('users', function ($query) {
                $query->where('users.id', $this->id);
            });
        })->get();
        
        $directPermissions = $this->permissions;
        
        return $rolePermissions->merge($directPermissions)->unique('id');
    }
    
    /**
     * Assign role
     */
    public function assignRole(string $roleName, ?string $assignedBy = null): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        
        if (!$this->hasRole($roleName)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => $assignedBy
            ]);
        }
    }
    
    /**
     * Remove role
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }
    
    /**
     * Set password (hashed)
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}