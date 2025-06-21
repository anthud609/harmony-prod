<?php
// File: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder
{
    public function run()
    {
        $this->seedDepartments();
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedUsers();
    }
    
    private function seedDepartments()
    {
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Sales', 'code' => 'SALES'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKTG']
        ];
        
        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
    
    private function seedPermissions()
    {
        $modules = [
            'users' => ['view', 'create', 'update', 'delete', 'export'],
            'employees' => ['view', 'create', 'update', 'delete', 'export'],
            'departments' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete', 'assign'],
            'permissions' => ['view', 'assign'],
            'reports' => ['view', 'create', 'export'],
            'settings' => ['view', 'update'],
            'dashboard' => ['view', 'customize'],
            'messages' => ['view', 'send'],
            'notifications' => ['view', 'manage']
        ];
        
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    'name' => "{$module}.{$action}",
                    'display_name' => ucfirst($action) . ' ' . ucfirst($module),
                    'description' => "Ability to {$action} {$module}",
                    'module' => $module,
                    'action' => $action,
                    'is_system' => in_array($module, ['users', 'roles', 'permissions'])
                ]);
            }
        }
    }
    
    private function seedRoles()
    {
        // Create Admin Role
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
            'priority' => 100,
            'is_system' => true
        ]);
        
        // Admin gets all permissions
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            $admin->grantPermission($permission->name);
        }
        
        // Create HR Manager Role
        $hrManager = Role::create([
            'name' => 'hr_manager',
            'display_name' => 'HR Manager',
            'description' => 'Human Resources management access',
            'priority' => 80,
            'is_system' => true
        ]);
        
        $hrPermissions = [
            'employees.*', 'departments.*', 'reports.view', 
            'reports.create', 'reports.export', 'messages.*',
            'notifications.*', 'dashboard.*'
        ];
        
        foreach ($hrPermissions as $pattern) {
            $permissions = Permission::where('name', 'LIKE', str_replace('*', '%', $pattern))->get();
            foreach ($permissions as $permission) {
                $hrManager->grantPermission($permission->name);
            }
        }
        
        // Create Employee Role
        $employee = Role::create([
            'name' => 'employee',
            'display_name' => 'Employee',
            'description' => 'Standard employee access',
            'priority' => 50,
            'is_system' => true
        ]);
        
        $employeePermissions = [
            'dashboard.view', 'employees.view', 'messages.view',
            'messages.send', 'notifications.view'
        ];
        
        foreach ($employeePermissions as $permissionName) {
            $employee->grantPermission($permissionName);
        }
    }
    
    private function seedUsers()
    {
        $hr = Department::where('code', 'HR')->first();
        $eng = Department::where('code', 'ENG')->first();
        $sales = Department::where('code', 'SALES')->first();
        
        // Create Admin User
        $alice = User::create([
            'username' => 'alice_admin',
            'email' => 'alice_admin@email.com',
            'password' => 'secret',
            'employee_id' => 'EMP001',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'job_title' => 'System Administrator',
            'department_id' => $eng->id,
            'hire_date' => now()->subYears(5),
            'preferred_theme' => 'dark',
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now()
        ]);
        $alice->assignRole('admin');
        
        // Create HR Manager
        $bob = User::create([
            'username' => 'bob_hr',
            'email' => 'bob_hr@email.com',
            'password' => 'secret',
            'employee_id' => 'EMP002',
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'job_title' => 'HR Manager',
            'department_id' => $hr->id,
            'hire_date' => now()->subYears(3),
            'preferred_theme' => 'light',
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now()
        ]);
        $bob->assignRole('hr_manager');
        
        // Create Regular Employee
        $charlie = User::create([
            'username' => 'charlie_user',
            'email' => 'charlie_user@email.com',
            'password' => 'secret',
            'employee_id' => 'EMP003',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'job_title' => 'Sales Representative',
            'department_id' => $sales->id,
            'manager_id' => $bob->id,
            'hire_date' => now()->subYear(),
            'preferred_theme' => 'system',
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now()
        ]);
        $charlie->assignRole('employee');
        
        // Create some notifications and messages
        $charlie->notifications()->create([
            'type' => 'leave_approved',
            'icon' => 'fas fa-check',
            'color' => 'green',
            'message' => 'Your leave request for Dec 25-27 has been approved',
            'is_read' => false
        ]);
        
        $alice->receivedMessages()->create([
            'sender_id' => $bob->id,
            'subject' => 'Q4 Report Review',
            'content' => 'Hey, can you review the Q4 report before the meeting tomorrow?',
            'is_read' => false
        ]);
    }
}