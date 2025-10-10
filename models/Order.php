<?php
/**
 * Order Model
 * Handles all order-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class Order {
    private $conn;
    private $table_name = "orders";
    private $items_table = "order_items";
    private $logs_table = "order_status_logs";
    private $payment_codes_table = "payment_codes";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new order
     */
    public function create($orderData) {
        try {
            $this->conn->beginTransaction();

            // Insert order
            $sql = "
                INSERT INTO " . $this->table_name . " 
                (id, customer_email, customer_phone, customer_first_name, customer_last_name, 
                 customer_address, customer_city, customer_country, total_amount, 
                 payment_method, status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $orderData['orderId'],
                $orderData['customerInfo']['email'],
                $orderData['customerInfo']['phone'],
                $orderData['customerInfo']['firstName'],
                $orderData['customerInfo']['lastName'],
                $orderData['customerInfo']['address'],
                $orderData['customerInfo']['city'],
                $orderData['customerInfo']['country'],
                $orderData['totalAmount'],
                $orderData['paymentMethod'],
                $orderData['status'],
                $orderData['paymentStatus']
            ]);

            if (!$result) {
                throw new Exception("Failed to create order");
            }

            // Insert order items
            if (!empty($orderData['items'])) {
                $item_sql = "
                    INSERT INTO " . $this->items_table . " 
                    (order_id, product_name, product_price, quantity, product_image) 
                    VALUES (?, ?, ?, ?, ?)
                ";

                $item_stmt = $this->conn->prepare($item_sql);
                
                foreach ($orderData['items'] as $item) {
                    $item_stmt->execute([
                        $orderData['orderId'],
                        $item['name'],
                        $item['price'],
                        $item['quantity'],
                        $item['image'] ?? null
                    ]);
                }
            }

            // Log initial status
            $this->logStatusChange($orderData['orderId'], null, $orderData['status'], 'system', 'Order created');

            $this->conn->commit();
            return $orderData['orderId'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Get order by ID
     */
    public function getById($orderId) {
        $sql = "
            SELECT o.*, 
                   STRING_AGG(
                       CONCAT(oi.product_name, '|', oi.product_price, '|', oi.quantity, '|', COALESCE(oi.product_image, ''))
                       , '||'
                   ) as items_data
            FROM " . $this->table_name . " o
            LEFT JOIN " . $this->items_table . " oi ON o.id = oi.order_id
            WHERE o.id = ?
            GROUP BY o.id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $order = $this->formatOrderData($order);
        }

        return $order;
    }

    /**
     * Get all orders with pagination
     */
    public function getAll($page = 1, $limit = 10, $status = null, $paymentStatus = null) {
        $offset = ($page - 1) * $limit;
        
        $where_conditions = [];
        $params = [];

        if ($status) {
            $where_conditions[] = "o.status = ?";
            $params[] = $status;
        }

        if ($paymentStatus) {
            $where_conditions[] = "o.payment_status = ?";
            $params[] = $paymentStatus;
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        $sql = "
            SELECT o.*, 
                   COUNT(oi.id) as item_count
            FROM " . $this->table_name . " o
            LEFT JOIN " . $this->items_table . " oi ON o.id = oi.order_id
            " . $where_clause . "
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " o " . $where_clause;
        $count_params = array_slice($params, 0, -2); // Remove limit and offset
        $count_stmt = $this->conn->prepare($count_sql);
        $count_stmt->execute($count_params);
        $total = $count_stmt->fetch()['total'];

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $newStatus, $changedBy = 'system', $reason = '') {
        try {
            // Check if we're already in a transaction
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
            }

            // Get current status
            $current_sql = "SELECT status FROM " . $this->table_name . " WHERE id = ?";
            $current_stmt = $this->conn->prepare($current_sql);
            $current_stmt->execute([$orderId]);
            $current_order = $current_stmt->fetch();

            if (!$current_order) {
                throw new Exception("Order not found");
            }

            $oldStatus = $current_order['status'];

            // Update status
            $sql = "UPDATE " . $this->table_name . " SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$newStatus, $orderId]);

            if (!$result) {
                throw new Exception("Failed to update order status");
            }

            // Log status change
            $this->logStatusChange($orderId, $oldStatus, $newStatus, $changedBy, $reason);

            if ($this->conn->inTransaction()) {
                $this->conn->commit();
            }
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $newPaymentStatus, $changedBy = 'system', $reason = '') {
        try {
            // Check if we're already in a transaction
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
            }

            // Get current payment status
            $current_sql = "SELECT payment_status FROM " . $this->table_name . " WHERE id = ?";
            $current_stmt = $this->conn->prepare($current_sql);
            $current_stmt->execute([$orderId]);
            $current_order = $current_stmt->fetch();

            if (!$current_order) {
                throw new Exception("Order not found");
            }

            $oldPaymentStatus = $current_order['payment_status'];

            // Update payment status
            $sql = "UPDATE " . $this->table_name . " SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$newPaymentStatus, $orderId]);

            if (!$result) {
                throw new Exception("Failed to update payment status");
            }

            // If payment is approved, automatically move to processing
            if ($newPaymentStatus === 'approved' && $oldPaymentStatus === 'pending') {
                $this->updateStatus($orderId, 'processing', $changedBy, 'Payment approved');
            }

            if ($this->conn->inTransaction()) {
                $this->conn->commit();
            }
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Get orders by customer email
     */
    public function getByCustomerEmail($email) {
        $sql = "
            SELECT o.*, 
                   COUNT(oi.id) as item_count
            FROM " . $this->table_name . " o
            LEFT JOIN " . $this->items_table . " oi ON o.id = oi.order_id
            WHERE o.customer_email = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];

        // Total orders
        $sql = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch()['total'];

        // Total revenue
        $sql = "SELECT SUM(total_amount) as total FROM " . $this->table_name . " WHERE payment_status = 'approved'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

        // Unique customers (excluding admin users)
        $sql = "SELECT COUNT(DISTINCT customer_email) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['unique_customers'] = $stmt->fetch()['total'];

        // Pending payments
        $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE payment_status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['pending_payments'] = $stmt->fetch()['total'];

        return $stats;
    }

    /**
     * Get detailed sales report data
     */
    public function getSalesReport() {
        $report = [];
        
        try {
            // Get monthly sales data for the last 12 months
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        COALESCE(AVG(total_amount), 0) as avg_order_value
                    FROM " . $this->table_name . " 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $report['monthly_sales'] = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $report['monthly_sales'] = [];
        }
        
        try {
            // Get sales by payment method
            $sql = "SELECT 
                        COALESCE(payment_method, 'unknown') as payment_method,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM " . $this->table_name . " 
                    GROUP BY payment_method
                    ORDER BY total_revenue DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $report['sales_by_payment_method'] = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $report['sales_by_payment_method'] = [];
        }
        
        try {
            // Get sales by status
            $sql = "SELECT 
                        COALESCE(status, 'unknown') as status,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM " . $this->table_name . " 
                    GROUP BY status
                    ORDER BY total_revenue DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $report['sales_by_status'] = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $report['sales_by_status'] = [];
        }
        
        try {
            // Get top customers (simplified query)
            $sql = "SELECT 
                        customer_email,
                        customer_first_name,
                        customer_last_name,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as total_spent
                    FROM " . $this->table_name . " 
                    GROUP BY customer_email, customer_first_name, customer_last_name
                    ORDER BY total_spent DESC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $report['top_customers'] = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $report['top_customers'] = [];
        }
        
        try {
            // Get daily sales for the last 30 days
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as order_count,
                        COALESCE(SUM(total_amount), 0) as daily_revenue
                    FROM " . $this->table_name . " 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $report['daily_sales'] = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $report['daily_sales'] = [];
        }
        
        return $report;
    }

    /**
     * Log status changes
     */
    private function logStatusChange($orderId, $oldStatus, $newStatus, $changedBy, $reason) {
        $sql = "
            INSERT INTO " . $this->logs_table . " 
            (order_id, old_status, new_status, changed_by, change_reason) 
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId, $oldStatus, $newStatus, $changedBy, $reason]);
    }

    /**
     * Format order data for API response
     */
    private function formatOrderData($order) {
        $formatted = [
            'orderId' => $order['id'],
            'customerInfo' => [
                'email' => $order['customer_email'],
                'phone' => $order['customer_phone'],
                'firstName' => $order['customer_first_name'],
                'lastName' => $order['customer_last_name'],
                'address' => $order['customer_address'],
                'city' => $order['customer_city'],
                'country' => $order['customer_country']
            ],
            'total' => number_format($order['total_amount']) . 'frw',
            'totalAmount' => $order['total_amount'],
            'status' => $order['status'],
            'paymentStatus' => $order['payment_status'],
            'paymentMethod' => $order['payment_method'],
            'date' => $order['created_at'],
            'updatedAt' => $order['updated_at']
        ];

        // Parse items if they exist
        if (!empty($order['items_data'])) {
            $items = [];
            $items_array = explode('||', $order['items_data']);
            
            foreach ($items_array as $item_string) {
                if (!empty($item_string)) {
                    $item_parts = explode('|', $item_string);
                    if (count($item_parts) >= 3) {
                        $items[] = [
                            'name' => $item_parts[0],
                            'price' => number_format($item_parts[1]) . 'frw',
                            'quantity' => (int)$item_parts[2],
                            'image' => $item_parts[3] ?? null
                        ];
                    }
                }
            }
            $formatted['items'] = $items;
        }

        return $formatted;
    }
    
    /**
     * Generate mobile money payment code for an order
     */
    public function generatePaymentCode($orderId, $amount, $phoneNumber) {
        try {
            // Generate a unique 6-digit code for USSD format
            $codeNumber = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $paymentCode = "*182*8*1*{$codeNumber}#";
            
            // Check if code already exists (very unlikely but good practice)
            while ($this->paymentCodeExists($paymentCode)) {
                $codeNumber = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $paymentCode = "*182*8*1*{$codeNumber}#";
            }
            
            // Set expiration time (24 hours from now)
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $sql = "INSERT INTO " . $this->payment_codes_table . " 
                    (order_id, payment_code, amount, phone_number, expires_at, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$orderId, $paymentCode, $amount, $phoneNumber, $expiresAt]);
            
            if ($result) {
                return [
                    'success' => true,
                    'payment_code' => $paymentCode,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'expires_at' => $expiresAt,
                    'instructions' => "Dial {$paymentCode} to pay {$amount}frw for your order"
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to generate payment code'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error generating payment code: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if payment code exists
     */
    private function paymentCodeExists($paymentCode) {
        $sql = "SELECT id FROM " . $this->payment_codes_table . " WHERE payment_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$paymentCode]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get payment code for an order
     */
    public function getPaymentCode($orderId) {
        $sql = "SELECT * FROM " . $this->payment_codes_table . " 
                WHERE order_id = ? AND status = 'pending' AND expires_at > NOW() 
                ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'success' => true,
                'payment_code' => $result['payment_code'],
                'amount' => $result['amount'],
                'phone_number' => $result['phone_number'],
                'expires_at' => $result['expires_at'],
                'created_at' => $result['created_at']
            ];
        }
        
        return ['success' => false, 'message' => 'No active payment code found'];
    }
    
    /**
     * Verify payment code (admin function)
     */
    public function verifyPaymentCode($orderId, $paymentCode, $verifiedBy) {
        try {
            // Check if we're already in a transaction
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
            }
            
            // Check if payment code exists and is valid
            $sql = "SELECT * FROM " . $this->payment_codes_table . " 
                    WHERE order_id = ? AND payment_code = ? AND status = 'pending'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId, $paymentCode]);
            $paymentRecord = $stmt->fetch();
            
            if (!$paymentRecord) {
                throw new Exception('Invalid or expired payment code');
            }
            
            // Mark payment code as verified
            $update_sql = "UPDATE " . $this->payment_codes_table . " 
                          SET status = 'verified', verified_at = NOW(), verified_by = ? 
                          WHERE id = ?";
            
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->execute([$verifiedBy, $paymentRecord['id']]);
            
            // Update order payment status
            $this->updatePaymentStatus($orderId, 'approved', $verifiedBy, 'Mobile money payment verified');
            
            if ($this->conn->inTransaction()) {
                $this->conn->commit();
            }
            
            return [
                'success' => true,
                'message' => 'Payment verified successfully',
                'order_id' => $orderId,
                'amount' => $paymentRecord['amount']
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get all pending payment codes (admin function)
     */
    public function getPendingPaymentCodes() {
        $sql = "SELECT pc.*, o.customer_first_name, o.customer_last_name, o.customer_phone, o.customer_email
                FROM " . $this->payment_codes_table . " pc
                JOIN " . $this->table_name . " o ON pc.order_id = o.id
                WHERE pc.status = 'pending' AND pc.expires_at > NOW()
                ORDER BY pc.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $paymentCodes = [];
        foreach ($results as $result) {
            $paymentCodes[] = [
                'id' => $result['id'],
                'order_id' => $result['order_id'],
                'payment_code' => $result['payment_code'],
                'amount' => $result['amount'],
                'phone_number' => $result['phone_number'],
                'customer_name' => $result['customer_first_name'] . ' ' . $result['customer_last_name'],
                'customer_phone' => $result['customer_phone'],
                'customer_email' => $result['customer_email'],
                'created_at' => $result['created_at'],
                'expires_at' => $result['expires_at']
            ];
        }
        
        return $paymentCodes;
    }
}
?>
