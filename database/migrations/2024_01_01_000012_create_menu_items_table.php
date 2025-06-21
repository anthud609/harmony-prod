<?php
// File: database/migrations/2024_01_01_000012_create_menu_items_table.php

namespace Database\Migrations;

use Illuminate\Database\Schema\Blueprint;

class CreateMenuItemsTable extends Migration
{
    public function up()
    {
        $this->schema->create('menu_items', function (Blueprint $table) {
            $this->addUuidPrimaryKey($table);
            
            $table->string('name', 100);
            $table->string('label', 200);
            $table->string('icon', 100)->nullable();
            $table->string('url', 255)->nullable();
            $table->char('parent_id', 36)->nullable()->index();
            $table->char('permission_id', 36)->nullable()->index(); // Required permission
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('badge')->nullable(); // For notification badges
            $table->json('dropdown_items')->nullable(); // For nested items
            
            $this->addTimestamps($table);
            
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('set null');
            
            $table->index(['parent_id', 'order']);
            $table->index('is_active');
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('menu_items');
    }
}