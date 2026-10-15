<?php
session_start();
include 'includes/db.php';

$filtersActive = isset($_GET['category']) || isset($_GET['metal']) || isset($_GET['price']) || isset($_GET['sort']);

$limit = 12;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$metalFilter    = isset($_GET['metal'])    ? $_GET['metal']    : null;
$priceFilter    = isset($_GET['price'])    ? $_GET['price']    : null;
$sort           = isset($_GET['sort'])     ? $_GET['sort']     : "featured";

$where = "WHERE 1=1";

if ($categoryFilter) {
    $cat    = $conn->real_escape_string($categoryFilter);
    $where .= " AND category = '$cat'";
}
if ($metalFilter) {
    $metal  = $conn->real_escape_string($metalFilter);
    $where .= " AND metal = '$metal'";
}
if ($priceFilter) {
    if ($priceFilter == "under500")      $where .= " AND price < 500";
    elseif ($priceFilter == "500to1000") $where .= " AND price BETWEEN 500 AND 1000";
    elseif ($priceFilter == "1000plus")  $where .= " AND price > 1000";
}

$order = "";
if ($sort == "low_high")     $order = " ORDER BY price ASC";
elseif ($sort == "high_low") $order = " ORDER BY price DESC";
elseif ($sort == "newest")   $order = " ORDER BY created_at DESC";

$count_result   = $conn->query("SELECT COUNT(*) as total FROM products $where");
$count_row      = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$total_pages    = ceil($total_products / $limit);

$filtered_result = null;
if ($filtersActive) {
    $query           = "SELECT * FROM products $where $order LIMIT $limit OFFSET $offset";
    $filtered_result = $conn->query($query);
}

// Category counts
$categories = ['rings', 'necklaces', 'earrings', 'bracelets', 'sets'];
$cat_counts  = [];
foreach ($categories as $c) {
    $r = $conn->query("SELECT COUNT(*) as total FROM products WHERE category = '$c'");
    $cat_counts[$c] = $r->fetch_assoc()['total'];
}

// Category preview — 6 random products per category
$categoryPages = [
    'rings'     => 'rings.php',
    'necklaces' => 'necklaces.php',
    'earrings'  => 'earrings.php',
    'bracelets' => 'bracelets.php',
    'sets'      => 'jewelry-sets.php',
];
$categoryLabels = [
    'rings'     => 'Rings',
    'necklaces' => 'Necklaces',
    'earrings'  => 'Earrings',
    'bracelets' => 'Bracelets',
    'sets'      => 'Jewelry Sets',
];
$categoryPreviews = [];
foreach ($categories as $c) {
    $r = $conn->query("SELECT * FROM products WHERE category = '$c' ORDER BY RAND() LIMIT 12");
    $categoryPreviews[$c] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="style-global.css">
    <link rel="stylesheet" href="style-shop.css">

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="shop-page">
    <div id="toast" class="toast"></div>

    <?php include 'includes/header.php'; ?>

    <!-- ===================== HERO ===================== -->
    <section class="shop-hero-section">
        <div class="shop-hero-overlay"></div>
        <div class="shop-hero-content">
            <p class="shop-hero-eyebrow">Curated for You</p>
            <h1 class="shop-hero-title">Our Collection</h1>
            <div class="shop-hero-divider">
                <span></span>
                <i class="fa-regular fa-gem"></i>
                <span></span>
            </div>
            <p class="shop-hero-subtitle">
                Timeless jewellery crafted to be worn, loved, and passed on.
            </p>
        </div>
    </section>


    <!-- ===================== FILTER BAR ===================== -->
    <section class="product-shop-section">
        <div class="product-shop-toolbar">
            <div class="product-shop-toolbar-left">
                <h2>FILTERS</h2>
            </div>
            <div class="product-shop-toolbar-center">
                <p><?php echo $filtersActive ? $total_products . ' PRODUCTS' : ''; ?></p>
            </div>
            <div class="product-shop-toolbar-right">
                <?php if ($filtersActive): ?>
                <form method="GET">
                    <?php if ($categoryFilter): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
                    <?php endif; ?>
                    <?php if ($metalFilter): ?>
                        <input type="hidden" name="metal" value="<?php echo htmlspecialchars($metalFilter); ?>">
                    <?php endif; ?>
                    <?php if ($priceFilter): ?>
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($priceFilter); ?>">
                    <?php endif; ?>
                    <select name="sort" class="product-shop-sort-select" onchange="this.form.submit()">
                        <option value="featured"  <?php if ($sort == 'featured')  echo 'selected'; ?>>Sort by: Featured</option>
                        <option value="low_high"  <?php if ($sort == 'low_high')  echo 'selected'; ?>>Price: Low to High</option>
                        <option value="high_low"  <?php if ($sort == 'high_low')  echo 'selected'; ?>>Price: High to Low</option>
                        <option value="newest"    <?php if ($sort == 'newest')    echo 'selected'; ?>>Newest</option>
                    </select>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-shop-layout">
            <aside class="product-shop-sidebar">
                <form method="GET">
                    <?php if ($sort && $sort !== 'featured'): ?>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <?php endif; ?>

                    <div class="product-shop-filter-grid">

                        <!-- CATEGORY FILTER -->
                        <div class="product-shop-filter-group">
                            <h4 class="product-shop-filter-title">Category</h4>
                            <?php
                            $categoryFilterLabels = [
                                'rings'     => 'Rings',
                                'necklaces' => 'Necklaces',
                                'earrings'  => 'Earrings',
                                'bracelets' => 'Bracelets',
                                'sets'      => 'Jewelry Sets',
                            ];
                            foreach ($categoryFilterLabels as $val => $label): ?>
                                <label class="product-shop-filter-label">
                                    <input type="radio" name="category" value="<?php echo $val; ?>"
                                        <?php if ($categoryFilter == $val) echo 'checked'; ?>>
                                    <span><?php echo $label; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- METAL FILTER -->
                        <div class="product-shop-filter-group">
                            <h4 class="product-shop-filter-title">Metal</h4>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="metal" value="gold" <?php if ($metalFilter == 'gold') echo 'checked'; ?>>
                                <span>Gold</span>
                            </label>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="metal" value="silver" <?php if ($metalFilter == 'silver') echo 'checked'; ?>>
                                <span>Silver</span>
                            </label>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="metal" value="rose gold" <?php if ($metalFilter == 'rose gold') echo 'checked'; ?>>
                                <span>Rose Gold</span>
                            </label>
                        </div>

                        <!-- PRICE FILTER -->
                        <div class="product-shop-filter-group">
                            <h4 class="product-shop-filter-title">Price</h4>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="price" value="under500" <?php if ($priceFilter == 'under500') echo 'checked'; ?>>
                                <span>Under R500</span>
                            </label>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="price" value="500to1000" <?php if ($priceFilter == '500to1000') echo 'checked'; ?>>
                                <span>R500 – R1000</span>
                            </label>
                            <label class="product-shop-filter-label">
                                <input type="radio" name="price" value="1000plus" <?php if ($priceFilter == '1000plus') echo 'checked'; ?>>
                                <span>R1000+</span>
                            </label>
                        </div>

                    </div>

                    <div class="product-shop-filter-buttons">
                        <button type="submit" class="product-shop-apply-button">APPLY FILTERS</button>
                        <a href="shop.php" class="product-shop-clear-button">CLEAR FILTERS</a>
                    </div>
                </form>
            </aside>
        </div>
    </section>


    <!-- ===================== FILTERED RESULTS ===================== -->
    <?php if ($filtersActive && $filtered_result): ?>
    <section id="product-shop-products-section">
        <div class="product-shop-product-grid">

            <?php if ($filtered_result->num_rows > 0): ?>
                <?php while ($row = $filtered_result->fetch_assoc()): ?>
                    <?php $inWishlist = isset($_SESSION['wishlist']) && in_array($row['id'], $_SESSION['wishlist']); ?>

                    <div class="product-shop-product-card">
                        <div class="product-shop-image-wrapper">
                            <a href="product-info.php?id=<?php echo $row['id']; ?>">
                                <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </a>
                            <div class="product-shop-wishlist-form">
                                <input type="hidden" class="product-id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" class="current-page" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="button" class="product-shop-wishlist-button js-wishlist-btn">
                                    <i class="<?php echo $inWishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>

                        <div class="product-shop-product-description">
                            <span><?php echo htmlspecialchars(ucfirst($row['category'])); ?></span>
                            <h5>
                                <a href="product-info.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </h5>
                        </div>

                        <h4>R<?php echo number_format($row['price'], 2); ?></h4>

                        <form action="cart/cart-handler.php" method="POST" class="product-shop-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <button type="submit" class="product-shop-cart-button">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="shop-no-results">
                    <i class="fa-regular fa-gem"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or <a href="shop.php">clear all filters</a>.</p>
                </div>
            <?php endif; ?>

        </div>

        <!-- PAGINATION — only when filters active and multiple pages -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                $params = $_GET;
                for ($i = 1; $i <= $total_pages; $i++) {
                    $params['page'] = $i;
                    $qs     = http_build_query($params);
                    $active = ($i == $page) ? 'active' : '';
                    echo "<a href='shop.php?{$qs}' class='{$active}'>{$i}</a>";
                }
                ?>
            </div>
        <?php endif; ?>
    </section>

    <?php else: ?>


    <!-- ===================== CATEGORY SECTIONS ===================== -->
    <div class="category-showcase">
        <?php foreach ($categories as $cat): ?>
        <section class="category-block">
            <div class="category-header">
                <h2>
                    <a href="<?php echo $categoryPages[$cat]; ?>">
                        <?php echo $categoryLabels[$cat]; ?>
                    </a>
                </h2>

                <a href="<?php echo $categoryPages[$cat]; ?>" class="view-more-link">
                    VIEW MORE >
                </a>
            </div>

            <div class="category-products">
            <?php
            $rows = [];
            while ($row = $categoryPreviews[$cat]->fetch_assoc()) {
                $rows[] = $row;
            }
            if (!empty($rows)):
                foreach ($rows as $row):
                    $inWishlist = isset($_SESSION['wishlist']) && in_array($row['id'], $_SESSION['wishlist']);
            ?>
                <div class="product-shop-product-card">
                    <div class="product-shop-image-wrapper">
                        <a href="product-info.php?id=<?php echo $row['id']; ?>">
                            <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </a>
                        <div class="product-shop-wishlist-form">
                            <input type="hidden" class="product-id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" class="current-page" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <button type="button" class="product-shop-wishlist-button js-wishlist-btn">
                                <i class="<?php echo $inWishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                            </button>
                        </div>
                    </div>

                    <div class="product-shop-product-description">
                        <span><?php echo htmlspecialchars(ucfirst($row['category'])); ?></span>
                        <h5>
                            <a href="product-info.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </h5>
                    </div>

                    <h4>R<?php echo number_format($row['price'], 2); ?></h4>

                    <form action="cart/cart-handler.php" method="POST" class="product-shop-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit" class="product-shop-cart-button">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </button>
                    </form>
                </div>
            <?php
                endforeach;
            else: ?>
                <p class="shop-category-empty">No products yet in this category.</p>
            <?php endif; ?>
            </div>

        </section>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>


    <?php include 'includes/footer.php'; ?>
    <script src="index.js"></script>
</body>
</html>