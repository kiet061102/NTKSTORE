<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'config/db.php';

// Lấy 8 sản phẩm mới nhất
$sql = "SELECT * FROM products ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);

// Phân trang
$limit = 8; // số sản phẩm mỗi trang
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số sản phẩm
$countResult = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Lấy sản phẩm cho trang hiện tại
$allProducts = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<body class="bg-light">
  <?php include 'includes/carousel.php'; ?>
  <div class="container py-4">
    <div class="new-product">
      <h2 class="text-center mb-4 mt-4 fw-bold">Sản phẩm mới</h2>
      <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
          $images = !empty($row['image']) ? explode(',', $row['image']) : [];
          $mainImage = $images[2] ?? null;
          ?>
          <div class="col-md-2 mb-4">
            <div class="card h-100">
              <img src="public/uploads/<?= htmlspecialchars(trim($mainImage)) ?>"
                alt="<?= htmlspecialchars($row['name']) ?>" class="card-img-top">
              <div class="card-body">
                <h5 class="card-title"><?= mb_strimwidth($row['name'], 0, 30, '...'); ?></h5>
                <hr>
                <p><?= mb_strimwidth($row['description'], 0, 75, '...'); ?></p>
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
      </div>
    </div>

    <hr class="w-50 mx-auto my-4">

    <div class="all-product">
      <h2 class="text-center mb-4 fw-bold">Tất cả sản phẩm</h2>
      <div class="row">
        <?php
        while ($row = $allProducts->fetch_assoc()):
          $images = !empty($row['image']) ? explode(',', $row['image']) : [];
          $mainImage = $images[2] ?? null;
          ?>
          <div class="col-md-3 mb-4">
            <div class="card h-100">
              <img src="public/uploads/<?= htmlspecialchars(trim($mainImage)) ?>"
                alt="<?= htmlspecialchars($row['name']) ?>" class="card-img-top">
              <div class="card-body">
                <h5 class="card-title"><?= mb_strimwidth($row['name'], 0, 40, '...'); ?></h5>
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
      </div>
    </div>

    <!-- Phân trang -->
    <nav>
      <ul class="pagination justify-content-center">
        <!-- Nút Prev -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= max(1, $page - 1) ?>">Prev</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <!-- Nút Next -->
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>
</body>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
  </script>