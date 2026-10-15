<?php
session_start();

$lastPage = !empty($_SESSION['last_shop_page']) ? $_SESSION['last_shop_page'] : 'index.php';
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$subtotal = 0;
$totalItems = 0;

foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$shipping = ($subtotal > 0) ? 100 : 0;
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>

    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="includes/style-footer-thank-you.css">
    <link rel="stylesheet" href="style-global.css">
    <link rel="stylesheet" href="style-checkout.css">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="cart-page">
    <!-- ===================== HEADER ===================== -->
    <?php include 'includes/header.php'; ?>


    <!-- ===================== CART PAGE ===================== -->
    <section class="cart-page">
        <div class="cart-header-title">
            <h1>Your Cart</h1>
            <div class="cart-divider">
                <i class="fa-regular fa-gem"></i>
            </div>
        </div>

        <?php if (!empty($cart)) { ?>

            <div class="cart-header">
                <h3>Your Items</h3>
                <h3>Price</h3>
                <h3>Quantity</h3>
                <h3>Total</h3>
                <h3></h3>
            </div>

            <?php foreach ($cart as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
            ?>

            <div class="cart-item" data-id="<?php echo htmlspecialchars($item['id']); ?>" data-price="<?php echo (float)$item['price']; ?>">
                <div class="product-info">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="product-image" alt="<?php echo htmlspecialchars($item['name']); ?>">

                    <div class="product-details">
                        <h4 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="product-meta"><?php echo ucfirst($item['metal']); ?></p>
                    </div>
                </div>

                <div class="product-price">
                    R<?php echo number_format($item['price'], 2); ?>
                </div>

                <div class="quantity-control">
                    <button
                        type="button"
                        class="qty-btn qty-decrease"
                        data-id="<?php echo htmlspecialchars($item['id']); ?>"
                        <?php if ($item['quantity'] <= 1) echo 'disabled'; ?>
                        aria-label="Decrease quantity"
                    >&#8722;</button>

                    <span class="qty-value"><?php echo (int)$item['quantity']; ?></span>

                    <button
                        type="button"
                        class="qty-btn qty-increase"
                        data-id="<?php echo htmlspecialchars($item['id']); ?>"
                        aria-label="Increase quantity"
                    >+</button>
                </div>

                <div class="item-total">
                    R<span class="item-total-value"><?php echo number_format($itemTotal, 2); ?></span>
                </div>

                <button
                    type="button"
                    class="remove-item remove-btn"
                    data-id="<?php echo htmlspecialchars($item['id']); ?>"
                    aria-label="Remove item"
                >&#215;</button>
            </div>

            <?php } ?>

        <?php } else { ?>
            <h2 class="empty-cart">Your cart is empty</h2>
        <?php } ?>

        <div class="cart-actions">
            <a href="<?php echo htmlspecialchars($lastPage); ?>" class="continue-btn">CONTINUE SHOPPING</a>
        </div>
    </section>

    <!-- ===================== CART SUMMARY SECTION ===================== -->
    <section class="summary-section">
        <div class="summary-box">
            <h2 class="summary-title">ORDER SUMMARY</h2>

            <div class="summary-row">
                <span>Subtotal (<span id="summary-item-count"><?php echo $totalItems; ?></span> item<?php echo $totalItems !== 1 ? 's' : ''; ?>)</span>
                <span class="price" id="summary-subtotal">R<?php echo number_format($subtotal, 2); ?></span>
            </div>

            <div class="summary-row">
                <span>Shipping</span>
                <span class="price" id="summary-shipping">R<?php echo number_format($shipping, 2); ?></span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total-row">
                <span>TOTAL</span>
                <span class="price" id="summary-total">R<?php echo number_format($total, 2); ?></span>
            </div>

            <button id="checkout-btn" class="checkout-btn" 
                data-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>">PROCEED TO CHECKOUT</button>
        </div>
    </section>

    <!-- ===================== LOGIN PROMPT MODAL ===================== -->
    <div id="login-prompt-overlay" class="login-prompt-overlay" style="display:none;">
        <div class="login-prompt-box">
            <div class="login-prompt-icon"><i class="fa-regular fa-user"></i></div>
            <h3>Login Required</h3>
            <p>You need to be logged in to proceed to checkout. Redirecting you to the login page...</p>
            <div class="login-prompt-bar-wrap">
                <div class="login-prompt-bar" id="login-prompt-bar"></div>
            </div>
            <div class="login-prompt-actions">
                <a href="login.php" class="login-prompt-btn-primary">LOGIN NOW</a>
                <button class="login-prompt-btn-secondary" id="login-prompt-cancel">CANCEL</button>
            </div>
        </div>
    </div>
    

    <!-- ===================== FOOTER ===================== -->
    <?php include 'includes/footer-thank-you.php'; ?>
    
    <script src="index.js"></script>
</body>
</html>