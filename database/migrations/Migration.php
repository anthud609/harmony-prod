<?php
// File: database/migrations/Migration.php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

abstract class Migration
{
    protected $capsule;
    protected $schema;
    
    public function __construct()
    {
        $this->capsule = Capsule::connection();
        $this->schema = Capsule::schema();
    }
    
    abstract public function up();
    abstract public function down();
    
    /**
     * Add UUID primary key to table
     */
    protected function addUuidPrimaryKey(Blueprint $table)
    {
        $table->char('id', 36)->primary();
    }
    
    /**
     * Add timestamps with proper defaults
     */
    protected function addTimestamps(Blueprint $table)
    {
        $table->timestamp('created_at')->useCurrent();
        $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    }
    
    /**
     * Add soft deletes
     */
    protected function addSoftDeletes(Blueprint $table)
    {
        $table->timestamp('deleted_at')->nullable()->index();
    }
    
    /**
     * Add created_by and updated_by
     */
    protected function addUserStamps(Blueprint $table)
    {
        $table->char('created_by', 36)->nullable()->index();
        $table->char('updated_by', 36)->nullable()->index();
    }
}