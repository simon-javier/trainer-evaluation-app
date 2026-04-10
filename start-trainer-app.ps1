# start-trainer-app.ps1
# This script starts the Laravel application and Vite frontend using the standalone PHP 8.4.

# Automatically use the directory where this script is located as the project path
$ProjectPath = $PSScriptRoot

# Paths to the standalone PHP 8.4 and Composer
$PhpPath = "C:\Users\Evangeline\.config\herd-lite\bin\php.exe" 
$ComposerPath = "C:\Users\Evangeline\.config\herd-lite\bin\composer.phar"

Set-Location -Path $ProjectPath

Write-Host "Running composer update..."
Start-Process -FilePath $PhpPath -ArgumentList "$ComposerPath update" -NoNewWindow -Wait

Write-Host "Running composer setup..."
Start-Process -FilePath $PhpPath -ArgumentList "$ComposerPath run setup" -NoNewWindow -Wait

Write-Host "Starting Laravel Server (PHP 8.4) in the background..."
Start-Process -FilePath $PhpPath -ArgumentList "artisan serve --port=8000" -WindowStyle Minimized

Write-Host "Starting Vite Frontend in the background..."
Start-Process -FilePath "npm.cmd" -ArgumentList "run dev" -WindowStyle Minimized

Write-Host "Application started successfully!"
Start-Sleep -Seconds 3
