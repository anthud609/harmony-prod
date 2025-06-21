<?php

use Illuminate\Database\Schema\Blueprint;

class CreateRolesTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name', 100)->unique();
            $table->string('display_name', 200);
            $table->text('description')->nullable();
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('name');
            $table->index('priority');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('roles');
    }
}
