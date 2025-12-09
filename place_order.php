<?php
session_start();
include 'db_connect.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login first!"]);
    exit();
}

if ($data) {
    $user_id = $_SESSION['user_id'];
    $total = $data['total'];
    // Capture the payment method sent from JS
    $payment = isset($data['payment_method']) ? $data['payment_method'] : 'COD';

    // SQL Query updated to include payment_method
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $user_id, $total, $payment); // i=int, d=decimal, s=string
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            $stmt_item->bind_param("isid", $order_id, $item['name'], $item['quantity'], $item['price']);
            $stmt_item->execute();
        }
        echo json_encode(["status" => "success", "message" => "Order Placed!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
    }
}
?>