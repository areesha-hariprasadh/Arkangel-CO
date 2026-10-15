<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (basename($_SERVER['PHP_SELF']) !== 'cart.php') {
    $_SESSION['last_shop_page'] = $_SERVER['REQUEST_URI'];
}

$currentPage = basename($_SERVER['PHP_SELF']);

$shopPages = [
    'shop.php',
    'necklaces.php',
    'earrings.php',
    'bracelets.php',
    'rings.php',
    'jewelry-sets.php',
    'product-info.php'
];

$cartCount = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>

<header id="ark-navbar">
    <div class="ark-navbar-container">

        <!-- MOBILE MENU BUTTON -->
        <div class="ark-menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </div>

        <!-- LOGO -->
        <div class="ark-logo">
            <a href="index.php">
                <img src="includes/image-files/logo.png" alt="Arkangel & Co Logo">
            </a>
        </div>

        <!-- NAVIGATION -->
        <ul class="ark-nav-links">

            <li>
                <a href="index.php"
                   class="<?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                    HOME
                </a>
            </li>

            <li>
                <a href="shop.php"
                   class="<?php echo in_array($currentPage, $shopPages) ? 'active' : ''; ?>">
                    SHOP
                </a>
            </li>

            <li>
                <a href="about-us.php"
                   class="<?php echo ($currentPage === 'about-us.php') ? 'active' : ''; ?>">
                    ABOUT
                </a>
            </li>

            <li>
                <a href="contact-us.php"
                   class="<?php echo ($currentPage === 'contact-us.php') ? 'active' : ''; ?>">
                    CONTACT US
                </a>
            </li>

        </ul>

        <!-- ICONS -->
        <div class="ark-icons">

            <a href="login.php">
                <i class="fa-regular fa-user"></i>
            </a>

            <button class="ark-search-toggle" aria-label="Toggle search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <a href="wishlist.php">
                <i class="fa-regular fa-heart"></i>
            </a>

            <a href="cart.php" class="cart-icon-wrapper">
                <i class="fa-solid fa-cart-shopping"></i>

                <span class="cart-count"
                    <?php if ($cartCount === 0) echo 'style="display:none;"'; ?>>
                    <?php echo $cartCount; ?>
                </span>
            </a>

        </div>

    </div>
</header>

<div class="ark-search-bar" id="arkSearchBar">
    <form action="search.php" method="GET">
        <input
            type="text"
            name="query"
            placeholder="Search jewellery..."
            id="arkSearchInput"
        >
        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.querySelector('.ark-menu-toggle');
    const nav = document.querySelector('.ark-nav-links');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('active');
        });
    }

});

function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');

    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

const searchToggle = document.querySelector('.ark-search-toggle');
const searchBar = document.getElementById('arkSearchBar');
const searchInput = document.getElementById('arkSearchInput');

if (searchToggle && searchBar) {
    searchToggle.addEventListener('click', function () {
        searchBar.classList.toggle('active');
        if (searchBar.classList.contains('active')) {
            searchInput.focus();
        }
    });
}
</script>