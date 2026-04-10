#!/bin/bash

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in your PATH."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not installed or not in your PATH."
    echo "Please install Composer from https://getcomposer.org/"
    exit 1
fi

# Check if npm is installed (needed for the build step)
if ! command -v npm &> /dev/null; then
    echo "Error: npm (Node.js) is not installed or not in your PATH."
    echo "Please install Node.js from https://nodejs.org/"
    exit 1
fi

# First-time setup: if vendor directory or .env doesn't exist, run the setup script
if [ ! -d "vendor" ] || [ ! -f ".env" ]; then
    echo "First time setup detected. Installing dependencies and building the application..."
    composer run setup
fi

URL="http://localhost:42069"

echo "Starting the application server..."

# Open the browser in the background after a 2-second delay to let the server start
(
    sleep 2
    if command -v xdg-open > /dev/null; then
        xdg-open "$URL"
    elif command -v open > /dev/null; then
        open "$URL"
    fi
) &

# Run the standard PHP server on the correct port
php artisan serve --port=42069
