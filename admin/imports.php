<?php
session_start();
require "../config/db.php";

// Xử lý khi nhấn nút nhập hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $import_price = floatval($_POST['import_price']);
    $sell_price = floatval($_POST['sell_price']);

    if ($product_id > 0 && $quantity > 0 && $import_price > 0 && $sell_price > 0) {
        // Lấy tên sản phẩm
        $get_name = $conn->query("SELECT name FROM products WHERE id = $product_id");
        $product_name = $get_name->fetch_assoc()['name'];

        // Ghi vào bảng imports (lịch sử nhập)
        $stmt = $conn->prepare("INSERT INTO imports (product_id, product_name, quantity, import_price, sell_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isidd", $product_id, $product_name, $quantity, $import_price, $sell_price);
        $stmt->execute();

        // Cập nhật tồn kho & giá bán trong products
        $conn->query("UPDATE products SET stock = stock + $quantity, price = $sell_price WHERE id = $product_id");

        $_SESSION['success'] = "Đã nhập $quantity sản phẩm '$product_name' (Giá nhập: $import_price VNĐ, Giá bán: $sell_price VNĐ)";
        header("Location: imports.php");
        exit;
    } else {
        $_SESSION['error'] = "Vui lòng nhập đủ thông tin hợp lệ!";
    }
}

// Lọc theo danh mục
$selected_category = isset($_GET['category_id']) ? $_GET['category_id'] : "";

// Lấy danh sách danh mục
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$categories = $conn->query($sql_categories);

// Lấy danh sách sản phẩm (lọc theo danh mục nếu có)
if (!empty($selected_category)) {
    $sql_products = "SELECT id, name FROM products WHERE category_id = '$selected_category' ORDER BY name ASC";
} else {
    $sql_products = "SELECT id, name FROM products ORDER BY name ASC";
}
$products = $conn->query($sql_products);

// Lấy lịch sử nhập hàng
$sql_imports = "SELECT * FROM imports ORDER BY import_date DESC";
$imports = $conn->query($sql_imports);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Nhập hàng - Quản trị</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php"; ?>

    <div class="container my-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Quản lý nhập hàng</h4>
                <a href="../admin/index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
            <div class="card-body">
                <!-- Thông báo -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php elseif (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Form nhập hàng -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Nhập hàng mới</div>
                    <div class="card-body">
                        <!-- Bộ lọc loại sản phẩm -->
                        <form method="GET" class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Lọc theo loại sản phẩm</label>
                                <select name="category_id" id="category_id" class="form-select"
                                    onchange="this.form.submit()">
                                    <option value="">-- Tất cả loại sản phẩm --</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($selected_category == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </form>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="product_id" class="form-label">Sản phẩm</label>
                                    <select name="product_id" id="product_id" class="form-select" required>
                                        <option value="">-- Chọn sản phẩm --</option>
                                        <?php while ($p = $products->fetch_assoc()): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="quantity" class="form-label">Số lượng</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" min="1"
                                        required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="import_price" class="form-label">Giá nhập (VNĐ)</label>
                                    <input type="number" name="import_price" id="import_price" class="form-control"
                                        min="0" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="sell_price" class="form-label">Giá bán (VNĐ)</label>
                                    <input type="number" name="sell_price" id="sell_price" class="form-control" min="0"
                                        required>
                                </div>

                                <div class="col-md-1 mb-3 d-grid">
                                    <label class="form-label invisible">.</label>
                                    <button type="submit" class="btn btn-success">Nhập</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lịch sử nhập hàng -->
                <div class="card">
                    <div class="card-header bg-dark text-white">Lịch sử nhập hàng</div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập (VNĐ)</th>
                                    <th>Giá bán (VNĐ)</th>
                                    <th>Ngày nhập</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($imports->num_rows > 0): ?>
                                    <?php while ($row = $imports->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td><?= number_format($row['import_price'], 0, ',', '.') ?></td>
                                            <td><?= number_format($row['sell_price'], 0, ',', '.') ?></td>
                                            <td><?= $row['import_date'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-muted">Chưa có lịch sử nhập hàng</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>