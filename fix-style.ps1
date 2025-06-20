Write-Host "🔧 Comprehensive Style Fix Script" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green

# Step 1: Fix line endings
Write-Host "`n📝 Step 1: Converting line endings to Unix format..." -ForegroundColor Yellow
$files = Get-ChildItem -Path . -Include *.php -Recurse | Where-Object { $_.FullName -notlike "*\vendor\*" }
$count = 0
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($content) {
        $newContent = $content -replace "`r`n", "`n"
        if ($content -ne $newContent) {
            [System.IO.File]::WriteAllText($file.FullName, $newContent)
            $count++
        }
    }
}
Write-Host "✅ Fixed line endings in $count files" -ForegroundColor Green

# Step 2: Fix NOT operator spacing
Write-Host "`n🔧 Step 2: Fixing NOT operator spacing..." -ForegroundColor Yellow
$files = Get-ChildItem -Path . -Include *.php -Recurse | Where-Object { $_.FullName -notlike "*\vendor\*" }
$count = 0
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($content) {
        $newContent = $content -replace '!\s*function_exists', '! function_exists'
        $newContent = $newContent -replace '!\s*isset', '! isset'
        $newContent = $newContent -replace '!\s*empty', '! empty'
        $newContent = $newContent -replace '!\s*is_', '! is_'
        $newContent = $newContent -replace '!\s*file_exists', '! file_exists'
        $newContent = $newContent -replace '!\s*class_exists', '! class_exists'
        $newContent = $newContent -replace '!\s*method_exists', '! method_exists'
        $newContent = $newContent -replace '!\s*in_array', '! in_array'
        $newContent = $newContent -replace '!\s*array_key_exists', '! array_key_exists'
        $newContent = $newContent -replace '!\s*str', '! str'
        $newContent = $newContent -replace '!\s*preg', '! preg'
        $newContent = $newContent -replace '!\s*hash_equals', '! hash_equals'
        $newContent = $newContent -replace '!\s*headers_sent', '! headers_sent'
        $newContent = $newContent -replace '!\s*session_', '! session_'
        $newContent = $newContent -replace '!\s*(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)', '! $1'
        
        if ($content -ne $newContent) {
            [System.IO.File]::WriteAllText($file.FullName, $newContent)
            $count++
        }
    }
}
Write-Host "✅ Fixed NOT operator spacing in $count files" -ForegroundColor Green

# Step 3: Run PHPCBF
Write-Host "`n🛠️ Step 3: Running PHPCBF..." -ForegroundColor Yellow
& vendor\bin\phpcbf.bat

# Step 4: Run PHP-CS-Fixer
Write-Host "`n🎨 Step 4: Running PHP-CS-Fixer..." -ForegroundColor Yellow
& vendor\bin\php-cs-fixer.bat fix

# Step 5: Final check
Write-Host "`n📊 Step 5: Running final check..." -ForegroundColor Yellow
& vendor\bin\phpcs.bat --report=summary

Write-Host "`n✅ Style fixes complete!" -ForegroundColor Green