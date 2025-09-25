# PostgreSQL Deployment Guide for Wines & Liquors Website

## 🚀 Quick Deployment Options

### Option 1: Heroku (Free - Best for Testing)

#### Step 1: Prepare Your Project
1. Create a `composer.json` file for PHP dependencies
2. Create a `Procfile` for Heroku
3. Update database configuration

#### Step 2: Deploy to Heroku
```bash
# Install Heroku CLI
# Download from: https://devcenter.heroku.com/articles/heroku-cli

# Login to Heroku
heroku login

# Create new app
heroku create your-wine-app-name

# Add PostgreSQL addon
heroku addons:create heroku-postgresql:mini

# Get database URL
heroku config:get DATABASE_URL

# Deploy your code
git push heroku main
```

#### Step 3: Update Database Configuration
Update `config/database.php` to use Heroku's DATABASE_URL:
```php
<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Heroku PostgreSQL
        if (getenv('DATABASE_URL')) {
            $url = parse_url(getenv('DATABASE_URL'));
            $this->host = $url['host'];
            $this->db_name = substr($url['path'], 1);
            $this->username = $url['user'];
            $this->password = $url['pass'];
        } else {
            // Local development
            $this->host = 'localhost';
            $this->db_name = 'wines_liquors';
            $this->username = 'your_username';
            $this->password = 'your_password';
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
```

### Option 2: Railway (Free Tier)

#### Step 1: Prepare Project
1. Create `railway.json` configuration
2. Update database configuration

#### Step 2: Deploy to Railway
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project
railway init

# Add PostgreSQL service
railway add postgresql

# Deploy
railway up
```

### Option 3: DigitalOcean ($5/month - Recommended)

#### Step 1: Create Droplet
1. Go to DigitalOcean
2. Create a new droplet (Ubuntu 20.04)
3. Choose $5/month plan

#### Step 2: Setup Server
```bash
# SSH into your server
ssh root@your-server-ip

# Update system
apt update && apt upgrade -y

# Install Nginx, PHP, PostgreSQL
apt install nginx php8.1-fpm php8.1-pgsql postgresql postgresql-contrib -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Create database
sudo -u postgres createdb wines_liquors
sudo -u postgres createuser --interactive
```

#### Step 3: Deploy Your Code
```bash
# Clone your repository
git clone https://github.com/Hir2wa/Wines-Liquors.git /var/www/html

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Configure Nginx
# Create /etc/nginx/sites-available/wines-liquors
```

## 📋 Pre-Deployment Checklist

### 1. Update API URLs
Replace all `localhost:8000` with your domain:
```javascript
// In js/api-client.js
const baseURL = 'https://your-domain.com/api/';

// In all HTML files
fetch('/api/...') // This will work automatically
```

### 2. Environment Configuration
Create `.env` file:
```env
DB_HOST=your-db-host
DB_NAME=wines_liquors
DB_USER=your-username
DB_PASSWORD=your-password
DB_PORT=5432
```

### 3. Database Setup
Run your database setup script:
```bash
php setup/database_setup.php
```

### 4. File Permissions
```bash
chmod 755 api/
chmod 644 config/database.php
chmod 755 setup/
```

## 🔧 Database Migration

### Export Local Database
```bash
# Export your local PostgreSQL database
pg_dump -h localhost -U your_username -d wines_liquors > wines_liquors_backup.sql
```

### Import to Production
```bash
# Import to production database
psql -h your-production-host -U your_username -d wines_liquors < wines_liquors_backup.sql
```

## 🌐 Domain Setup

### 1. Buy Domain (Optional)
- Namecheap ($10-15/year)
- GoDaddy ($12-20/year)

### 2. Point Domain to Server
- Update DNS A record to point to your server IP
- Wait 24-48 hours for propagation

## 📱 Testing Checklist

### 1. Test All Features
- [ ] User registration
- [ ] User login
- [ ] Order creation
- [ ] Order tracking
- [ ] Admin dashboard
- [ ] Payment methods
- [ ] Email notifications

### 2. Test on Different Devices
- [ ] Desktop browsers
- [ ] Mobile browsers
- [ ] Tablet browsers

### 3. Performance Testing
- [ ] Page load speed
- [ ] Database queries
- [ ] API response times

## 🚨 Security Considerations

### 1. Environment Variables
- Never commit database passwords to Git
- Use environment variables for sensitive data

### 2. SSL Certificate
- Install Let's Encrypt SSL certificate
- Force HTTPS redirects

### 3. Database Security
- Use strong passwords
- Limit database access
- Regular backups

## 📞 Support

If you need help with deployment:
1. Check the logs: `heroku logs --tail` (for Heroku)
2. Check database connection
3. Verify file permissions
4. Test API endpoints

## 🎯 Recommended for You

**For Testing**: Use **Heroku** (free, easy setup)
**For Production**: Use **DigitalOcean** ($5/month, reliable)

Both support PostgreSQL and are easy to set up!
