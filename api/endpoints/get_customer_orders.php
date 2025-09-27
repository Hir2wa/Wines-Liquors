<?php
// get_customer_orders.php
// Required headers already set in index.php

// Get user ID from session or request
$user_id = null;

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Check if user is logged in via session token
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $auth_header);
    
    // Get user ID from session token
    
    $query = "SELECT user_id FROM user_sessions WHERE session_token = :token AND expires_at > CURRENT_TIMESTAMP";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($session) {
        $user_id = $session['user_id'];
    }
}

// If no session token, try to get from request body
if (!$user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['user_id'])) {
        $user_id = $data['user_id'];
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(array("message" => "User not authenticated"));
    exit();
}

// Get user email for order lookup

$query = "SELECT email FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(array("message" => "User not found"));
    exit();
}

// Get query parameters for filtering and pagination
$page = intval($_GET['page'] ?? 1);
$limit = intval($_GET['limit'] ?? 10);
$status = $_GET['status'] ?? null;
$paymentStatus = $_GET['paymentStatus'] ?? null;

// Validate parameters
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 100) $limit = 10;

// Validate status values
$validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
$validPaymentStatuses = ['pending', 'approved', 'rejected'];

if ($status && !in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid status value"));
    exit();
}

if ($paymentStatus && !in_array($paymentStatus, $validPaymentStatuses)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid payment status value"));
    exit();
}

try {
    // Get orders by customer email with pagination
    $offset = ($page - 1) * $limit;
    
    $where_conditions = ["o.customer_email = :email"];
    $params = [':email' => $user['email']];

    if ($status) {
        $where_conditions[] = "o.status = :status";
        $params[':status'] = $status;
    }

    if ($paymentStatus) {
        $where_conditions[] = "o.payment_status = :payment_status";
        $params[':payment_status'] = $paymentStatus;
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Get orders with items
    $sql = "
        SELECT o.*, 
               STRING_AGG(
                   CONCAT(oi.product_name, '|', oi.product_price, '|', oi.quantity, '|', COALESCE(oi.product_image, ''))
                   , '||'
               ) as items_data
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        " . $where_clause . "
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM orders o " . $where_clause;
    $count_stmt = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Format orders
    $formatted_orders = [];
    foreach ($orders as $order_data) {
        $formatted = [
            'orderId' => $order_data['id'],
            'customerInfo' => [
                'email' => $order_data['customer_email'],
                'phone' => $order_data['customer_phone'],
                'firstName' => $order_data['customer_first_name'],
                'lastName' => $order_data['customer_last_name'],
                'address' => $order_data['customer_address'],
                'city' => $order_data['customer_city'],
                'country' => $order_data['customer_country']
            ],
            'total' => number_format($order_data['total_amount']) . 'frw',
            'totalAmount' => $order_data['total_amount'],
            'status' => $order_data['status'],
            'paymentStatus' => $order_data['payment_status'],
            'paymentMethod' => $order_data['payment_method'],
            'date' => $order_data['created_at'],
            'updatedAt' => $order_data['updated_at']
        ];

        // Parse items if they exist
        if (!empty($order_data['items_data'])) {
            $items = [];
            $items_array = explode('||', $order_data['items_data']);
            
            foreach ($items_array as $item_string) {
                if (!empty($item_string)) {
                    $item_parts = explode('|', $item_string);
                    if (count($item_parts) >= 3) {
                        // Clean and validate price
                        $price = $item_parts[1];
                        $clean_price = preg_replace('/[^\d.]/', '', $price); // Remove non-numeric characters except decimal point
                        $numeric_price = is_numeric($clean_price) ? (float)$clean_price : 0;
                        
                        $items[] = [
                            'name' => $item_parts[0],
                            'price' => number_format($numeric_price) . 'frw',
                            'quantity' => (int)$item_parts[2],
                            'image' => $item_parts[3] ?? null
                        ];
                    }
                }
            }
            $formatted['items'] = $items;
        }

        $formatted_orders[] = $formatted;
    }

    http_response_code(200);
    echo json_encode(array(
        "status" => 200,
        "message" => "Customer orders retrieved successfully",
        "data" => [
            'orders' => $formatted_orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to retrieve orders: " . $e->getMessage()));
}
?>
