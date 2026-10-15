<?php
session_start();
include 'includes/db.php';

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: shop.php");
    exit();
}

// Fetch product
$result = $conn->query("SELECT * FROM products WHERE id = $id");

if (!$result || $result->num_rows === 0) {
    header("Location: shop.php");
    exit();
}

$product = $result->fetch_assoc();

// Check wishlist
$inWishlist = isset($_SESSION['wishlist']) && in_array($product['id'], $_SESSION['wishlist']);

// Category page link map
$categoryLinks = [
    'rings'     => 'rings.php',
    'necklaces' => 'necklaces.php',
    'earrings'  => 'earrings.php',
    'bracelets' => 'bracelets.php',
    'sets'      => 'sets.php',
];

$categoryLink  = $categoryLinks[$product['category']] ?? 'shop.php';
$categoryLabel = ucfirst($product['category']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Arkangel</title>

    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- ===================== TOAST ===================== -->
    <div id="toast" class="toast"></div>


    <!-- ===================== HEADER ===================== -->    
    <?php include 'includes/header.php'; ?>


    <!-- ===================== PRODUCT PAGE ===================== -->
    <section class="pip-page">
        <div class="pip-container">

            <!-- BREADCRUMB -->
            <div class="pip-breadcrumb">
                <a href="index.php">Home</a>
                <span class="pip-crumb-sep">—</span>
                <a href="shop.php">Shop</a>
                <span class="pip-crumb-sep">—</span>
                <a href="<?php echo $categoryLink; ?>"><?php echo $categoryLabel; ?></a>
                <span class="pip-crumb-sep">—</span>
                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
            </div>

            <div class="pip-grid">

                <!-- IMAGE COLUMN -->
                <div class="pip-image-col">
                    <div class="pip-image-wrap">

                        <?php if (!empty($product['badge'])): ?>
                            <div class="pip-image-badge">
                                <?php echo htmlspecialchars($product['badge']); ?>
                            </div>
                        <?php endif; ?>

                        <button class="pip-image-wishlist js-pip-wishlist" aria-label="Add to wishlist">
                            <i class="<?php echo $inWishlist ? 'fa-solid pip-liked' : 'fa-regular'; ?> fa-heart"></i>
                        </button>

                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                </div>

                <!-- INFO COLUMN -->
                <div class="pip-info">
                    <p class="pip-category-label">
                        Fine <?php echo $categoryLabel; ?>
                    </p>

                    <h1 class="pip-title">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <div class="pip-price-row">
                        <span class="pip-price">
                            R<?php echo number_format($product['price'], 2); ?>
                        </span>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                        <p class="pip-desc">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                    <?php endif; ?>

                    <div class="pip-divider"></div>

                    <!-- SPECS TABLE -->
                    <div class="pip-specs">
                        <?php if (!empty($product['metal'])): ?>
                            <div class="pip-spec-row">
                                <span class="pip-spec-label">Metal</span>
                                <span class="pip-spec-value"><?php echo ucfirst($product['metal']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['pendant_size'])): ?>
                            <div class="pip-spec-row">
                                <span class="pip-spec-label">Pendant Size</span>
                                <span class="pip-spec-value"><?php echo htmlspecialchars($product['pendant_size']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['chain_type'])): ?>
                            <div class="pip-spec-row">
                                <span class="pip-spec-label">Chain Type</span>
                                <span class="pip-spec-value"><?php echo htmlspecialchars($product['chain_type']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['clasp'])): ?>
                            <div class="pip-spec-row">
                                <span class="pip-spec-label">Clasp</span>
                                <span class="pip-spec-value"><?php echo htmlspecialchars($product['clasp']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['hypoallergenic'])): ?>
                            <div class="pip-spec-row">
                                <span class="pip-spec-label">Hypoallergenic</span>
                                <span class="pip-spec-value"><?php echo htmlspecialchars($product['hypoallergenic']); ?></span>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="pip-divider"></div>

                    <!-- QUANTITY -->
                    <div class="pip-qty-group">
                        <h3 class="pip-qty-label">Quantity</h3>
                        <div class="pip-qty-box">
                            <button class="pip-qty-btn" onclick="pipChangeQty(-1)" aria-label="Decrease">−</button>
                            <div class="pip-qty-num" id="pip-qty-display">1</div>
                            <button class="pip-qty-btn" onclick="pipChangeQty(1)" aria-label="Increase">+</button>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="pip-actions">
                        <button class="pip-cart-btn">
                            <span>Add to Cart</span>
                        </button>
                        <button class="pip-wishlist-btn js-pip-wishlist" aria-label="Save to wishlist">
                            <i class="<?php echo $inWishlist ? 'fa-solid pip-liked' : 'fa-regular'; ?> fa-heart"></i>
                        </button>
                    </div>

                    <!-- TRUST BADGES -->
                    <div class="pip-trust-row">
                        <span class="pip-trust-item"><i class="fa-solid fa-shield-halved"></i> Secure checkout</span>
                        <span class="pip-trust-item"><i class="fa-solid fa-rotate-left"></i> 30-day returns</span>
                        <span class="pip-trust-item"><i class="fa-solid fa-gem"></i> Authenticity guaranteed</span>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <!-- ===================== TABS ===================== -->
    <section class="pip-tabs-section">
        <div class="pip-container">

            <!-- TABS -->
            <div class="pip-tabs" role="tablist">
                <button class="pip-tab pip-tab--active" data-tab="pip-tab-description" role="tab">Description</button>
                <button class="pip-tab" data-tab="pip-tab-details" role="tab">Details</button>
                <button class="pip-tab" data-tab="pip-tab-shipping" role="tab">Shipping & Returns</button>
                <button class="pip-tab" data-tab="pip-tab-care" role="tab">Care Guide</button>
            </div>

            <!-- TAB PANEL -->
            <div class="pip-tab-panels">
                <div class="pip-tab-panel pip-tab-panel--active" id="pip-tab-description">
                    <div class="pip-panel-text">
                        <p>
                            <?php echo !empty($product['description'])
                                ? htmlspecialchars($product['description'])
                                : 'No description available for this product.'; ?>
                        </p>
                    </div>
                </div>

                <div class="pip-tab-panel" id="pip-tab-details">
                    <div class="pip-panel-text">
                        <p>Handcrafted with precision, each piece undergoes rigorous quality checks before it reaches you.</p>
                    </div>
                </div>

                <div class="pip-tab-panel" id="pip-tab-shipping">
                    <div class="pip-panel-text">
                        <p>Standard delivery takes 3-5 business days. Express options available at checkout. Returns accepted within 30 days of receipt — items must be unworn and in original packaging.</p>
                    </div>
                </div>

                <div class="pip-tab-panel" id="pip-tab-care">
                    <div class="pip-panel-text">
                        <p>Store in the provided pouch away from moisture and direct sunlight. Avoid contact with perfumes, lotions, and chlorine. Gently wipe with a soft cloth after wearing to preserve the finish.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ===================== FOOTER ===================== -->
    <?php include 'includes/footer.php'; ?>

    <!-- Hidden inputs for wishlist JS -->
    <input type="hidden" id="pip-product-id" value="<?php echo $product['id']; ?>">
    <input type="hidden" id="pip-current-page" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

    <script src="index.js"></script>
</body>
</html>