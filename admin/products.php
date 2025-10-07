<?php
session_start();
require "../config/db.php";

// Lấy danh sách hãng và loại
$brands = $conn->query("SELECT * FROM brands ORDER BY name ASC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// LẤY DANH SÁCH SẢN PHẨM
$products = "
    SELECT p.*, b.name AS brand_name, c.name AS category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
";
$result_products = $conn->query($products);


// XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit();
}

// THÊM / SỬA SẢN PHẨM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"] ?? "";
    $name = $_POST["name"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $description = $_POST["description"];
    $brand_id = $_POST["brand_id"];
    $category_id = $_POST["category_id"];

    $imageNames = [];

    // Nếu là sửa thì lấy ảnh cũ
    if (!empty($id)) {
        $oldData = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
        $oldImages = $oldData['image'] ?? "";
    }

    // Nếu có upload ảnh mới
    if (isset($_FILES['img']) && count($_FILES['img']['name']) > 0) {
        $total = count($_FILES['img']['name']);
        $total = $total > 3 ? 3 : $total;

        for ($i = 0; $i < $total; $i++) {
            $tmp_name = $_FILES['img']['tmp_name'][$i];
            $original_name = basename($_FILES['img']['name'][$i]);
            if ($tmp_name) {
                $target_dir = "../public/uploads/";
                $new_name = time() . '_' . $i . '_' . $original_name;
                $target_path = $target_dir . $new_name;

                if (move_uploaded_file($tmp_name, $target_path)) {
                    $imageNames[] = $new_name;
                }
            }
        }
    }

    // Xử lý chuỗi ảnh
    if (!empty($imageNames)) {
        $img = implode(',', $imageNames); // có ảnh mới → ghi đè
    } else {
        $img = $oldImages ?? ""; // không có ảnh mới → giữ ảnh cũ
    }

    if (!empty($id)) {
        // Cập nhật
        $query = "UPDATE products 
          SET name='$name', description='$description', price='$price', stock='$stock',
              image='$img', brand_id='$brand_id', category_id='$category_id'
          WHERE id=$id";
    } else {
        // Thêm mới
        $query = "INSERT INTO products (name, description, price, stock, image, brand_id, category_id) 
          VALUES ('$name', '$description', '$price', '$stock', '$img', '$brand_id', '$category_id')";
    }

    $conn->query($query);
    header("Location: products.php");
    exit();
}



// Nếu bấm sửa -> lấy dữ liệu sản phẩm
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editData = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm - Admin</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php" ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <h4 class="mb-0"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</h4>
                <a href="products.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm sản phẩm</a>
            </div>
            <div class="card-body">

                <!-- FORM THÊM / SỬA -->
                <form method="POST" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Tên sản phẩm"
                                value="<?= $editData['name'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="price" class="form-control" placeholder="Giá"
                                value="<?= $editData['price'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="stock" class="form-control" placeholder="Số lượng"
                                value="<?= $editData['stock'] ?? '' ?>" required>
                        </div>

                        <div class="col-md-2">
                            <select name="brand_id" class="form-select" required>
                                <option value="">-- Chọn hãng --</option>
                                <?php while ($b = $brands->fetch_assoc()): ?>
                                    <option value="<?= $b['id'] ?>" <?= (isset($editData['brand_id']) && $editData['brand_id'] == $b['id']) ? "selected" : "" ?>>
                                        <?= htmlspecialchars($b['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn loại --</option>
                                <?php while ($c = $categories->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>" <?= (isset($editData['category_id']) && $editData['category_id'] == $c['id']) ? "selected" : "" ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="file" class="form-control" name="img[]" multiple accept="image/*"
                                onchange="limitFiles(this)">
                        </div>
                        <div class="col-md-12">
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Mô tả sản phẩm"><?= $editData['description'] ?? '' ?></textarea>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editData ? "Cập nhật" : "Thêm" ?>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- DANH SÁCH SẢN PHẨM -->
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Hãng</th>
                            <th>Loại</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Mô tả</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_products->fetch_assoc()): ?>
                            <?php
                            $images = !empty($row['image']) ? explode(',', $row['image']) : [];
                            $mainImage = $images[2] ?? null;
                            ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if ($mainImage): ?>
                                        <img src="../public/uploads/<?= htmlspecialchars($mainImage) ?>" width="60">
                                    <?php else: ?>
                                        <span class="text-muted">Không có ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['brand_name']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><?= htmlspecialchars($row['stock']) ?></td>
                                <td><?= number_format($row['price'], 0, ',', '.') ?>₫</td>
                                <td
                                    style="max-width: 300px ;word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                    <?= mb_strimwidth($row['description'], 0, 70, '...'); ?>
                                </td>
                                <td>
                                    <a href="products.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
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