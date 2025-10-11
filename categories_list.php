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
$count_sql = "SELECT COUNT(*) AS total FROM products $where_sql";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];

// Lấy sản phẩm (có phân trang)
$sql = "SELECT * FROM products $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result_products = $conn->query($sql);

$total_pages = ceil($total_products / $limit);

// Lấy danh sách hãng
$sql_brands = "SELECT * FROM brands ORDER BY name ASC";
$result_brands = $conn->query($sql_brands);
?>

<div class="container py-4">
    <h2 class="text-center mb-4"><?= htmlspecialchars($category_name) ?></h2>

    <!-- Lọc theo hãng -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        <?php while ($brand = $result_brands->fetch_assoc()): ?>
            <a href="categories_list.php?id=<?= $category_id ?>&price=<?= $price_filter ?>&brand=<?= $brand['id'] ?>"
                class="btn <?= ($brand_id == $brand['id']) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                <?= htmlspecialchars($brand['name']) ?>
            </a>
        <?php endwhile; ?>
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
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <?php if ($mainImage): ?>
                            <img src="public/uploads/<?= htmlspecialchars(trim($mainImage)) ?>" class="card-img-top"
                                alt="<?= htmlspecialchars($row['name']) ?>">
                        <?php else: ?>
                            <div class="text-center text-muted p-5">Không có ảnh</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <hr>
                            <p><?= mb_strimwidth($row['description'], 0, 70, '...'); ?></p>
                            <p class="card-text text-danger fw-bold">
                                Giá: <?= number_format($row['price'], 0, ',', '.') ?> đ
                            </p>
                            <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-eye"></i> Xem
                            </a>
                        </div>
                    </div>
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