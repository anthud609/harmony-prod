<?php

use Illuminate\Database\Schema\Blueprint;

class CreateDepartmentsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->char('parent_id', 36)->nullable()->index();
            $table->char('manager_id', 36)->nullable()->index();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('departments');
    }
}
