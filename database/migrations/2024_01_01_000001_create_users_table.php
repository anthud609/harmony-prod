<?php

use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('users', function (Blueprint $table) {
            // Primary key
            $table->uuid('id')->primary();
            
            // Authentication fields
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            
            // Basic info
            $table->string('employee_id', 20)->nullable()->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('display_name', 100)->nullable();
            
            // Work info
            $table->string('job_title', 100)->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('manager_id')->nullable();
            $table->date('hire_date')->nullable();
            
            // Contact info
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('work_email', 100)->nullable();
            
            // Preferences
            $table->enum('preferred_theme', ['light', 'dark', 'system'])->default('system');
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // JSON fields for flexible data
            $table->json('settings')->nullable();
            $table->json('favorites')->nullable();
            
            // Counters
            $table->integer('notification_count')->default(0);
            $table->integer('message_count')->default(0);
            
            // Timestamps
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('department_id');
            $table->index('manager_id');
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('users');
    }
}
