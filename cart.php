<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = intval($_SESSION['user_id']);

// Thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($product_id > 0 && $quantity > 0) {
        $cartResult = $conn->query("SELECT * FROM carts WHERE user_id = $user_id");
        if (!$cartResult) {
            die("Lỗi SQL (carts): " . $conn->error);
        }

        $cart = $cartResult->fetch_assoc();

        if (!$cart) {
            $conn->query("INSERT INTO carts (user_id, created_at) VALUES ($user_id, NOW())");
            $cart_id = $conn->insert_id;
        } else {
            $cart_id = $cart['id'];
        }

        $check = $conn->query("SELECT * FROM cart_details WHERE cart_id = $cart_id AND product_id = $product_id");
        if (!$check) {
            die("Lỗi SQL (cart_details): " . $conn->error);
        }

        if ($check->num_rows > 0) {
            $conn->query("UPDATE cart_details SET quantity = quantity + $quantity 
                          WHERE cart_id = $cart_id AND product_id = $product_id");
        } else {
            $conn->query("INSERT INTO cart_details (cart_id, product_id, quantity) 
                          VALUES ($cart_id, $product_id, $quantity)");
        }

        $_SESSION['success'] = "Đã thêm sản phẩm vào giỏ hàng!";
        header("Location: cart.php");
        exit;
    }
}

// Cập nhật số lượng 
if (isset($_POST['update'])) {
    $cd_id = intval($_POST['update']);
    $qty = intval($_POST['quantities'][$cd_id] ?? 1);

    // Lấy tồn kho sản phẩm
    $product = $conn->query("SELECT stock FROM products WHERE id = (
    SELECT product_id FROM cart_details WHERE id = $cd_id
)")->fetch_assoc();

    $stock = $product['stock'] ?? 0;

    // Giới hạn số lượng
    if ($qty > $stock) {
        $qty = $stock;
    }

    if ($qty > 0) {
        $conn->query("UPDATE cart_details SET quantity = $qty WHERE id = $cd_id");
    } else {
        $conn->query("DELETE FROM cart_details WHERE id = $cd_id");
    }


    header("Location: cart.php");
    exit;
}

// Xóa sản phẩm 
if (isset($_POST['delete'])) {
    $cd_id = intval($_POST['delete']);
    $conn->query("DELETE FROM cart_details WHERE id = $cd_id");

    $_SESSION['success'] = "Đã xóa sản phẩm khỏi giỏ hàng!";
    header("Location: cart.php");
    exit;
}


// Hiển thị giỏ hàng
$sql = "
    SELECT cd.*, p.name, p.price, p.image, p.stock
    FROM cart_details cd
    JOIN carts c ON cd.cart_id = c.id
    JOIN products p ON cd.product_id = p.id
    WHERE c.user_id = $user_id
";

$result = $conn->query($sql);
if (!$result) {
    die("Lỗi SQL (load cart): " . $conn->error . "<br>Query: " . $sql);
}
?>
<?php include "includes/header.php"; ?>
<?php include "includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>Giỏ hàng của bạn</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'];
        unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <form method="POST" action="cart.php">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Hình</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php $subtotal = $row['price'] * $row['quantity']; ?>
                        <?php $total += $subtotal; ?>
                        <?php
                        $images = explode(',', $row['image']);
                        $firstImage = $images[2] ?? 'no-image.png';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <img src="public/uploads/<?= htmlspecialchars($firstImage) ?>" width="100">
                            </td>
                            <td><?= number_format($row['price'], 0, ',', '.') ?>₫</td>
                            <td style="width:120px;">
                                <div class="d-flex justify-content-center align-items-center">
                                    <input type="number" name="quantities[<?= $row['id'] ?>]" value="<?= $row['quantity'] ?>"
                                        min="1" max="<?= $row['stock'] ?>" class="form-control text-center"
                                        style="max-width:70px;">

                                    <button type="submit" name="update" value="<?= $row['id'] ?>"
                                        class="btn btn-sm btn-primary ms-2">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </td>
                            <td><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                            <td>
                                <button type="submit" name="delete" value="<?= $row['id'] ?>" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Tổng tiền:</td>
                        <td colspan="2" class="fw-bold text-danger"><?= number_format($total, 0, ',', '.') ?>₫</td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">Tiếp tục mua hàng</a>
                <a href="checkout.php" class="btn btn-success">Thanh toán</a>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info">Giỏ hàng trống</div>
    <?php endif; ?>
</div>