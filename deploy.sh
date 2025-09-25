#!/bin/bash

# Wines & Liquors Deployment Script
echo "🚀 Starting deployment process..."

# Check if Heroku CLI is installed
if ! command -v heroku &> /dev/null; then
    echo "❌ Heroku CLI not found. Please install it first:"
    echo "   https://devcenter.heroku.com/articles/heroku-cli"
    exit 1
fi

# Check if logged in to Heroku
if ! heroku auth:whoami &> /dev/null; then
    echo "🔐 Please login to Heroku first:"
    heroku login
fi

# Create Heroku app (if it doesn't exist)
echo "📱 Creating Heroku app..."
heroku create wines-liquors-$(date +%s) || echo "App already exists or name taken"

# Add PostgreSQL addon
echo "🐘 Adding PostgreSQL database..."
heroku addons:create heroku-postgresql:mini

# Get database URL
echo "🔗 Getting database URL..."
DATABASE_URL=$(heroku config:get DATABASE_URL)
echo "Database URL: $DATABASE_URL"

# Deploy to Heroku
echo "🚀 Deploying to Heroku..."
git push heroku main

# Run database setup
echo "🗄️ Setting up database..."
heroku run php setup/database_setup.php

# Open the app
echo "🌐 Opening your app..."
heroku open

echo "✅ Deployment complete!"
echo "📱 Your app is now live at: $(heroku apps:info --json | jq -r '.app.web_url')"
echo "🐘 Database is ready for use!"
