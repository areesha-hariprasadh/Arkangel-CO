<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "not_logged_in"]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if (!$product_id) {
    echo json_encode(["status" => "error", "msg" => "No product ID received"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "removed"]);
} else {
    $check->close();

    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "added"]);
}

$conn->close();