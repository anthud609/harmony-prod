<?php
// File: database/migrations/2024_01_01_000002_create_roles_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateRolesTable extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name', 100)->unique();
            $table->string('display_name', 200);
            $table->text('description')->nullable();
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $this->addUserStamps($table);
            
            $table->index('name');
            $table->index('priority');
            $table->index('is_active');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}