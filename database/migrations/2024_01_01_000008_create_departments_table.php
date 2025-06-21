<?php
// File: database/migrations/2024_01_01_000008_create_departments_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateDepartmentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->char('parent_id', 36)->nullable()->index();
            $table->char('manager_id', 36)->nullable()->index();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $this->addUserStamps($table);
            
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('name');
            $table->index('is_active');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
}