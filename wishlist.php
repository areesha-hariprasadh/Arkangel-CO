<?php
session_start();
include 'includes/db.php';

// Store the previous page (only if it's not the wishlist itself)
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wishlist.php') === false) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
}

$back_link = isset($_SESSION['previous_page']) ? $_SESSION['previous_page'] : 'shop.php';

$is_logged_in = isset($_SESSION['user_id']);
$wishlist_products = [];

if ($is_logged_in) {
    $user_id = (int) $_SESSION['user_id'];

    /*============= REMOVE PRODUCT FROM WISHLIST ================*/
    if (isset($_GET['remove'])) {
        $remove_id = (int) $_GET['remove'];

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $remove_id);
        $stmt->execute();
        $stmt->close();

        header("Location: wishlist.php");
        exit();
    }

    /*============= GET PRODUCTS FROM DATABASE ================*/
    $stmt = $conn->prepare(
        "SELECT p.* FROM products p
         INNER JOIN wishlist w ON w.product_id = p.id
         WHERE w.user_id = ?
         ORDER BY w.date_added DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $wishlist_products[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>

    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>

<body>
    <!-- ============ TOAST ============= -->
    <div id="toast" class="toast"></div>


    <!-- ============ HEADER ============= -->
    <?php include 'includes/header.php'; ?>


    <!-- ============ WISHLIST PAGE ============= -->
    <section id="wishlist-page">
        <section class="wishlist-page-header">
            <h1 class="wishlist-page-title">My Wishlist</h1>
            <p class="wishlist-page-subtitle">Your favourites, saved for you.</p>
        </section>

        <?php if (!$is_logged_in): ?>

            <!-- ============ NOT LOGGED IN ============= -->
            <section class="wishlist-empty">
                <i class="fa-regular fa-heart"></i>
                <h2>Login to see your wishlist</h2>
                <p>Your saved pieces are tied to your account. Log in to view them.</p>
                <a href="login.php" class="wishlist-shop-btn">
                    LOGIN
                </a>
            </section>

        <?php elseif (empty($wishlist_products)): ?>

            <!-- ============ EMPTY WISHLIST ============= -->
            <section class="wishlist-empty">
                <i class="fa-regular fa-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Save your favourite jewelry pieces here.</p>
                <a href="<?php echo htmlspecialchars($back_link); ?>" class="wishlist-shop-btn">
                    CONTINUE SHOPPING
                </a>
            </section>

        <?php else: ?>

            <!-- ============ WISHLIST PRODUCT GRID ============= -->
            <section class="wishlist-page-grid">
                <?php foreach ($wishlist_products as $product): ?>

                <article class="wishlist-page-card" data-id="<?php echo $product['id']; ?>">
                    <div class="wishlist-page-image-wrapper">

                        <a href="product-infor.php?id=<?php echo $product['id']; ?>">
                            <img
                                src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="wishlist-page-image">
                        </a>

                        <button
                            type="button"
                            class="wishlist-page-heart-btn js-wishlist-toggle"
                            data-id="<?php echo $product['id']; ?>"
                        >
                            <i class="fa-solid fa-heart"></i>
                        </button>
                    </div>

                    <div class="wishlist-page-content">
                        <p class="wishlist-page-category">
                            <?php echo ucfirst($product['category']); ?>
                        </p>

                        <h2 class="wishlist-page-product-name">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h2>

                        <p class="wishlist-page-product-price">
                            R<?php echo number_format($product['price']); ?>
                        </p>

                        <div class="wishlist-page-actions">
                            <button
                                type="button"
                                class="wishlist-page-add-btn js-wishlist-add-to-cart"
                                data-id="<?php echo $product['id']; ?>"
                            >
                                ADD TO BAG
                            </button>

                            <a href="wishlist.php?remove=<?php echo $product['id']; ?>"
                                class="wishlist-page-delete-btn">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </article>

                <?php endforeach; ?>
            </section>

            <section class="wishlist-page-bottom-section">
                <div class="wishlist-page-bottom-heart">
                    <i class="fa-regular fa-heart"></i>
                </div>

                <h2 class="wishlist-page-bottom-title">Still loving your picks?</h2>

                <p class="wishlist-page-bottom-text">
                    Add more pieces you adore and shop them anytime.
                </p>

                <a href="<?php echo htmlspecialchars($back_link); ?>" class="wishlist-page-continue-btn">
                    CONTINUE SHOPPING
                </a>
            </section>

        <?php endif; ?>
    </section>


    <!-- ============ FOOTER ============= -->
    <?php include 'includes/footer.php'; ?>

    <script src="index.js"></script>
</body>
</html>