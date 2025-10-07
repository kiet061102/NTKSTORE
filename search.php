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
$sql = "SELECT * FROM products $search ORDER BY id DESC LIMIT $limit OFFSET $offset";
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
                $mainImage = $images[0] ?? null;
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