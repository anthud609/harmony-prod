<?php

use Illuminate\Database\Schema\Blueprint;

class CreatePermissionsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('permissions', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('permissions');
    }
}
