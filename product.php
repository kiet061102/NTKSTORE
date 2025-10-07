<?php
include 'includes/navbar.php';
include 'config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;
// Lấy thông tin sản phẩm
$query = "
    SELECT p.*, b.name AS brand_name, c.name AS category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = $id
";
$product = $conn->query($query)->fetch_assoc();

if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// Xử lý ảnh
$images = !empty($product['image']) ? explode(',', $product['image']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

}
?>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="row g-0">
                <!-- Ảnh sản phẩm -->
                <div class="col-md-6 p-3">
                    <?php if ($images): ?>
                        <div id="carouselProduct" class="carousel slide">
                            <div class="carousel-inner">
                                <?php foreach ($images as $i => $img): ?>
                                    <div class="carousel-item <?= $i == 2 ? 'active' : '' ?>">
                                        <img src="public/uploads/<?= htmlspecialchars($img) ?>"
                                            class="d-block w-100 img-fluid rounded" alt="Ảnh sản phẩm">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($images) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduct"
                                    data-bs-slide="prev">
                                    <i class="fa-solid fa-chevron-left fa-2x text-secondary"></i>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselProduct"
                                    data-bs-slide="next">
                                    <i class="fa-solid fa-chevron-right fa-2x text-secondary"></i>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <img src="public/no-image.png" class="img-fluid rounded" alt="Không có ảnh">
                    <?php endif; ?>
                </div>

                <!-- Thông tin sản phẩm -->
                <div class="col-md-6 p-4 d-flex flex-column">
                    <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h2>
                    <h4 class="text-danger fw-bold mb-3"><?= number_format($product['price'], 0, ',', '.') ?>₫</h4>
                    <p><span class="fw-semibold">Hãng:</span> <?= htmlspecialchars($product['brand_name']) ?></p>
                    <p><span class="fw-semibold">Loại:</span> <?= htmlspecialchars($product['category_name']) ?></p>
                    <p><span class="fw-semibold">Số lượng còn:</span> <?= (int) $product['stock'] ?></p>
                    <p>
                        <span class="fw-semibold">Trạng thái:</span>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge bg-success">Còn hàng</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Hết hàng</span>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <p><span class="fw-semibold">Mô
                            tả:</span><br><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <div class="mt-auto">
                        <?php if ($product['stock'] > 0): ?>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <div class="mb-3">
                                    <label for="qty" class="fw-semibold">Số lượng:</label>
                                    <input type="number" name="quantity" id="qty" value="1" min="1"
                                        max="<?= $product['stock'] ?>" class="form-control w-25">
                                </div>
                                <?php if ($user_id): ?>
                                    <div>
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg me-2">
                                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                </form>
                                <form method="POST" action="checkout.php" class="d-inline">
                                    <input type="hidden" name="buy_now" value="1">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1" id="buyNowQty">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fa-solid fa-bolt"></i> Mua ngay
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning fw-bold mt-3">Bạn cần <a href="login.php"
                                    class="text-decoration-underline">đăng
                                    nhập</a> để mua hàng.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fa-solid fa-ban"></i> Tạm hết hàng
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<script>
    document.getElementById("qty")?.addEventListener("input", function () {
        document.getElementById("buyNowQty").value = this.value;
    });
</script>