<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Form Data
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$apartment = $_POST['apartment'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$country = $_POST['country'];

$shipping_method = $_POST['shipping_method'];
$payment_method = $_POST['payment_method'];

$subtotal = $_POST['subtotal'];
$shipping_cost = $_POST['shipping_cost'];
$total_amount = $_POST['total_amount'];

// Create Order
$sql = "INSERT INTO orders (
    user_id,
    full_name,
    email,
    phone,
    address,
    apartment,
    city,
    province,
    postal_code,
    country,
    shipping_method,
    payment_method,
    subtotal,
    shipping_cost,
    total_amount
)
VALUES (
    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isssssssssssddd",
    $user_id,
    $full_name,
    $email,
    $phone,
    $address,
    $apartment,
    $city,
    $province,
    $postal_code,
    $country,
    $shipping_method,
    $payment_method,
    $subtotal,
    $shipping_cost,
    $total_amount
);

$stmt->execute();

$order_id = $conn->insert_id;

// Save Order Items
foreach ($_SESSION['cart'] as $item) {

    $product_id = $item['id'];
    $product_name = $item['name'];
    $product_price = $item['price'];
    $quantity = $item['quantity'];
    $product_image = $item['image'];

    $item_sql = "INSERT INTO order_items (
        order_id,
        product_id,
        product_name,
        product_price,
        quantity,
        product_image
    )
    VALUES (?, ?, ?, ?, ?, ?)";

    $item_stmt = $conn->prepare($item_sql);

    $item_stmt->bind_param(
        "iisdis",
        $order_id,
        $product_id,
        $product_name,
        $product_price,
        $quantity,
        $product_image
    );

    $item_stmt->execute();
}

// Empty Cart
unset($_SESSION['cart']);

// Store order id
$_SESSION['last_order_id'] = $order_id;

// Redirect
header("Location: order-success.php");
exit();
?>