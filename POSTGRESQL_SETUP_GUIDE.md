# 🐘 PostgreSQL Setup Guide for Total Wine & More

## ✅ **Complete PostgreSQL Integration**

Your PHP backend is now configured to work with PostgreSQL! Here's how to set it up.

---

## **📋 Prerequisites**

### **1. Install PostgreSQL**
- **Download PostgreSQL** from: https://www.postgresql.org/download/
- **Choose your operating system** (Windows/Mac/Linux)
- **Install PostgreSQL** with default settings
- **Remember the password** you set for the `postgres` user

### **2. Install PHP with PostgreSQL Support**
You'll need PHP with the `pdo_pgsql` extension:

#### **Windows:**
- Download PHP from: https://windows.php.net/download/
- Or use XAMPP with PostgreSQL support

#### **Mac:**
```bash
# Using Homebrew
brew install php
brew install postgresql
```

#### **Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php php-pgsql postgresql postgresql-contrib
```

---

## **🔧 Configuration Steps**

### **Step 1: Update Database Configuration**

Edit `config/database.php` and update these values:

```php
private $host = 'localhost';
private $db_name = 'total_wine_orders';
private $username = 'postgres';
private $password = 'your_postgres_password'; // Change this!
private $port = '5432';
```

### **Step 2: Start PostgreSQL Service**

#### **Windows:**
- Open **Services** (services.msc)
- Start **postgresql-x64-XX** service
- Or use pgAdmin to start the service

#### **Mac:**
```bash
brew services start postgresql
```

#### **Linux:**
```bash
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### **Step 3: Create Database**

#### **Option A: Using psql (Command Line)**
```bash
# Connect to PostgreSQL
psql -U postgres

# Create database
CREATE DATABASE total_wine_orders;

# Exit psql
\q
```

#### **Option B: Using pgAdmin (GUI)**
1. Open **pgAdmin**
2. Connect to your PostgreSQL server
3. Right-click **Databases** → **Create** → **Database**
4. Name: `total_wine_orders`
5. Click **Save**

### **Step 4: Run Database Setup**

```bash
# Navigate to your project directory
cd C:\Users\Aime\Desktop\Wines-Liquors

# Run the setup script
php setup/database_setup.php
```

Or visit: `http://localhost/Wines-Liquors/setup/database_setup.php`

---

## **🧪 Testing the Setup**

### **1. Test Database Connection**
```bash
# Test connection
php -r "
require_once 'config/database.php';
\$db = new Database();
\$conn = \$db->getConnection();
if (\$conn) {
    echo '✅ PostgreSQL connection successful!';
} else {
    echo '❌ Connection failed!';
}
"
```

### **2. Test API Endpoints**
- **Health Check**: `http://localhost/Wines-Liquors/api/health`
- **Integration Test**: `http://localhost/Wines-Liquors/test-php-integration.html`

### **3. Verify Tables Created**
```sql
-- Connect to your database
psql -U postgres -d total_wine_orders

-- List tables
\dt

-- Check orders table structure
\d orders
```

---

## **📊 PostgreSQL-Specific Features**

### **Database Schema Differences**

#### **Orders Table:**
```sql
CREATE TABLE orders (
    id VARCHAR(20) PRIMARY KEY,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_first_name VARCHAR(100) NOT NULL,
    customer_last_name VARCHAR(100) NOT NULL,
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(100) NOT NULL,
    customer_country VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' 
        CHECK (status IN ('pending', 'processing', 'shipped', 'completed', 'cancelled')),
    payment_status VARCHAR(20) DEFAULT 'pending' 
        CHECK (payment_status IN ('pending', 'approved', 'rejected')),
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### **Order Items Table:**
```sql
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,  -- PostgreSQL auto-increment
    order_id VARCHAR(20) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    product_image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

---

## **🔐 Security Configuration**

### **1. Update PostgreSQL Password**
```sql
-- Connect as postgres user
psql -U postgres

-- Change password
ALTER USER postgres PASSWORD 'your_secure_password';

-- Create dedicated user for your app (recommended)
CREATE USER wine_app_user WITH PASSWORD 'secure_app_password';
GRANT ALL PRIVILEGES ON DATABASE total_wine_orders TO wine_app_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO wine_app_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO wine_app_user;
```

### **2. Update Configuration**
```php
// In config/database.php
private $username = 'wine_app_user';
private $password = 'secure_app_password';
```

---

## **🚨 Troubleshooting**

### **Common Issues:**

1. **"PDO extension not found"**
   ```bash
   # Install PHP PostgreSQL extension
   # Windows: Enable pdo_pgsql in php.ini
   # Mac: brew install php-pgsql
   # Linux: sudo apt install php-pgsql
   ```

2. **"Connection refused"**
   - Check PostgreSQL is running
   - Verify port 5432 is open
   - Check firewall settings

3. **"Authentication failed"**
   - Verify username/password
   - Check pg_hba.conf configuration
   - Ensure user has database access

4. **"Database does not exist"**
   - Create the database first
   - Check database name spelling
   - Verify user has CREATE privileges

### **Debug Connection:**
```php
// Add to any PHP file for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=total_wine_orders", 
                   "postgres", "your_password");
    echo "✅ Connection successful!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
```

---

## **🎯 Environment Variables (Optional)**

### **Using Environment Variables:**
Create a `.env` file in your project root:
```env
DB_HOST=localhost
DB_NAME=total_wine_orders
DB_USER=postgres
DB_PASS=your_password
DB_PORT=5432
```

Update `config/database.php`:
```php
// Load from environment variables
private $host = $_ENV['DB_HOST'] ?? 'localhost';
private $db_name = $_ENV['DB_NAME'] ?? 'total_wine_orders';
private $username = $_ENV['DB_USER'] ?? 'postgres';
private $password = $_ENV['DB_PASS'] ?? '';
private $port = $_ENV['DB_PORT'] ?? '5432';
```

---

## **🚀 Quick Start Commands**

### **Complete Setup:**
```bash
# 1. Start PostgreSQL
# Windows: Start service in Services
# Mac: brew services start postgresql
# Linux: sudo systemctl start postgresql

# 2. Create database
psql -U postgres -c "CREATE DATABASE total_wine_orders;"

# 3. Update config/database.php with your password

# 4. Run setup
php setup/database_setup.php

# 5. Test API
curl http://localhost/Wines-Liquors/api/health
```

---

## **🎉 Success Indicators**

### **✅ Everything Working If:**
1. PostgreSQL service is running
2. Database `total_wine_orders` exists
3. Tables are created successfully
4. API health check returns success
5. Order creation works
6. Admin dashboard loads data

### **📈 PostgreSQL Advantages:**
- ✅ **Better performance** for complex queries
- ✅ **Advanced data types** (JSON, arrays, etc.)
- ✅ **Excellent concurrency** handling
- ✅ **Robust ACID compliance**
- ✅ **Great for production** environments

---

**🎊 Your wine store now runs on PostgreSQL! 🐘🍷**

*PostgreSQL provides excellent performance and reliability for your order management system.*
