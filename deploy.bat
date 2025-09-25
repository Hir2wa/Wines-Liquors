@echo off
echo 🚀 Starting deployment process...

REM Check if Heroku CLI is installed
heroku --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Heroku CLI not found. Please install it first:
    echo    https://devcenter.heroku.com/articles/heroku-cli
    pause
    exit /b 1
)

REM Check if logged in to Heroku
heroku auth:whoami >nul 2>&1
if %errorlevel% neq 0 (
    echo 🔐 Please login to Heroku first:
    heroku login
)

REM Create Heroku app (if it doesn't exist)
echo 📱 Creating Heroku app...
heroku create wines-liquors-%RANDOM% || echo App already exists or name taken

REM Add PostgreSQL addon
echo 🐘 Adding PostgreSQL database...
heroku addons:create heroku-postgresql:mini

REM Get database URL
echo 🔗 Getting database URL...
heroku config:get DATABASE_URL

REM Deploy to Heroku
echo 🚀 Deploying to Heroku...
git push heroku main

REM Run database setup
echo 🗄️ Setting up database...
heroku run php setup/database_setup.php

REM Open the app
echo 🌐 Opening your app...
heroku open

echo ✅ Deployment complete!
echo 📱 Your app is now live!
echo 🐘 Database is ready for use!
pause
