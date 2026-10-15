<?php
session_start();
header('Content-Type: application/json');

$productId = isset($_POST['product_id']) ? $_POST['product_id'] : null;
$action    = isset($_POST['action'])     ? $_POST['action']     : null;

if (!$productId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

if (!isset($_SESSION['cart']) || !isset($_SESSION['cart'][$productId])) {
    echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
    exit;
}

if ($action === 'increase') {
    $_SESSION['cart'][$productId]['quantity']++;
} elseif ($action === 'decrease') {
    if ($_SESSION['cart'][$productId]['quantity'] > 1) {
        $_SESSION['cart'][$productId]['quantity']--;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

echo json_encode([
    'success'  => true,
    'quantity' => $_SESSION['cart'][$productId]['quantity']
]);
exit;