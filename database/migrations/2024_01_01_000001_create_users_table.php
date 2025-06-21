<?php
// File: database/migrations/2024_01_01_000001_create_users_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Authentication
            $table->string('username', 100)->unique();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            
            // Personal Information
            $table->string('employee_id', 50)->unique()->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('display_name', 200)->nullable();
            
            // Job Information
            $table->string('job_title', 200)->nullable();
            $table->char('department_id', 36)->nullable()->index();
            $table->char('manager_id', 36)->nullable()->index();
            $table->date('hire_date')->nullable();
            
            // Contact Information
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('work_email', 255)->nullable();
            
            // Preferences
            $table->enum('preferred_theme', ['light', 'dark', 'system'])->default('system');
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Authentication tokens
            $table->string('remember_token', 100)->nullable();
            $table->string('api_token', 80)->unique()->nullable();
            
            // Additional fields
            $table->json('settings')->nullable();
            $table->json('favorites')->nullable();
            $table->integer('notification_count')->default(0);
            $table->integer('message_count')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            
            // Indexes
            $table->index(['first_name', 'last_name']);
            $table->index('status');
            $table->index('hire_date');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}