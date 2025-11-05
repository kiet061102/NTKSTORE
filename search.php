<?php
include 'includes/header.php';
include 'includes/navbar.php';
require_once "config/db.php";

// Lấy từ khóa tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";

// Phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search = "";
if (!empty($keyword)) {
    $keyword_safe = $conn->real_escape_string($keyword);
    $search = "WHERE name LIKE '%$keyword_safe%' OR description LIKE '%$keyword_safe%'";
}

// Đếm tổng sản phẩm
$count_sql = "SELECT COUNT(*) AS total FROM products $search";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'] ?? 0;

// Lấy sản phẩm theo từ khóa
$sql = "SELECT p.*, (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) AS total_reviews, COALESCE((SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = p.id), 0) AS avg_rating FROM products p $search ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$result_products = $conn->query($sql);

$total_pages = ceil($total_products / $limit);
?>

<div class="container py-4">
    <h2 class="text-center mb-4">
        Kết quả tìm kiếm: "<?= htmlspecialchars($keyword) ?>"
    </h2>

    <div class="row">
        <?php if ($result_products && $result_products->num_rows > 0): ?>
            <?php while ($row = $result_products->fetch_assoc()): ?>
                <?php
                $images = !empty($row['image']) ? explode(',', $row['image']) : [];
                $mainImage = $images[2] ?? null;
                $total_reviews = $row['total_reviews'] ?? 0;
                $avg_rating = $row['avg_rating'] ?? 0;
                ?>
                <div class="col-md-3 mb-4">
                    <a href="product.php?id=<?= $row['id'] ?>" class="text-decoration-none">
                        <div class="card h-100">
                            <img src="public/uploads/<?= htmlspecialchars(trim($mainImage)) ?>"
                                alt="<?= htmlspecialchars($row['name']) ?>" class="card-img-top w-100"
                                style="height: 255px; object-fit: contain;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mt-auto"><?= mb_strimwidth($row['name'], 0, 35, '...'); ?></h5>
                                <p class="card-text mt-auto"><?= mb_strimwidth($row['description'], 0, 80, '...'); ?></p>

                                <p class="card-text text-danger fw-bold mt-auto">
                                    Giá: <?= number_format($row['price'], 0, ',', '.') ?> đ
                                </p>
                                <p class="card-text mt-auto">
                                    <?php if ($total_reviews > 0): ?>
                                        <?php
                                        $fullStars = floor($avg_rating);
                                        $halfStar = ($avg_rating - $fullStars) >= 0.5 ? 1 : 0;
                                        $emptyStars = 5 - $fullStars - $halfStar;

                                        for ($i = 0; $i < $fullStars; $i++)
                                            echo '<i class="fa-solid fa-star text-warning"></i>';
                                        if ($halfStar)
                                            echo '<i class="fa-solid fa-star-half-stroke text-warning"></i>';
                                        for ($i = 0; $i < $emptyStars; $i++)
                                            echo '<i class="fa-regular fa-star text-warning"></i>';
                                        ?>
                                        <?= $total_reviews ?>
                                        <span class="ms-2">(<?= number_format($avg_rating, 1) ?>)</span>
                                    <?php else: ?>
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fa-regular fa-star text-secondary"></i>
                                        <?php endfor; ?>
                                        <?= $total_reviews ?>
                                        <span class="ms-2">(<?= number_format($avg_rating, 1) ?>)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Không tìm thấy sản phẩm nào.</p>
        <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                    <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                        <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
                    <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>