<?php
require_once "../config/db.php";

function updateProductStock($conn, $order_id, $old_status, $new_status)
{
    $result = $conn->query("SELECT product_id, quantity FROM order_details WHERE order_id=$order_id");
    if ($result && $result->num_rows > 0) {
        $deduct_statuses = ['Confirmed', 'Shipped', 'Completed'];
        while ($item = $result->fetch_assoc()) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];

            // Nếu trạng thái mới cần trừ và trước đó chưa trừ
            if (in_array($new_status, $deduct_statuses) && !in_array($old_status, $deduct_statuses)) {
                $conn->query("UPDATE products SET stock = stock - $qty WHERE id=$pid");
            }

            // Nếu trạng thái mới là Cancelled mà trước đó đã trừ
            if ($new_status === 'Cancelled' && in_array($old_status, $deduct_statuses)) {
                $conn->query("UPDATE products SET stock = stock + $qty WHERE id=$pid");
            }
        }
    }
}

// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);

    // Lấy trạng thái cũ
    $old_status = $conn->query("SELECT status FROM orders WHERE id=$order_id")->fetch_assoc()['status'] ?? '';

    if ($old_status !== $new_status) {
        updateProductStock($conn, $order_id, $old_status, $new_status);
        $conn->query("UPDATE orders SET status='$new_status' WHERE id=$order_id");
    }

    header("Location: orders.php");
    exit;
}

// Xóa đơn hàng
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Hoàn lại stock nếu đơn chưa Cancelled
    $status = $conn->query("SELECT status FROM orders WHERE id=$id")->fetch_assoc()['status'] ?? '';
    if (in_array($status, ['Confirmed', 'Shipped', 'Completed'])) {
        $result = $conn->query("SELECT product_id, quantity FROM order_details WHERE order_id=$id");
        while ($item = $result->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id={$item['product_id']}");
        }
    }

    $conn->query("DELETE FROM order_details WHERE order_id=$id");
    $conn->query("DELETE FROM orders WHERE id=$id");
    header("Location: orders.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng - Admin</title>
</head>

<body class="bg-light">
<?php include "../admin/index.php" ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fa-solid fa-receipt"></i> Quản lý đơn hàng
            </h4>
            <a href="../admin/index.php" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Ngày đặt</th>
                            <th>Mã đơn</th>
                            <th>Mã khách hàng</th>
                            <th>Người đặt</th>
                            <th>SĐT</th>
                            <th>Địa chỉ</th>
                            <th>Phương thức</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Sản phẩm</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?= $order['created_at'] ?></td>
                                <td>MDH<?= $order['id'] ?></td>
                                <td><?= $order['user_id'] ?></td>
                                <td><?= htmlspecialchars($order['fullname']) ?></td>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td><?= number_format($order['total_price'], 0, ',', '.') ?>đ</td>
                                <td>
                                    <?php if ($order['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Đang chờ xác nhận</span>
                                    <?php elseif ($order['status'] == 'Confirmed'): ?>
                                        <span class="badge bg-info">Đã xác nhận</span>
                                    <?php elseif ($order['status'] == 'Shipped'): ?>
                                        <span class="badge bg-primary">Đang giao hàng</span>
                                    <?php elseif ($order['status'] == 'Completed'): ?>
                                        <span class="badge bg-success">Hoàn thành</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= $order['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#orderDetail<?= $order['id'] ?>">
                                        Xem
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Form đổi trạng thái -->
                                        <form method="post" class="d-flex align-items-center gap-1 mb-0">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" class="form-select form-select-sm w-auto"
                                                style="height:31px;">
                                                <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>
                                                    Đang chờ</option>
                                                <option value="Confirmed" <?= $order['status'] == 'Confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                                <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>
                                                    Đang giao</option>
                                                <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                                <i class="fas fa-redo-alt"></i>
                                            </button>
                                        </form>
                                        <!-- Nút xóa -->
                                        <a class="btn btn-sm btn-danger" href="orders.php?delete=<?= $order['id'] ?>"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Chi tiết đơn hàng -->
                            <tr>
                                <td colspan="11" class="p-0">
                                    <div class="collapse" id="orderDetail<?= $order['id'] ?>">
                                        <div class="card card-body">
                                            <table class="table table-bordered">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>Mã SP</th>
                                                        <th>Tên sản phẩm</th>
                                                        <th>Số lượng</th>
                                                        <th>Giá</th>
                                                        <th>Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $order_id = $order['id'];
                                                    $sql_items = "
                                                        SELECT od.*, p.name AS product_name 
                                                        FROM order_details od
                                                        JOIN products p ON od.product_id = p.id
                                                        WHERE od.order_id = $order_id
                                                    ";
                                                    $items = $conn->query($sql_items);
                                                    while ($item = $items->fetch_assoc()):
                                                        $subtotal = $item['quantity'] * $item['price'];
                                                        ?>
                                                        <tr>
                                                            <td><?= $item['product_id'] ?></td>
                                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                            <td><?= $item['quantity'] ?></td>
                                                            <td><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
                                                            <td><?= number_format($subtotal, 0, ',', '.') ?>đ</td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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