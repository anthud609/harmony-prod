<?php
// File: database/migrate.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Get the database connection
$connection = Capsule::connection();
$schema = $connection->getSchemaBuilder();

echo "Starting database migration...\n\n";

// Get all migration files
$migrationFiles = glob(__DIR__ . '/migrations/*.php');
sort($migrationFiles);

// Track which migrations have been run
$migrationsTable = 'migrations';

// Create migrations table if it doesn't exist
if (!$schema->hasTable($migrationsTable)) {
    echo "Creating migrations table...\n";
    $schema->create($migrationsTable, function ($table) {
        $table->increments('id');
        $table->string('migration');
        $table->integer('batch');
        $table->timestamp('migrated_at')->useCurrent();
    });
}

// Get already run migrations
$ranMigrations = $connection->table($migrationsTable)->pluck('migration')->toArray();

// Get the current batch number
$batch = $connection->table($migrationsTable)->max('batch') ?? 0;
$batch++;

$migrationsRun = 0;

foreach ($migrationFiles as $file) {
    $filename = basename($file, '.php');
    
    // Skip if already run
    if (in_array($filename, $ranMigrations)) {
        echo "Skipping: {$filename} (already run)\n";
        continue;
    }
    
    // Skip the base Migration class
    if ($filename === 'Migration') {
        continue;
    }
    
    echo "Running: {$filename}...\n";
    
    try {
        // Include the migration file
        require_once $file;
        
        // Get the class name from the filename
        // Remove date prefix (2024_01_01_000001_) and .php extension
        $nameWithoutDate = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
        // Convert snake_case to PascalCase
        $parts = explode('_', $nameWithoutDate);
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        // Check if class exists
        if (!class_exists($className)) {
            echo "  ERROR: Class {$className} not found in {$filename}\n";
            continue;
        }
        
        // Create instance and run migration
        $migration = new $className();
        
        // Pass the schema builder to the migration
        if (method_exists($migration, 'up')) {
            $migration->up($schema);
        } else {
            echo "  ERROR: Migration {$className} does not have an 'up' method\n";
            continue;
        }
        
        // Record the migration
        $connection->table($migrationsTable)->insert([
            'migration' => $filename,
            'batch' => $batch,
            'migrated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "  ✓ Success\n";
        $migrationsRun++;
        
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        echo "  Rolling back...\n";
        exit(1);
    }
}

if ($migrationsRun === 0) {
    echo "\nNo new migrations to run.\n";
} else {
    echo "\nSuccessfully ran {$migrationsRun} migration(s).\n";
}

// Show current database status
echo "\nDatabase tables:\n";
$tables = $connection->select("SHOW TABLES");
$dbName = $connection->getDatabaseName();
foreach ($tables as $table) {
    $tableName = $table->{"Tables_in_{$dbName}"};
    $count = $connection->table($tableName)->count();
    echo "  - {$tableName} ({$count} records)\n";
}

echo "\nMigration complete!\n";