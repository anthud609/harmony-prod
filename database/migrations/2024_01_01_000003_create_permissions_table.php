<?php
// File: database/migrations/2024_01_01_000003_create_permissions_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreatePermissionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name', 100)->unique(); // e.g., 'users.view', 'users.create'
            $table->string('display_name', 200);
            $table->text('description')->nullable();
            $table->string('module', 50); // e.g., 'users', 'employees', 'payroll'
            $table->string('action', 50); // e.g., 'view', 'create', 'update', 'delete'
            $table->boolean('is_system')->default(false);
            
            $table->timestamps();
            
            $table->index('name');
            $table->index(['module', 'action']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
}