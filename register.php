<?php
require_once __DIR__ . "/config/db.php";
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $fullname = trim($_POST["fullname"]);

    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        $errors[] = "Vui lòng nhập đầy đủ các thông tin bắt buộc.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Mật khẩu nhập lại không khớp.";
    } else {
        // Kiểm tra trùng username/email
        $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($checkUser) > 0) {
            $errors[] = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (username, email, password, fullname) 
                    VALUES ('$username', '$email', '$hashedPassword', '$fullname')";

            if (mysqli_query($conn, $sql)) {
                $_SESSION["success"] = "Đăng ký thành công! Bạn có thể đăng nhập.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Có lỗi xảy ra: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include "includes/header.php"; ?>
<?php include "includes/navbar.php"; ?>

<div class="container mt-4" style="max-width: 600px;">
    <h2 class="mb-4 text-center">Đăng ký tài khoản</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error)
                echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Tên đăng nhập *</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mật khẩu *</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nhập lại mật khẩu *</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Họ và tên *</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
    </form>

    <div class="mt-3 text-center">
        <p>Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</div>

<?php include "includes/footer.php"; ?>