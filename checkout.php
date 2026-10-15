<?php
session_start();

// -- Login gate -- //
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit();
}

// -- Cart data -- //
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    header('Location: cart.php');
    exit();
}

$subtotal   = 0;
$totalItems = 0;
foreach ($cart as $item) {
    $subtotal   += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$standardShipping = 100;
$expressShipping  = 180;
$freeThreshold    = 1000;

// -- Free standard shipping on orders over R1000 -- //
$defaultShipping = ($subtotal >= $freeThreshold) ? 0 : $standardShipping;
$total           = $subtotal + $defaultShipping;

// -- Pre-fill from session if user data exists -- //
$userName  = $_SESSION['user_name']  ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="includes/style-footer-thank-you.css">
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style-checkout.css">
</head>
<body class="checkout-page">
    <!-- ===================== HEADER ===================== -->
    <?php include 'includes/header.php'; ?>


    <!-- ===================== CHECKOUT PAGE ===================== -->
    <section class="lux-checkout-wrapper">
        <!-- ===== TITLE ===== -->
        <section class="lux-checkout-title-section">
            <h1 id="lux-checkout-main-title">
                CHECKOUT
                <span><i class="fa-regular fa-gem"></i></span>
            </h1>
        </section>

        <!-- ===== GRID ===== -->
        <section class="lux-checkout-grid">
            <section class="lux-checkout-left-panel">

                <form id="lux-checkout-form" action="place-order.php" method="POST" novalidate>

                    <!-- ── 1. SHIPPING INFORMATION ── -->
                    <div class="lux-checkout-block">
                        <div class="lux-checkout-block-header">
                            <div class="lux-checkout-step-number">1</div>
                            <h2 class="lux-checkout-block-title">SHIPPING INFORMATION</h2>
                        </div>

                        <div class="lux-checkout-form-fields">

                            <div class="lux-checkout-row">
                                <div class="lux-field-group">
                                    <input type="text" name="full_name" id="full_name"
                                           placeholder="Full Name *"
                                           value="<?php echo htmlspecialchars($userName); ?>"
                                           required>
                                    <span class="lux-field-error" id="err-full_name"></span>
                                </div>
                                <div class="lux-field-group">
                                    <input type="email" name="email" id="email"
                                           placeholder="Email Address *"
                                           value="<?php echo htmlspecialchars($userEmail); ?>"
                                           required>
                                    <span class="lux-field-error" id="err-email"></span>
                                </div>
                            </div>

                            <div class="lux-field-group">
                                <input type="tel" name="phone" id="phone"
                                       placeholder="Phone Number *"
                                       value="<?php echo htmlspecialchars($userPhone); ?>"
                                       required>
                                <span class="lux-field-error" id="err-phone"></span>
                            </div>

                            <div class="lux-field-group">
                                <input type="text" name="address" id="address"
                                       placeholder="Street Address *" required>
                                <span class="lux-field-error" id="err-address"></span>
                            </div>

                            <div class="lux-field-group">
                                <input type="text" name="apartment"
                                       placeholder="Apartment, suite, etc. (optional)">
                            </div>

                            <div class="lux-checkout-three-column">
                                <div class="lux-field-group">
                                    <input type="text" name="city" id="city"
                                           placeholder="City *" required>
                                    <span class="lux-field-error" id="err-city"></span>
                                </div>
                                <div class="lux-field-group">
                                    <select name="province" id="province" required>
                                        <option value="">Province *</option>
                                        <option>Gauteng</option>
                                        <option>KwaZulu-Natal</option>
                                        <option>Western Cape</option>
                                        <option>Eastern Cape</option>
                                        <option>Limpopo</option>
                                        <option>Mpumalanga</option>
                                        <option>North West</option>
                                        <option>Northern Cape</option>
                                        <option>Free State</option>
                                    </select>
                                    <span class="lux-field-error" id="err-province"></span>
                                </div>
                                <div class="lux-field-group">
                                    <input type="text" name="postal_code" id="postal_code"
                                           placeholder="Postal Code *"
                                           maxlength="4" required>
                                    <span class="lux-field-error" id="err-postal_code"></span>
                                </div>
                            </div>

                            <div class="lux-field-group">
                                <select name="country">
                                    <option value="South Africa">South Africa</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- ── 2. SHIPPING METHOD ── -->
                    <div class="lux-checkout-block">
                        <div class="lux-checkout-block-header">
                            <div class="lux-checkout-step-number">2</div>
                            <h2 class="lux-checkout-block-title">SHIPPING METHOD</h2>
                        </div>

                        <div class="lux-checkout-option-box">

                            <label class="lux-checkout-option-item">
                                <div class="lux-checkout-option-left">
                                    <input type="radio" name="shipping_method"
                                           class="lux-checkout-radio-input"
                                           value="standard"
                                           data-cost="<?php echo ($subtotal >= $freeThreshold) ? 0 : $standardShipping; ?>"
                                           checked>
                                    <div class="lux-checkout-option-info">
                                        <h4>Standard Shipping</h4>
                                        <p>3-5 business days</p>
                                    </div>
                                </div>
                                <div class="lux-checkout-option-price">
                                    <?php echo ($subtotal >= $freeThreshold) ? '<span class="lux-free-badge">FREE</span>' : 'R100'; ?>
                                </div>
                            </label>

                            <label class="lux-checkout-option-item">
                                <div class="lux-checkout-option-left">
                                    <input type="radio" name="shipping_method"
                                           class="lux-checkout-radio-input"
                                           value="express"
                                           data-cost="<?php echo $expressShipping; ?>">
                                    <div class="lux-checkout-option-info">
                                        <h4>Express Shipping</h4>
                                        <p>1-2 business days</p>
                                    </div>
                                </div>
                                <div class="lux-checkout-option-price">R180</div>
                            </label>

                        </div>
                    </div>

                    <!-- ── 3. PAYMENT METHOD ── -->
                    <div class="lux-checkout-block">
                        <div class="lux-checkout-block-header">
                            <div class="lux-checkout-step-number">3</div>
                            <h2 class="lux-checkout-block-title">PAYMENT METHOD</h2>
                        </div>

                        <div class="lux-checkout-option-box">

                            <label class="lux-checkout-option-item">
                                <div class="lux-checkout-option-left">
                                    <input type="radio" name="payment_method"
                                           class="lux-checkout-radio-input"
                                           value="card" checked>
                                    <div class="lux-checkout-option-info">
                                        <h4>Credit / Debit Card</h4>
                                    </div>
                                </div>
                                <div class="lux-checkout-option-price lux-card-icons">
                                    <i class="fa-brands fa-cc-visa"></i>
                                    <i class="fa-brands fa-cc-mastercard"></i>
                                </div>
                            </label>

                            <label class="lux-checkout-option-item">
                                <div class="lux-checkout-option-left">
                                    <input type="radio" name="payment_method"
                                           class="lux-checkout-radio-input"
                                           value="paypal">
                                    <div class="lux-checkout-option-info">
                                        <h4>PayPal</h4>
                                    </div>
                                </div>
                                <div class="lux-checkout-option-price">
                                    <i class="fa-brands fa-paypal"></i> PayPal
                                </div>
                            </label>

                        </div>
                    </div>

                    <!-- ── PLACE ORDER BUTTON ── -->
                    <div class="lux-complete-order-section">
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="shipping_cost" value="<?php echo $defaultShipping; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $total; ?>">

                        <button type="submit" id="lux-complete-order-btn">
                            <i class="fa-solid fa-lock"></i>&nbsp; COMPLETE ORDER
                        </button>

                        <div class="lux-checkout-security-text">
                            <i class="fa-solid fa-shield-halved"></i>
                            &nbsp;Your payment information is secure and encrypted.
                        </div>
                    </div>

                </form>

            </section>

            <!-- ========= RIGHT PANEL ========== -->
            <section class="lux-checkout-right-panel">
                <!-- ORDER SUMMARY -->
                <div class="lux-order-summary-box">
                    <h2 class="lux-order-summary-title">ORDER SUMMARY</h2>

                    <div class="lux-summary-items">
                        <?php foreach ($cart as $item): ?>
                        <div class="lux-summary-product">
                            <div class="lux-summary-product-img-wrap">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <span class="lux-summary-qty-badge"><?php echo (int)$item['quantity']; ?></span>
                            </div>
                            <div class="lux-summary-product-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <?php if (!empty($item['metal'])): ?>
                                    <p><?php echo ucfirst($item['metal']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="lux-summary-product-price">
                                R<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="lux-summary-divider"></div>

                    <div class="lux-summary-row">
                        <span>Subtotal (<?php echo $totalItems; ?> item<?php echo $totalItems !== 1 ? 's' : ''; ?>)</span>
                        <span>R<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="lux-summary-row">
                        <span>Shipping</span>
                        <span id="lux-shipping-cost">
                            <?php echo $defaultShipping === 0 ? 'FREE' : 'R' . number_format($defaultShipping, 2); ?>
                        </span>
                    </div>

                    <?php if ($subtotal >= $freeThreshold): ?>
                    <div class="lux-free-shipping-note">
                        <i class="fa-solid fa-truck"></i> Free shipping applied on orders over R1,000
                    </div>
                    <?php endif; ?>

                    <div class="lux-summary-divider"></div>

                    <div class="lux-summary-row lux-summary-total">
                        <span>TOTAL</span>
                        <span id="lux-order-total">R<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <!-- TRUST FEATURES -->
                <div class="lux-checkout-feature-box">
                    <div class="lux-checkout-feature-item">
                        <div class="lux-checkout-feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="lux-checkout-feature-text">
                            <h4>SECURE CHECKOUT</h4>
                            <p>Your payment information is 100% secure.</p>
                        </div>
                    </div>
                    <div class="lux-checkout-feature-item">
                        <div class="lux-checkout-feature-icon"><i class="fa-solid fa-truck"></i></div>
                        <div class="lux-checkout-feature-text">
                            <h4>FREE SHIPPING</h4>
                            <p>On all orders over R1,000.</p>
                        </div>
                    </div>
                    <div class="lux-checkout-feature-item">
                        <div class="lux-checkout-feature-icon"><i class="fa-solid fa-arrow-rotate-left"></i></div>
                        <div class="lux-checkout-feature-text">
                            <h4>EASY RETURNS</h4>
                            <p>30-day return policy.</p>
                        </div>
                    </div>
                    <div class="lux-checkout-feature-item">
                        <div class="lux-checkout-feature-icon"><i class="fa-solid fa-headset"></i></div>
                        <div class="lux-checkout-feature-text">
                            <h4>CUSTOMER SUPPORT</h4>
                            <p>We're here to help.</p>
                        </div>
                    </div>
                </div>

            </section>

        </section>
    </section>


    <!-- ===================== FOOTER ===================== -->
    <?php include 'includes/footer-thank-you.php'; ?>


    <!-- ===================== JAVASCRIPT ===================== -->
    <script>
    window.checkoutData = {
        subtotal: <?php echo $subtotal; ?>
    };
    </script>

    <script src="index.js"></script>

</body>
</html>