<?php
session_start();
include 'db_connect.php';

$action = $_POST['action'];
$email = $_POST['email'];
$password = $_POST['password'];

if ($action == 'register') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, email, password) VALUES ('User', '$email', '$hashed')");
    echo "<script>alert('Registered! Please Login.'); window.location='login.html';</script>";
} 
elseif ($action == 'login') {
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    $row = $result->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        echo "<script>alert('Login Successful!'); window.location='index.html';</script>";
    } else {
        echo "<script>alert('Invalid details'); window.location='login.html';</script>";
    }
}
?>