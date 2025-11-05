<?php
require_once __DIR__ . "/config/db.php";
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $sql = "SELECT * FROM users WHERE username='$username' OR email='$username' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row["password"])) {
                // Lưu session
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["username"] = $row["username"];
                $_SESSION["fullname"] = $row["fullname"];
                $_SESSION["email"] = $row["email"];
                $_SESSION["role"] = $row["role"];

                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Mật khẩu không đúng.";
            }
        } else {
            $errors[] = "Tên đăng nhập hoặc email không tồn tại.";
        }
    }
}
?>

<?php include "includes/header.php"; ?>
<?php include "includes/navbar.php"; ?>

<div class="container mt-4" style="max-width: 500px;">
    <h2 class="mb-4 text-center">Đăng nhập</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error)
                echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success">
            <?= $_SESSION["success"];
            unset($_SESSION["success"]); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Tên đăng nhập hoặc Email</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
    </form>

    <div class="mt-3 text-center">
        <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    </div>
</div>

<?php include "includes/footer.php"; ?>