<?php
include 'includes/header.php';
include 'includes/navbar.php';
require "config/db.php";

// Lấy id danh mục từ URL
$category_id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';

// Lấy brand_id nếu có lọc
$brand_id = isset($_GET['brand']) ? intval($_GET['brand']) : 0;

// Lọc giá
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';

// Phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Điều kiện WHERE
$where = [];
if (!empty($category_id)) {
    $where[] = "category_id = '$category_id'";
}
if ($brand_id > 0) {
    $where[] = "brand_id = $brand_id";
}
if ($price_filter == "under1tr") {
    $where[] = "price < 1000000";
} elseif ($price_filter == "1to3") {
    $where[] = "price BETWEEN 1000000 AND 3000000";
} elseif ($price_filter == "up3tr") {
    $where[] = "price > 3000000";
}
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Lấy tên danh mục
if (!empty($category_id)) {
    $cat_sql = "SELECT name FROM categories WHERE id = '$category_id'";
    $cat_result = $conn->query($cat_sql);
    $category_name = ($cat_result && $cat_result->num_rows > 0)
        ? "Sản phẩm " . $cat_result->fetch_assoc()['name']
        : "Danh mục không tồn tại";
} else {
    $category_name = "Tất cả sản phẩm";
}

// Đếm tổng sản phẩm
$count_sql = "SELECT COUNT(DISTINCT p.id) AS total FROM products p LEFT JOIN reviews r ON p.id = r.product_id $where_sql";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];

// Lấy tất cả sản phẩm + thông tin đánh giá
$sql = "SELECT p.*, COUNT(r.id) AS total_reviews, ROUND(AVG(r.rating), 1) AS avg_rating FROM products p LEFT JOIN reviews r ON p.id = r.product_id $where_sql GROUP BY p.id ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$result_products = $conn->query($sql);

$total_pages = ceil($total_products / $limit);

// Lấy danh sách hãng
$sql_brands = "SELECT * FROM brands ORDER BY name ASC";
$result_brands = $conn->query($sql_brands);

$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $conn->query($sql_categories);
?>

<div class="container py-4">
    <h2 class="text-center mb-4"><?= htmlspecialchars($category_name) ?></h2>

    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        <a href="categories.php"
            class="btn <?= (empty($brand_id) && empty($category_id)) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
            Tất cả
        </a>

        <!-- Lọc theo hãng -->
        <?php while ($brand = $result_brands->fetch_assoc()): ?>
            <a href="categories.php?id=<?= $category_id ?>&price=<?= $price_filter ?>&brand=<?= $brand['id'] ?>"
                class="btn <?= ($brand_id == $brand['id']) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                <?= htmlspecialchars($brand['name']) ?>
            </a>
        <?php endwhile; ?>
    </div>
    <!-- Lọc theo loại -->
    <?php if ($result_categories->num_rows > 0): ?>
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <?php while ($cat = $result_categories->fetch_assoc()): ?>
                <a href="categories.php?id=<?= $cat['id'] ?>&brand=<?= $brand_id ?>&price=<?= $price_filter ?>"
                    class="btn <?= ($category_id == $cat['id']) ? 'btn-dark' : 'btn-outline-dark' ?> btn-sm">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>


<!-- Form lọc giá -->
<form method="get" class="mb-4 text-end">
    <?php if ($category_id > 0): ?>
        <input type="hidden" name="id" value="<?= $category_id ?>">
    <?php endif; ?>
    <?php if ($brand_id > 0): ?>
        <input type="hidden" name="brand" value="<?= $brand_id ?>">
    <?php endif; ?>
    <select name="price" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
        <option value="">-- Lọc theo giá --</option>
        <option value="under1tr" <?= $price_filter == 'under1tr' ? 'selected' : '' ?>>Dưới 1 triệu</option>
        <option value="1to3" <?= $price_filter == '1to3' ? 'selected' : '' ?>>1 triệu đến 3 triệu</option>
        <option value="up3tr" <?= $price_filter == 'up3tr' ? 'selected' : '' ?>>Trên 3 triệu</option>
    </select>
</form>

<div class="row">
    <?php if ($result_products->num_rows > 0): ?>
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
        <p class="text-center text-muted">Không có sản phẩm nào.</p>
    <?php endif; ?>
</div>

<!-- Phân trang -->
<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                <a class="page-link"
                    href="?id=<?= $category_id ?>&brand=<?= $brand_id ?>&price=<?= $price_filter ?>&page=<?= $page - 1 ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                    <a class="page-link"
                        href="?id=<?= $category_id ?>&brand=<?= $brand_id ?>&price=<?= $price_filter ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
                <a class="page-link"
                    href="?id=<?= $category_id ?>&brand=<?= $brand_id ?>&price=<?= $price_filter ?>&page=<?= $page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>