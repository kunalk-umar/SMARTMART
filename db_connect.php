<?php
$servername = "localhost";
$username = "root";  
$password = "";      // <--- MUST BE EMPTY
$dbname = "smartmart_db"; // <--- MUST MATCH DATABASE NAME

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>