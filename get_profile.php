<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = [];

// 1. Fetch User Details
$stmt = $conn->prepare("SELECT username, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$response['user'] = $stmt->get_result()->fetch_assoc();

// 2. Fetch Order History
$orders_sql = "SELECT id, total_price, payment_method, order_date FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$orders_result = $conn->query($orders_sql);

$orders = [];
while($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}
$response['orders'] = $orders;

echo json_encode($response);
?>