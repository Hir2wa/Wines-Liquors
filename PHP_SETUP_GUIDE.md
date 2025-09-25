# 🚀 PHP Backend Setup Guide

## ✅ **Complete PHP Integration for Total Wine & More**

Your frontend is now fully integrated with a PHP backend! Here's how to set it up and test it.

---

## **📋 Setup Instructions**

### **1. Database Setup**

1. **Start your web server** (XAMPP, WAMP, or similar)
2. **Open phpMyAdmin** or your MySQL client
3. **Run the database setup script**:

   ```bash
   # Navigate to your project directory
   cd C:\Users\Aime\Desktop\Wines-Liquors

   # Run the setup script
   php setup/database_setup.php
   ```

   Or visit: `http://localhost/Wines-Liquors/setup/database_setup.php`

### **2. Database Configuration**

Edit `config/database.php` if needed:

```php
private $host = 'localhost';
private $db_name = 'total_wine_orders';
private $username = 'root';
private $password = ''; // Your MySQL password
```

### **3. Test API Connection**

Visit: `http://localhost/Wines-Liquors/api/health`

You should see:

```json
{
  "status": 200,
  "message": "API is running",
  "data": {
    "status": "OK",
    "timestamp": "2024-01-15 10:30:00"
  }
}
```

---

## **🔧 API Endpoints**

### **Order Management**

- `POST /api/orders` - Create new order
- `GET /api/orders/track?orderId=ORD-123456` - Track order
- `GET /api/orders/customer?email=user@example.com` - Get customer orders
- `PUT /api/orders/update-status` - Update order status
- `PUT /api/orders/update-payment` - Update payment status

### **Admin Functions**

- `GET /api/admin/dashboard` - Get dashboard data
- `POST /api/admin/login` - Admin login

---

## **🧪 Testing the Integration**

### **1. Test Order Creation**

1. Go to `Order.html`
2. Add items to cart
3. Fill out the form
4. Complete the order
5. Check if order appears in database

### **2. Test Order Tracking**

1. Use the order ID from step 1
2. Go to `OrderTracking.html`
3. Enter the order ID
4. Verify order details load

### **3. Test Admin Dashboard**

1. Go to `AdminDashboard.html`
2. Check if dashboard loads with real data
3. Test payment approval/rejection
4. Test order status updates

### **4. Test Order History**

1. Go to `OrderHistory.html`
2. Enter the email used in step 1
3. Verify order history loads

---

## **📊 Database Structure**

### **Orders Table**

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
  status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
  payment_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  payment_method VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Order Items Table**

```sql
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(20) NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  product_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  product_image VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

### **Admin Users Table**

```sql
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin', 'manager') DEFAULT 'admin',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## **🔐 Default Admin Credentials**

- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@totalwine.com`

**⚠️ Change these credentials in production!**

---

## **📱 Frontend Integration**

### **What's Updated**

- ✅ **Order.html** - Now uses PHP API for order creation
- ✅ **OrderTracking.html** - Now uses PHP API for order lookup
- ✅ **OrderHistory.html** - Now uses PHP API for customer orders
- ✅ **AdminDashboard.html** - Now uses PHP API for all admin functions
- ✅ **API Client** - New JavaScript client for all API communication

### **API Client Functions**

```javascript
// Available in all pages
createOrder(orderData);
trackOrder(orderId);
getCustomerOrders(email);
updateOrderStatus(orderId, status);
updatePaymentStatus(orderId, paymentStatus);
getDashboardData();
adminLogin(username, password);
```

---

## **🚨 Troubleshooting**

### **Common Issues**

1. **Database Connection Error**

   - Check MySQL is running
   - Verify database credentials in `config/database.php`
   - Ensure database exists

2. **API Not Responding**

   - Check web server is running
   - Verify file permissions
   - Check PHP error logs

3. **CORS Issues**

   - API includes CORS headers
   - Should work with local development

4. **Order Not Found**
   - Check order ID format (ORD-123456789)
   - Verify order exists in database

### **Debug Mode**

Add this to any PHP file for debugging:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## **🎉 Success Indicators**

### **✅ Everything Working If:**

1. Database setup completes without errors
2. API health check returns success
3. Order creation redirects to tracking page
4. Order tracking shows real data
5. Admin dashboard loads with statistics
6. Order history shows customer orders

### **📈 Performance**

- Orders are stored in MySQL database
- Real-time updates every 30 seconds
- Proper error handling and validation
- Responsive design maintained

---

## **🚀 Next Steps**

1. **Test all functionality** using the test page
2. **Add more products** to your inventory
3. **Customize admin features** as needed
4. **Set up email notifications** for orders
5. **Add payment gateway integration**
6. **Deploy to production server**

---

**🎊 Congratulations! Your wine store now has a complete PHP backend!**

_All frontend functionality is now connected to a real database with proper API endpoints._

