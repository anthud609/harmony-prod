<?php
// File: database/migrations/2024_01_01_000008_create_departments_table.php

namespace Database\Migrations;

use Illuminate\Database\Schema\Blueprint;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        $this->schema->create('departments', function (Blueprint $table) {
            $this->addUuidPrimaryKey($table);
            
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->char('parent_id', 36)->nullable()->index();
            $table->char('manager_id', 36)->nullable()->index();
            $table->boolean('is_active')->default(true);
            
            $this->addTimestamps($table);
            $this->addUserStamps($table);
            
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('name');
            $table->index('is_active');
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('departments');
    }
}