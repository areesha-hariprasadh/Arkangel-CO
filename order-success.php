<?php
session_start();

if (!isset($_SESSION['last_order_id'])) {
    header("Location: shop.php");
    exit();
}

$order_id = $_SESSION['last_order_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Successful</title>
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="order-success-page">

    <div class="order-success-container">
        <div class="order-success-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 class="order-success-title">
            ORDER CONFIRMED
        </h1>

        <div class="order-success-divider">
            <i class="fa-regular fa-gem"></i>
        </div>

        <p class="order-success-message">
            Thank you for your purchase. Your order has been received and is being processed.
            A confirmation email will be sent shortly.
        </p>

        <div class="order-number-box">
            <span class="order-number-label">ORDER NUMBER</span>
            <div class="order-number">
                #<?php echo $order_id; ?>
            </div>
        </div>

        <div class="order-success-buttons">
            <a href="shop.php" class="order-success-btn order-success-btn-primary">
                Continue Shopping
            </a>

            <a href="index.php" class="order-success-btn order-success-btn-secondary">
                Home Page
            </a>
        </div>
    </div>

</body>
</html>