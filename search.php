<?php
include 'includes/db.php';
include 'includes/header.php';

$search = "";
$results = [];

if (isset($_GET['query']) && trim($_GET['query']) !== '') {
    $search = trim($_GET['query']);

    $sql = "SELECT * FROM products
            WHERE name LIKE ?
            OR category LIKE ?
            OR metal LIKE ?
            OR description LIKE ?
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search<?php if ($search) echo ' — ' . htmlspecialchars($search); ?> | Arkangel & Co</title>

    <link rel="stylesheet" href="includes/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

<div class="search-page">

    <?php if ($search): ?>

        <div class="search-header">
            <p class="search-meta">
                <?php if (count($results) > 0): ?>
                    <?php echo count($results); ?> result<?php echo count($results) !== 1 ? 's' : ''; ?> for
                <?php else: ?>
                    No results for
                <?php endif; ?>
                <span>"<?php echo htmlspecialchars($search); ?>"</span>
            </p>
        </div>

    <?php endif; ?>

    <?php if (!empty($results)): ?>

        <div class="search-results">
            <?php foreach ($results as $row): ?>

                <a href="product-infor.php?id=<?php echo $row['id']; ?>" class="search-card">

                    <div class="search-card-img">
                        <img
                            src="<?php echo htmlspecialchars($row['image']); ?>"
                            alt="<?php echo htmlspecialchars($row['name']); ?>"
                        >
                    </div>

                    <div class="search-card-info">

                        <?php if (!empty($row['badge'])): ?>
                            <span class="search-badge"><?php echo htmlspecialchars($row['badge']); ?></span>
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>

                        <p class="search-category">
                            <?php echo htmlspecialchars($row['category']); ?>
                            <?php if (!empty($row['metal'])): ?>
                                &middot; <?php echo htmlspecialchars($row['metal']); ?>
                            <?php endif; ?>
                        </p>

                        <p class="search-price">
                            R<?php echo number_format($row['price'], 2); ?>
                        </p>

                    </div>

                </a>

            <?php endforeach; ?>
        </div>

    <?php elseif ($search): ?>

        <div class="search-empty">
            <i class="fa-regular fa-face-sad-tear"></i>
            <p>We couldn't find anything matching <strong>"<?php echo htmlspecialchars($search); ?>"</strong>.</p>
            <p>Try searching for a category like <em>necklaces</em>, a metal like <em>gold</em>, or a product name.</p>
        </div>

    <?php else: ?>

        <div class="search-empty">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>Type something in the search bar above to find products.</p>
        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>