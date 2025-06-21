<?php
// File: app/Modules/IAM/Models/Department.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'manager_id',
        'location',
        'budget',
        'cost_center',
        'is_active',
        'employee_count',
        'established_date'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'budget' => 'decimal:2',
        'employee_count' => 'integer',
        'established_date' => 'date'
    ];
    
    /**
     * Employees in this department
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Department manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Parent department
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }
    
    /**
     * Child departments
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }
    
    /**
     * Get all ancestors
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }
    
    /**
     * Get all descendants
     */
    public function descendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }
        
        return $descendants;
    }
    
    /**
     * Get full department path
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->name]);
        $ancestors = $this->ancestors();
        
        foreach ($ancestors as $ancestor) {
            $path->prepend($ancestor->name);
        }
        
        return $path->implode(' > ');
    }
}