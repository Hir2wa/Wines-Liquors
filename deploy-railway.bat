@echo off
echo 🚂 Starting Railway deployment process...

REM Check if Railway CLI is installed
railway --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Railway CLI not found. Installing...
    npm install -g @railway/cli
)

REM Login to Railway
echo 🔐 Logging into Railway...
railway login

REM Initialize Railway project
echo 📱 Initializing Railway project...
railway init

REM Add PostgreSQL service
echo 🐘 Adding PostgreSQL database...
railway add postgresql

REM Deploy to Railway
echo 🚀 Deploying to Railway...
railway up

REM Get the deployed URL
echo 🌐 Getting deployment URL...
railway domain

echo ✅ Railway deployment complete!
echo 📱 Your app is now live on Railway!
echo 🐘 PostgreSQL database is ready!
echo 👥 Share the URL with your team for testing!
pause
