<?php
session_start();
require "../config/db.php";

// Xử lý khi nhấn nút nhập hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_submit'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $import_price = floatval($_POST['import_price']);
    $sell_price = floatval($_POST['sell_price']);

    if ($product_id > 0 && $quantity > 0 && $import_price > 0 && $sell_price > 0) {
        // Lấy tên sản phẩm
        $get_name = $conn->query("SELECT name FROM products WHERE id = $product_id");
        $product_name = $get_name->fetch_assoc()['name'];

        // Ghi vào bảng imports
        $stmt = $conn->prepare("INSERT INTO imports (product_id, product_name, quantity, import_price, sell_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isidd", $product_id, $product_name, $quantity, $import_price, $sell_price);
        $stmt->execute();

        // Cập nhật tồn kho
        $conn->query("UPDATE products SET stock = stock + $quantity, price = $sell_price WHERE id = $product_id");

        $_SESSION['success'] = "Đã nhập $quantity sản phẩm '$product_name'";
        header("Location: imports.php");
        exit;
    } else {
        $_SESSION['error'] = "Vui lòng nhập đủ thông tin hợp lệ!";
    }
}

// Lọc
$selected_category = $_GET['category_id'] ?? "";
$selected_month = $_GET['month'] ?? "";
$selected_year = $_GET['year'] ?? "";

// Danh mục
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$categories = $conn->query($sql_categories);

// Sản phẩm
if (!empty($selected_category)) {
    $sql_products = "SELECT id, name FROM products WHERE category_id = '$selected_category' ORDER BY name ASC";
} else {
    $sql_products = "SELECT id, name FROM products ORDER BY name ASC";
}
$products = $conn->query($sql_products);

// Điều kiện lọc lịch sử
$where = [];
if (!empty($selected_month)) {
    $where[] = "MONTH(import_date) = '$selected_month'";
}
if (!empty($selected_year)) {
    $where[] = "YEAR(import_date) = '$selected_year'";
}

$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$sql_imports = "SELECT * FROM imports $where_sql ORDER BY import_date DESC";
$imports = $conn->query($sql_imports);

// ✅ XUẤT FILE EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $month_name = !empty($selected_month) ? sprintf("%02d", $selected_month) : date("m");
    $year_name = !empty($selected_year) ? $selected_year : date("Y");
    $filename = "lich_su_nhap_hang_{$month_name}_{$year_name}.xls";

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$filename}");

    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá nhập (VNĐ)</th>
            <th>Giá bán (VNĐ)</th>
            <th>Ngày nhập</th>
          </tr>";

    $result = $conn->query($sql_imports);
    while ($r = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>" . htmlspecialchars($r['product_name']) . "</td>
                <td>{$r['quantity']}</td>
                <td>" . number_format($r['import_price'], 0, ',', '.') . "</td>
                <td>" . number_format($r['sell_price'], 0, ',', '.') . "</td>
                <td>{$r['import_date']}</td>
              </tr>";
    }
    echo "</table>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Nhập hàng - Quản trị</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>

<body class="bg-light">
    <?php include "../admin/index.php"; ?>

    <div class="container my-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Quản lý nhập hàng</h4>
                <a href="../admin/index.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i></a>
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
                        <form method="GET" class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Lọc theo loại sản phẩm</label>
                                <select name="category_id" class="form-select" onchange="this.form.submit()">
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
                                    <label class="form-label">Sản phẩm</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">-- Chọn sản phẩm --</option>
                                        <?php while ($p = $products->fetch_assoc()): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" name="quantity" class="form-control" min="1" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá nhập (VNĐ)</label>
                                    <input type="number" name="import_price" class="form-control" min="0" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá bán (VNĐ)</label>
                                    <input type="number" name="sell_price" class="form-control" min="0" required>
                                </div>

                                <div class="col-md-1 mb-3 d-grid">
                                    <label class="form-label invisible">.</label>
                                    <button type="submit" name="import_submit" class="btn btn-success">Nhập</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lịch sử nhập hàng -->
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span>Lịch sử nhập hàng</span>
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex gap-2">
                                <select name="month" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Tháng --</option>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= ($selected_month == $m) ? 'selected' : '' ?>>Tháng
                                            <?= $m ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <select name="year" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Năm --</option>
                                    <?php
                                    $currentYear = date("Y");
                                    if (empty($selected_year))
                                        $selected_year = $currentYear;
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                                        <option value="<?= $y ?>" <?= ($selected_year == $y) ? 'selected' : '' ?>><?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </form>

                            <!-- Nút xuất Excel -->
                            <a href="imports.php?export=excel&month=<?= $selected_month ?>&year=<?= $selected_year ?>"
                                class="btn btn-success btn-sm fw-bold">Xuất Excel
                            </a>
                        </div>
                    </div>

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
                                        <td colspan="6" class="text-muted">Không có dữ liệu</td>
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