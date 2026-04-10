@echo off

:: Check if PHP is installed
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo Error: PHP is not installed or not in your PATH.
    pause
    exit /b 1
)

:: Check if Composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo Error: Composer is not installed or not in your PATH.
    echo Please install Composer from https://getcomposer.org/
    pause
    exit /b 1
)

:: Check if npm is installed
where npm >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo Error: npm (Node.js^) is not installed or not in your PATH.
    echo Please install Node.js from https://nodejs.org/
    pause
    exit /b 1
)

:: First-time setup: if vendor directory or .env doesn't exist, run the setup script
if not exist "vendor\" (
    echo First time setup detected. Installing dependencies and building the application...
    call composer run setup
) else if not exist ".env" (
    echo First time setup detected. Installing dependencies and building the application...
    call composer run setup
)

set URL="http://localhost:42069"
echo Starting the application server...

:: Open the browser in a separate background process after a short delay
start /B cmd /c "timeout /t 3 >nul && start """" %URL%"

:: Start the Vite development server in a new window
start "Vite Dev Server" cmd /c "npm run dev"

:: Run the standard PHP server on the correct port
php artisan serve --port=42069
