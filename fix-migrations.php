<?php
// File: fix-all-migrations.php
// Run this to fix all migration files

$migrationsPath = __DIR__ . '/database/migrations/';
$files = glob($migrationsPath . '*.php');

echo "Fixing migration files...\n\n";

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip the base Migration.php file
    if ($filename === 'Migration.php') {
        unlink($file); // Remove the base Migration.php file as we don't need it
        echo "Removed: {$filename} (not needed)\n";
        continue;
    }
    
    echo "Processing: {$filename}\n";
    
    // Read the file content
    $content = file_get_contents($file);
    
    // Extract the class name from the file content
    if (preg_match('/class\s+(\w+)/', $content, $matches)) {
        $className = $matches[1];
        echo "  Found class: {$className}\n";
    } else {
        echo "  ERROR: Could not find class name\n";
        continue;
    }
    
    // Create a clean migration file
    $newContent = "<?php\n\n";
    $newContent .= "use Illuminate\\Database\\Schema\\Blueprint;\n\n";
    $newContent .= "class {$className}\n";
    $newContent .= "{\n";
    
    // Extract the up method
    if (preg_match('/public\s+function\s+up\s*\([^)]*\)\s*(?::\s*void)?\s*\{(.*?)\n    \}/s', $content, $upMatch)) {
        $upBody = $upMatch[1];
        // Replace Schema:: with $schema->
        $upBody = str_replace('Schema::', '$schema->', $upBody);
        // Replace $this->schema-> with $schema->
        $upBody = str_replace('$this->schema->', '$schema->', $upBody);
        
        $newContent .= "    /**\n";
        $newContent .= "     * Run the migrations.\n";
        $newContent .= "     */\n";
        $newContent .= "    public function up(\$schema)\n";
        $newContent .= "    {" . $upBody . "\n";
        $newContent .= "    }\n\n";
    }
    
    // Extract the down method
    if (preg_match('/public\s+function\s+down\s*\([^)]*\)\s*(?::\s*void)?\s*\{(.*?)\n    \}/s', $content, $downMatch)) {
        $downBody = $downMatch[1];
        // Replace Schema:: with $schema->
        $downBody = str_replace('Schema::', '$schema->', $downBody);
        // Replace $this->schema-> with $schema->
        $downBody = str_replace('$this->schema->', '$schema->', $downBody);
        
        $newContent .= "    /**\n";
        $newContent .= "     * Reverse the migrations.\n";
        $newContent .= "     */\n";
        $newContent .= "    public function down(\$schema)\n";
        $newContent .= "    {" . $downBody . "\n";
        $newContent .= "    }\n";
    }
    
    $newContent .= "}\n";
    
    // Write the fixed content back
    file_put_contents($file, $newContent);
    
    echo "  ✓ Fixed\n";
}

echo "\nAll migration files have been fixed!\n";

// Also display what class names are expected
echo "\nExpected class names for each migration:\n";
$files = glob($migrationsPath . '*.php');
foreach ($files as $file) {
    $filename = basename($file, '.php');
    $nameWithoutDate = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
    $parts = explode('_', $nameWithoutDate);
    $className = '';
    foreach ($parts as $part) {
        $className .= ucfirst($part);
    }
    echo "  {$filename} => {$className}\n";
}

echo "\nYou can now run: php database/migrate.php\n";