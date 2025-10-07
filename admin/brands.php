<?php
session_start();
require "../config/db.php";

// LẤY DANH SÁCH HÃNG
$brands = $conn->query("SELECT * FROM brands ORDER BY id DESC");

// XÓA HÃNG
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM brands WHERE id=$id");
    header("Location: brands.php");
    exit();
}

// THÊM / SỬA HÃNG
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"] ?? "";
    $name = $_POST["name"];
    $description = $_POST["description"];

    if (!empty($id)) {
        // Cập nhật
        $query = "UPDATE brands SET name='$name', description='$description' WHERE id=$id";
    } else {
        // Thêm mới
        $query = "INSERT INTO brands (name, description) VALUES ('$name', '$description')";
    }
    $conn->query($query);

    header("Location: brands.php");
    exit();
}

// NẾU BẤM SỬA
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editData = $conn->query("SELECT * FROM brands WHERE id=$id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý hãng sản xuất</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php" ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <h4 class="mb-0"><i class="fa-brands fa-apple"></i> Quản lý hãng sản xuất</h4>
                <a href="brands.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm hãng</a>
            </div>
            <div class="card-body">
                <!-- FORM THÊM / SỬA -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Tên hãng"
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

                <!-- DANH SÁCH HÃNG -->
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Tên hãng</th>
                            <th>Mô tả</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $brands->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= mb_strimwidth($row['description'], 0, 100, '...'); ?></td>
                                <td>
                                    <a href="brands.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="brands.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Bạn có chắc muốn xóa hãng này?');">
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