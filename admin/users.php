<?php
session_start();
require "../config/db.php";

// Lấy danh sách user
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

// Xóa user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: users.php");
    exit();
}

// Thêm / Sửa user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"] ?? "";
    $username = $_POST["username"];
    $email = $_POST["email"];
    $role = $_POST["role"];

    if (!empty($id)) {
        // Cập nhật
        $conn->query("UPDATE users SET username='$username', email='$email', role='$role' WHERE id=$id");
    } else {
        // Mặc định password: 123456 (hash)
        $password = password_hash("123456", PASSWORD_BCRYPT);
        $conn->query("INSERT INTO users (username, email, password, role) 
                      VALUES ('$username', '$email', '$password', '$role')");
    }

    header("Location: users.php");
    exit();
}

// Nếu bấm sửa -> lấy dữ liệu
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editData = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php" ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <h4 class="mb-0"><i class="fa-solid fa-users"></i> Quản lý người dùng</h4>
                <a href="users.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm người dùng</a>
            </div>
            <div class="card-body">
                <!-- FORM -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập"
                                value="<?= $editData['username'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="email" class="form-control" placeholder="Email"
                                value="<?= $editData['email'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select" required>
                                <option value="user" <?= (isset($editData['role']) && $editData['role'] == 'user') ? "selected" : "" ?>>User</option>
                                <option value="admin" <?= (isset($editData['role']) && $editData['role'] == 'admin') ? "selected" : "" ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editData ? "Cập nhật" : "Thêm" ?>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- DANH SÁCH -->
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['role'] == 'admin' ? 'danger' : 'secondary' ?>">
                                        <?= htmlspecialchars($row['role']) ?>
                                    </span>
                                </td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <a href="users.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="users.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Xóa người dùng này?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>