<?php
include "header.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            NTKSTORE
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><?php include "categories.php"; ?></li>
                <li class="nav-item"><a class="nav-link" href="about.php">Giới thiệu</a></li>
            </ul>

            <form class="d-flex me-3" action="search.php" method="GET">
                <input class="form-control me-2" type="search" name="keyword" placeholder="Tìm sản phẩm..."
                    aria-label="Search">
                <button class="btn btn-outline-light" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Nếu đã đăng nhập -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['fullname']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">

                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <!-- Admin chỉ thấy mục Quản trị -->
                                <li><a class="dropdown-item text-warning" href="admin/index.php">
                                        <i class="fa-solid fa-toolbox"></i> Quản trị
                                    </a></li>
                            <?php else: ?>
                                <!-- User thường thấy Trang cá nhân -->
                                <li><a class="dropdown-item" href="profile.php">Trang cá nhân</a></li>
                                <li><a class="dropdown-item" href="orders.php">Đơn hàng</a></li>
                            <?php endif; ?>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Nếu chưa đăng nhập -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fa-solid fa-user"></i> Đăng nhập
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

<div class="container mt-2">