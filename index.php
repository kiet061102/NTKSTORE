<link rel="stylesheet" href="public/owlcarousel/css/owl.carousel.min.css">
<link rel="stylesheet" href="public/owlcarousel/css/owl.theme.default.min.css">

<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'config/db.php';

// Lấy 8 sản phẩm mới nhất
$newProducts = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) AS total_reviews, (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = p.id) AS avg_rating FROM products p ORDER BY p.id DESC LIMIT 8");

// Phân trang
$limit = 8; // số sản phẩm mỗi trang
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số sản phẩm
$countResult = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Lấy tất cả sản phẩm + thông tin đánh giá
$allProducts = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) AS total_reviews, (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = p.id) AS avg_rating FROM products p ORDER BY p.id DESC LIMIT $limit OFFSET $offset");

$total_reviews = $row['total_reviews'] ?? 0;
$avg_rating = $row['avg_rating'] ?? 0;
?>

<body class="bg-light">
  <?php include 'includes/carousel.php'; ?>
  <div class="container py-4">
    <div class="new-product">
      <h2 class="text-center mb-4 fw-bold">Sản phẩm mới nhất</h2>
      <div class="row">
        <div class="owl-carousel owl-theme">
          <?php while ($row = $newProducts->fetch_assoc()): ?>
            <?php
            $images = !empty($row['image']) ? explode(',', $row['image']) : [];
            $mainImage = $images[2] ?? 'no-image.jpg';
            ?>
            <div class="item">
              <a href="product.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
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
        </div>
      </div>
    </div>

    <hr class="w-50 mx-auto my-5">

    <div class="all-product">
      <h2 class="text-center mb-4 fw-bold">Tất cả sản phẩm</h2>
      <div class="row">
        <?php
        while ($row = $allProducts->fetch_assoc()):
          $images = !empty($row['image']) ? explode(',', $row['image']) : [];
          $mainImage = $images[2] ?? null;
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
<script src="public/owlcarousel/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/owlcarousel/js/owl.carousel.min.js"></script>

<script>
  $(document).ready(function () {
    $('.owl-carousel').owlCarousel({
      loop: true,
      margin: 10,
      nav: false,
      autoplay: true,
      autoplayTimeout: 1000,
      responsive: {
        0: { items: 1 },
        600: { items: 2 },
        1000: { items: 4 }
      }
    });
  });
</script>