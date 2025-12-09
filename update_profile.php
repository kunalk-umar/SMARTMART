<?php
session_start();
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($_SESSION['user_id']) && $data) {
    $phone = $data['phone'];
    $address = $data['address'];
    $id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssi", $phone, $address, $id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Profile Updated!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update Failed"]);
    }
}
?>