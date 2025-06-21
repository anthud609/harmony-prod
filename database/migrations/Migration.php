<?php
// File: database/migrations/Migration.php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder;

abstract class Migration
{
    /**
     * The schema builder instance
     */
    protected Builder $schema;
    
    /**
     * The database manager instance
     */
    protected Capsule $db;
    
    public function __construct()
    {
        $this->db = new Capsule();
        $this->schema = $this->db->schema();
    }
    
    /**
     * Run the migrations
     */
    abstract public function up(): void;
    
    /**
     * Reverse the migrations
     */
    abstract public function down(): void;
}