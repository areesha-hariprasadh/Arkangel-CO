<?php
session_start();
include 'includes/db.php';

// -- Trending products -- //
$trendingNames = ['Wish Upon A Pink', 'Butterfly Effect', 'Emerald Goddess'];

$trendingIds = [];
foreach ($trendingNames as $name) {
    $safe = $conn->real_escape_string($name);
    $res  = $conn->query("SELECT id FROM products WHERE name = '$safe' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $trendingIds[$name] = (int)$row['id'];
    }
}

// -- Best Sellers: limit 12 -- //
$bestSellersResult = $conn->query(
    "SELECT id, name, category, price, image FROM products
     WHERE badge = 'Best Seller'
     ORDER BY id ASC
     LIMIT 12"
);

// -- Helper: resolve name to product-infor link -- //
function productLink(array $ids, string $name): string {
    return isset($ids[$name])
        ? 'product-infor.php?id=' . $ids[$name]
        : 'shop.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arkangel & Co</title>
    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <!-- ================ TOAST ================= -->
    <div id="toast" class="toast"></div>


    <!-- ================ HEADER ================== -->
    <?php include 'includes/header.php'; ?>


    <!-- ================= HOME PAGE =================== -->
    <!-- ===== HERO ===== -->
    <section id="home-hero">
        <div class="home-hero__text">
            <h1>ARKANGEL & CO</h1>
            <p>Elegance at its finest</p>
            <a href="shop.php"><button class="home-hero__btn">SHOP NOW</button></a>
        </div>
    </section>

    <!-- ===== OUR COLLECTION ===== -->
    <section id="home-collection" class="home-section-pad">
        <div class="home-divider">
            <span>OUR COLLECTION</span>
        </div>

        <div class="home-collection__grid">
            <a href="necklaces.php" class="home-collection__item">
                <img class="home-collection__img" src="images/our-collection/necklace.avif" alt="Necklaces">
                <h6 class="home-collection__label">NECKLACES</h6>
            </a>
            <a href="rings.php" class="home-collection__item">
                <img class="home-collection__img" src="images/our-collection/rings.avif" alt="Rings">
                <h6 class="home-collection__label">RINGS</h6>
            </a>
            <a href="bracelets.php" class="home-collection__item">
                <img class="home-collection__img" src="images/our-collection/bracelet.avif" alt="Bracelets">
                <h6 class="home-collection__label">BRACELETS</h6>
            </a>
            <a href="earrings.php" class="home-collection__item">
                <img class="home-collection__img" src="images/our-collection/earrings.avif" alt="Earrings">
                <h6 class="home-collection__label">EARRINGS</h6>
            </a>
            <a href="jewelry-sets.php" class="home-collection__item">
                <img class="home-collection__img" src="images/our-collection/sets.jpg" alt="Sets">
                <h6 class="home-collection__label">SETS</h6>
            </a>
        </div>
    </section>

    <!-- ===== TRENDY PRODUCTS ===== -->
    <section id="home-trendy">
        <div class="home-divider">
            <span>TRENDY PRODUCTS</span>
        </div>

        <div class="home-trendy__container">
            <div class="home-trendy__list">

                <a href="<?php echo productLink($trendingIds, 'Wish Upon A Pink'); ?>" class="home-trendy__item">
                    <img src="images/rings/Wish Upon A Pink.avif" alt="Wish Upon A Pink">
                    <div class="home-trendy__info">
                        <h3>Wish Upon A Pink</h3>
                        <p>A touch of pink, a lifetime of charm.</p>
                    </div>
                </a>

                <a href="<?php echo productLink($trendingIds, 'Butterfly Effect'); ?>" class="home-trendy__item">
                    <img src="images/rings/Butterfly Effect.avif" alt="Butterfly Effect">
                    <div class="home-trendy__info">
                        <h3>Butterfly Effect</h3>
                        <p>Delicate like a butterfly, beautiful like you.</p>
                    </div>
                </a>

                <a href="<?php echo productLink($trendingIds, 'Emerald Goddess'); ?>" class="home-trendy__item">
                    <img src="images/earrings/Emerald Goddess.avif" alt="Emerald Goddess">
                    <div class="home-trendy__info">
                        <h3>Emerald Goddess</h3>
                        <p>Bold, timeless, unforgettable. Just like you.</p>
                    </div>
                </a>
            </div>

            <div class="home-trendy__feature">
                <img src="images/model.avif" alt="Model wearing jewelry">
            </div>
        </div>
    </section>

    <!-- ===== BEST SELLERS ===== -->
    <section id="home-bestsellers" class="home-section-pad">
        <div class="home-divider">
            <span>BEST SELLERS</span>
        </div>

        <div class="home-bestsellers__grid">
            <?php while ($item = $bestSellersResult->fetch_assoc()):
                $link      = 'product-infor.php?id=' . $item['id'];
                $productId = $item['id'];
                $category  = ucfirst($item['category']);
            ?>
            <div class="home-product-card" onclick="window.location='<?php echo $link; ?>'">
                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="home-product-card__meta">
                    <span class="home-product-card__category"><?php echo $category; ?></span>
                    <h5 class="home-product-card__name"><?php echo htmlspecialchars($item['name']); ?></h5>
                    <p class="home-product-card__price">R<?php echo number_format($item['price'], 2); ?></p>
                </div>
                <form action="cart/cart-handler.php" method="POST" class="home-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <input type="hidden" name="current_page" value="index.php">
                    <button type="submit"
                            class="home-product-card__cart"
                            aria-label="Add <?php echo htmlspecialchars($item['name']); ?> to cart"
                            onclick="event.stopPropagation()">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                </form>
            </div>
            <?php endwhile; ?>

        </div>
    </section>


    <!-- ================= FOOTER =================== -->
    <?php include 'includes/footer.php'; ?>


    <script src="index.js"></script>
</body>
</html>