<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$product_id = (int)$_POST['product_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity']++;
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $_SESSION['cart'][$product_id] = [
            'id'       => $product['id'],
            'name'     => $product['name'],
            'price'    => $product['price'],
            'image'    => $product['image'],
            'metal'    => $product['metal'],
            'quantity' => 1
        ];
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    $stmt->close();
}

// Save the page the user was on so the cart can send them back
if (!empty($_POST['current_page'])) {
    $_SESSION['last_shop_page'] = $_POST['current_page'];
}

$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

echo json_encode([
    'success'    => true,
    'message'    => 'Product added to cart',
    'cart_count' => $cartCount
]);
exit();