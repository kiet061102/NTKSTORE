<?php
session_start();
include "includes/header.php";
require "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Lấy thông tin user
$sql_user = "SELECT fullname FROM users WHERE id = $user_id LIMIT 1";
$result_user = $conn->query($sql_user);
$users = $result_user->fetch_assoc();

// Nếu submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra mật khẩu cũ
    $sql = "SELECT password FROM users WHERE id = $user_id LIMIT 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row || !password_verify($current_password, $row['password'])) {
        $_SESSION['error'] = "Mật khẩu hiện tại không đúng!";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu mới và xác nhận mật khẩu không khớp!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $update = "UPDATE users SET password = '$hashed' WHERE id = $user_id";
        if ($conn->query($update)) {
            $_SESSION['success'] = "Đổi mật khẩu thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra. Vui lòng thử lại.";
        }
    }

    header("Location: changepassword.php");
    exit;
}

include "includes/header.php";
include "includes/navbar.php";
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="col-md-12 text-center py-3">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center"
                        style="width:120px; height:120px;">
                        <i class="fa-solid fa-user" style="font-size:60px; color:#6c757d;"></i>
                    </div>
                    <h5 class="mt-2"><?= htmlspecialchars($users['fullname']) ?></h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-user"></i> Hồ sơ
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-box"></i> Đơn hàng
                    </a>
                    <a href="changepassword.php" class="list-group-item list-group-item-action active">
                        <i class="fa-solid fa-lock"></i> Đổi mật khẩu
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Đổi mật khẩu
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['success'] ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['error'] ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Mật khẩu hiện tại</label>
                            <div class="col-sm-9">
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Mật khẩu mới</label>
                            <div class="col-sm-9">
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Xác nhận mật khẩu</label>
                            <div class="col-sm-9">
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
<style>
    .list-group-item {
        border: none;
        padding: 12px 20px;
        font-size: 15px;
    }

    .list-group-item.active {
        background-color: #ff5722;
        color: #fff;
        font-weight: bold;
    }

    .list-group-item:hover {
        background-color: #ffe0d6;
        color: #ff5722;
    }
</style>