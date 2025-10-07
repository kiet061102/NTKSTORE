<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang quản trị - NTKSTORE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <!-- Nút về trang chủ -->
                <a href="../index.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-house"></i>
                </a>

                <!-- Tiêu đề ở giữa -->
                <h4 class="mb-0"><i class="fa-solid fa-user-shield"></i> Bảng điều khiển - Admin</h4>

                <!-- Nút đăng xuất -->
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
            <div class="card-body">
                <p>Xin chào, <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>!</p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="products.php" class="btn btn-primary w-100 fw-bold">
                            <i class="fa-solid fa-box"></i> Quản lý sản phẩm
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="brands.php" class="btn btn-secondary w-100 fw-bold">
                            <i class="fa-brands fa-apple"></i> Quản lý hãng
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="categories.php" class="btn btn-info w-100 fw-bold">
                            <i class="fa-solid fa-list"></i> Quản lý loại sản phẩm
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="users.php" class="btn btn-success w-100 fw-bold">
                            <i class="fa-solid fa-users"></i> Quản lý người dùng
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="orders.php" class="btn btn-warning w-100 fw-bold">
                            <i class="fa-solid fa-receipt"></i> Quản lý đơn hàng
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="statistics.php" class="btn btn-dark w-100 fw-bold">
                            <i class="fa-solid fa-chart-simple"></i> Thống kê bán hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>