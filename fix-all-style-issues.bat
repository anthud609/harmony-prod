@echo off
echo Fixing code style issues...

REM Fix line endings first
echo Converting line endings to Unix format...
powershell -Command "Get-ChildItem -Path . -Include *.php -Recurse | Where-Object { $_.FullName -notlike '*\vendor\*' } | ForEach-Object { $content = Get-Content $_.FullName -Raw; if ($content) { $content = $content -replace '`r`n', '`n'; [System.IO.File]::WriteAllText($_.FullName, $content) } }"

REM Run PHPCBF to auto-fix most issues
echo Running PHPCBF...
vendor\bin\phpcbf

REM Run PHP-CS-Fixer for additional fixes
echo Running PHP-CS-Fixer...
vendor\bin\php-cs-fixer fix

echo Done!