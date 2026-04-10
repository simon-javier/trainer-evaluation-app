# start-trainer-app.ps1
# This script starts the Laravel application and Vite frontend using the standalone PHP 8.4.

# Automatically use the directory where this script is located as the project path
$ProjectPath = $PSScriptRoot

# Update this if the standalone PHP 8.4 was installed in a different location
$PhpPath = "C:\php\php.exe" 

Set-Location -Path $ProjectPath

Write-Host "Starting Laravel Server (PHP 8.4) in the background..."
Start-Process -FilePath $PhpPath -ArgumentList "artisan serve --port=8000" -WindowStyle Minimized

Write-Host "Starting Vite Frontend in the background..."
Start-Process -FilePath "npm.cmd" -ArgumentList "run dev" -WindowStyle Minimized

Write-Host "Application started successfully!"
Start-Sleep -Seconds 3
