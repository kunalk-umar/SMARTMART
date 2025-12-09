<?php
session_start();
include 'db_connect.php';

// 1. Security Check: Kick out non-admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h2>⛔ Access Denied</h2>
            <p>You must be an Admin to view this page.</p>
            <a href='login.html' style='color:blue;'>Login Here</a>
         </div>");
}

// 2. Handle Adding Products
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $cat = $_POST['category'];
    $img = $_POST['image'];
    $stmt = $conn->prepare("INSERT INTO products (name, price, category, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $price, $cat, $img);
    $stmt->execute();
    header("Location: admin.php"); // Refresh
}

// 3. Handle Deleting Products
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SmartMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; padding: 30px; }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Header */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; }
        .home-btn { 
            text-decoration: none; background: #2c3e50; color: white; 
            padding: 10px 20px; border-radius: 5px; font-weight: bold; 
        }

        /* Cards */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card h2 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; color: #2ecc71; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; color: #555; font-weight: 600; text-align: left; padding: 15px; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #444; }
        tr:hover { background: #fafafa; }

        /* Status Badges */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: bold; }
        .bg-cod { background: #fff3cd; color: #856404; }
        .bg-card { background: #d1ecf1; color: #0c5460; }
        .bg-upi { background: #d4edda; color: #155724; }

        /* Forms */
        .add-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        input { padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        .add-btn { background: #2ecc71; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .add-btn:hover { background: #27ae60; }
        
        .del-btn { color: #e74c3c; cursor: pointer; transition: 0.2s; }
        .del-btn:hover { transform: scale(1.2); }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🛠️ Admin Dashboard</h1>
        <a href="index.html" class="home-btn"><i class="fa-solid fa-store"></i> Back to Shop</a>
    </div>

    <div class="card">
        <h2>📦 Recent Customer Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items Ordered</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch Orders + User Info
                $sql = "SELECT orders.id, users.username, orders.total_price, orders.payment_method, orders.order_date 
                        FROM orders 
                        JOIN users ON orders.user_id = users.id 
                        ORDER BY orders.order_date DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($order = $result->fetch_assoc()) {
                        
                        // Fetch Items for this specific order
                        $oid = $order['id'];
                        $item_sql = "SELECT product_name, quantity FROM order_items WHERE order_id = $oid";
                        $items_res = $conn->query($item_sql);
                        $items_list = "";
                        while($i = $items_res->fetch_assoc()) {
                            $items_list .= $i['product_name'] . " (x" . $i['quantity'] . "), ";
                        }
                        $items_list = rtrim($items_list, ", "); // Remove last comma

                        // Determine Badge Color
                        $payClass = 'bg-cod';
                        if($order['payment_method'] == 'Card') $payClass = 'bg-card';
                        if($order['payment_method'] == 'UPI') $payClass = 'bg-upi';

                        echo "<tr>
                            <td>#{$order['id']}</td>
                            <td><strong>{$order['username']}</strong></td>
                            <td style='font-size:0.9rem; color:#666;'>$items_list</td>
                            <td>₹{$order['total_price']}</td>
                            <td><span class='badge $payClass'>{$order['payment_method']}</span></td>
                            <td>{$order['order_date']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No orders found yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>🍏 Manage Products</h2>
        
        <form method="POST" class="add-form">
            <input type="text" name="name" placeholder="Product Name (e.g. Orange)" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <input type="text" name="category" placeholder="Category" required>
            <input type="text" name="image" placeholder="Image URL" required>
            <button type="submit" name="add_product" class="add-btn">+ Add Item</button>
        </form>

        <br>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $prods = $conn->query("SELECT * FROM products");
                while($p = $prods->fetch_assoc()) {
                    echo "<tr>
                        <td>{$p['id']}</td>
                        <td><img src='{$p['image_url']}' style='width:40px; height:40px; border-radius:4px; object-fit:cover;'></td>
                        <td>{$p['name']}</td>
                        <td>₹{$p['price']}</td>
                        <td><a href='admin.php?delete={$p['id']}' class='del-btn'><i class='fa-solid fa-trash'></i></a></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>