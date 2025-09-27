/**
 * API Client for Total Wine & More
 * Handles all API communication with PHP backend
 */

class APIClient {
  constructor() {
    this.baseURL = "/api/";
    this.defaultHeaders = {
      "Content-Type": "application/json",
    };
  }

  /**
   * Make HTTP request
   */
  async request(endpoint, options = {}) {
    const url = this.baseURL + endpoint;
    const config = {
      headers: { ...this.defaultHeaders, ...options.headers },
      ...options,
    };

    try {
      const response = await fetch(url, config);

      // Check if response is ok first
      if (!response.ok) {
        const errorText = await response.text();
        console.error("API Error Response:", errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }

      // Get response text first to check if it's empty
      const responseText = await response.text();

      if (!responseText || responseText.trim() === "") {
        throw new Error("Empty response received from server");
      }

      // Try to parse JSON
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error("JSON Parse Error:", parseError);
        console.error("Response text:", responseText);
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }

      return data;
    } catch (error) {
      console.error("API Request failed:", error);
      throw error;
    }
  }

  /**
   * Create a new order
   */
  async createOrder(orderData) {
    return await this.request("orders", {
      method: "POST",
      body: JSON.stringify(orderData),
    });
  }

  /**
   * Track an order by ID
   */
  async trackOrder(orderId) {
    return await this.request(
      `orders/track?orderId=${encodeURIComponent(orderId)}`
    );
  }

  /**
   * Get orders with filters
   */
  async getOrders(filters = {}) {
    const params = new URLSearchParams();

    if (filters.page) params.append("page", filters.page);
    if (filters.limit) params.append("limit", filters.limit);
    if (filters.status) params.append("status", filters.status);
    if (filters.paymentStatus)
      params.append("paymentStatus", filters.paymentStatus);

    const queryString = params.toString();
    const endpoint = queryString ? `orders?${queryString}` : "orders";

    return await this.request(endpoint);
  }

  /**
   * Get customer orders by email (legacy method)
   */
  async getCustomerOrders(email) {
    return await this.request(
      `orders/customer?email=${encodeURIComponent(email)}`
    );
  }

  /**
   * Get authenticated customer orders
   */
  async getCustomerOrdersAuth() {
    const sessionToken = localStorage.getItem("sessionToken");
    return await this.request("orders/customer", {
      headers: {
        ...this.defaultHeaders,
        Authorization: `Bearer ${sessionToken}`,
      },
    });
  }

  /**
   * Update order status
   */
  async updateOrderStatus(orderId, status, changedBy = "system", reason = "") {
    return await this.request("orders/update-status", {
      method: "PUT",
      body: JSON.stringify({
        orderId,
        status,
        changedBy,
        reason,
      }),
    });
  }

  /**
   * Update payment status
   */
  async updatePaymentStatus(
    orderId,
    paymentStatus,
    changedBy = "system",
    reason = ""
  ) {
    return await this.request("orders/update-payment", {
      method: "PUT",
      body: JSON.stringify({
        orderId,
        paymentStatus,
        changedBy,
        reason,
      }),
    });
  }

  /**
   * Get admin dashboard data
   */
  async getDashboardData() {
    return await this.request("admin/dashboard");
  }

  /**
   * Admin login
   */
  async adminLogin(username, password) {
    return await this.request("admin/login", {
      method: "POST",
      body: JSON.stringify({ username, password }),
    });
  }

  /**
   * Send order notification
   */
  async sendOrderNotification(orderId, type) {
    return await this.request("orders/notify", {
      method: "POST",
      body: JSON.stringify({
        orderId: orderId,
        type: type,
      }),
    });
  }

  /**
   * Check API health
   */
  async checkHealth() {
    return await this.request("health");
  }
}

// Create global API client instance
window.apiClient = new APIClient();

/**
 * Order Management Functions
 * These replace the localStorage functions
 */

// Generate Order ID (same as before)
function generateOrderId() {
  const timestamp = Date.now().toString().slice(-6);
  const random = Math.floor(Math.random() * 1000)
    .toString()
    .padStart(3, "0");
  return `ORD-${timestamp}${random}`;
}

// Create Order (replaces localStorage version)
async function createOrder(orderData) {
  try {
    // Calculate total amount
    let totalAmount = 0;
    orderData.items.forEach((item) => {
      const price = parseFloat(item.price.replace(/[^\d.]/g, ""));
      totalAmount += price * item.quantity;
    });

    // Prepare data for API
    const apiData = {
      customerInfo: orderData.customerInfo,
      items: orderData.items,
      paymentMethod: orderData.paymentMethod,
      total: totalAmount.toFixed(2),
    };

    const response = await apiClient.createOrder(apiData);

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to create order:", error);
    throw error;
  }
}

// Track Order (replaces localStorage version)
async function trackOrder(orderId) {
  try {
    const response = await apiClient.trackOrder(orderId);

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to track order:", error);
    throw error;
  }
}

// Get Customer Orders (replaces localStorage version)
async function getCustomerOrders(email) {
  try {
    const response = await apiClient.getCustomerOrders(email);

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to get customer orders:", error);
    throw error;
  }
}

// Get Customer Orders (authenticated version)
async function getCustomerOrdersAuth() {
  try {
    const response = await apiClient.getCustomerOrdersAuth();

    if (response.status === 200) {
      return response.data.orders;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to get customer orders:", error);
    throw error;
  }
}

// Update Order Status (for admin)
async function updateOrderStatus(
  orderId,
  status,
  changedBy = "admin",
  reason = ""
) {
  try {
    const response = await apiClient.updateOrderStatus(
      orderId,
      status,
      changedBy,
      reason
    );

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to update order status:", error);
    throw error;
  }
}

// Update Payment Status (for admin)
async function updatePaymentStatus(
  orderId,
  paymentStatus,
  changedBy = "admin",
  reason = ""
) {
  try {
    const response = await apiClient.updatePaymentStatus(
      orderId,
      paymentStatus,
      changedBy,
      reason
    );

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to update payment status:", error);
    throw error;
  }
}

// Get Dashboard Data (for admin)
async function getDashboardData() {
  try {
    const response = await apiClient.getDashboardData();

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to get dashboard data:", error);
    throw error;
  }
}

// Send Order Notification
async function sendOrderNotification(orderId, type) {
  try {
    const response = await apiClient.sendOrderNotification(orderId, type);

    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to send notification:", error);
    throw error;
  }
}

// Admin Login
async function adminLogin(username, password) {
  try {
    const response = await apiClient.adminLogin(username, password);

    if (response.status === 200) {
      // Store admin session
      localStorage.setItem("adminToken", response.data.token);
      localStorage.setItem("adminUser", JSON.stringify(response.data.admin));
      return response.data;
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error("Failed to login:", error);
    throw error;
  }
}

// Check if admin is logged in
function isAdminLoggedIn() {
  return localStorage.getItem("adminToken") !== null;
}

// Admin logout
function adminLogout() {
  localStorage.removeItem("adminToken");
  localStorage.removeItem("adminUser");
}

// Get current admin user
function getCurrentAdmin() {
  const adminData = localStorage.getItem("adminUser");
  return adminData ? JSON.parse(adminData) : null;
}

// Show API error message
function showAPIError(message) {
  // Create error notification
  const errorDiv = document.createElement("div");
  errorDiv.className = "api-error-message";
  errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #dc3545;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-weight: 500;
        font-size: 14px;
        max-width: 350px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
  errorDiv.textContent = message;
  document.body.appendChild(errorDiv);

  // Animate in
  setTimeout(() => {
    errorDiv.style.transform = "translateX(0)";
  }, 100);

  // Remove after 5 seconds
  setTimeout(() => {
    errorDiv.style.transform = "translateX(100%)";
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.parentNode.removeChild(errorDiv);
      }
    }, 300);
  }, 5000);
}

// Show API success message
function showAPISuccess(message) {
  // Create success notification
  const successDiv = document.createElement("div");
  successDiv.className = "api-success-message";
  successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-weight: 500;
        font-size: 14px;
        max-width: 350px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
  successDiv.textContent = message;
  document.body.appendChild(successDiv);

  // Animate in
  setTimeout(() => {
    successDiv.style.transform = "translateX(0)";
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    successDiv.style.transform = "translateX(100%)";
    setTimeout(() => {
      if (successDiv.parentNode) {
        successDiv.parentNode.removeChild(successDiv);
      }
    }, 300);
  }, 3000);
}

// Test API connection
async function testAPIConnection() {
  try {
    const response = await apiClient.checkHealth();
    console.log("✅ API Connection successful:", response);
    return true;
  } catch (error) {
    console.error("❌ API Connection failed:", error);
    showAPIError("API connection failed. Please check your server.");
    return false;
  }
}

// Initialize API connection test on page load
document.addEventListener("DOMContentLoaded", function () {
  // Test API connection
  testAPIConnection();

  console.log("🚀 API Client initialized");
  console.log("Available functions:");
  console.log("- createOrder(orderData)");
  console.log("- trackOrder(orderId)");
  console.log("- getCustomerOrders(email)");
  console.log("- updateOrderStatus(orderId, status)");
  console.log("- updatePaymentStatus(orderId, paymentStatus)");
  console.log("- getDashboardData()");
  console.log("- adminLogin(username, password)");
});
