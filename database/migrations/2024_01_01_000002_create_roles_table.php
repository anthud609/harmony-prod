<?php
// File: database/migrations/2024_01_01_000002_create_roles_table.php

namespace Database\Migrations;

use Illuminate\Database\Schema\Blueprint;

class CreateRolesTable extends Migration
{
    public function up()
    {
        $this->schema->create('roles', function (Blueprint $table) {
            $this->addUuidPrimaryKey($table);
            
            $table->string('name', 100)->unique();
            $table->string('display_name', 200);
            $table->text('description')->nullable();
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->boolean('is_active')->default(true);
            
            $this->addTimestamps($table);
            $this->addUserStamps($table);
            
            $table->index('name');
            $table->index('priority');
            $table->index('is_active');
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('roles');
    }
}