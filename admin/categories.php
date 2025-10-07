<?php
session_start();
require "../config/db.php";

// THÊM / SỬA LOẠI
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $conn->real_escape_string($_POST['id']) : "";
    $id_edit = isset($_POST['id_edit']) ? $conn->real_escape_string($_POST['id_edit']) : null;
    $name = $conn->real_escape_string($_POST["name"]);
    $description = $conn->real_escape_string($_POST["description"]);

    if ($id_edit) {
        // update
        $query = "UPDATE categories 
              SET id='$id', name='$name', description='$description' 
              WHERE id='$id_edit'";
    } else {
        // Thêm mới
        $query = "INSERT INTO categories (id, name, description) 
              VALUES ('$id', '$name', '$description')";
    }

    if (!$conn->query($query)) {
        die("Lỗi SQL: " . $conn->error);
    }

    header("Location: categories.php");
    exit();
}

// NẾU BẤM SỬA -> lấy dữ liệu cần sửa
$editData = null;
if (isset($_GET['edit'])) {
    $id = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SELECT * FROM categories WHERE id='$id'");
    if ($result && $result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
}

// XÓA LOẠI
if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id='$id'");
    header("Location: categories.php");
    exit();
}

// LẤY DANH SÁCH LOẠI (đặt cuối cùng)
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý loại sản phẩm</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php" ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm me-2">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <h4 class="mb-0"><i class="fa-solid fa-list"></i> Quản lý loại sản phẩm</h4>
                <a href="categories.php" class="btn btn-success">
                    <i class="fa-solid fa-plus"></i> Thêm loại
                </a>
            </div>
            <div class="card-body">
                <!-- FORM THÊM / SỬA -->
                <form method="POST" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <?php if ($editData): ?>
                                <input type="text" name="id" class="form-control" value="<?= $editData['id'] ?>" required>
                                <input type="hidden" name="id_edit" value="<?= $editData['id'] ?>">
                            <?php else: ?>
                                <input type="text" name="id" class="form-control" placeholder="Mã loại" required>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Tên loại"
                                value="<?= $editData['name'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="description" class="form-control" placeholder="Mô tả"
                                value="<?= $editData['description'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editData ? "Cập nhật" : "Thêm" ?>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- DANH SÁCH LOẠI -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Tên loại</th>
                                <th>Mô tả</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <a href="categories.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="categories.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Bạn có chắc muốn xóa loại này?');">
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
    </div>
</body>

</html>