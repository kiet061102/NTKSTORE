<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$items = [];
$total_price = 0;

// Lấy danh sách sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    // Trường hợp mua ngay
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $sql = "SELECT id, name, price, image FROM products WHERE id = $product_id LIMIT 1";
    $product = $conn->query($sql)->fetch_assoc();

    if (!$product) {
        die("Sản phẩm không tồn tại.");
    }

    $subtotal = $product['price'] * $quantity;
    $total_price += $subtotal;

    $items[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'image' => $product['image']
    ];

} else {
    // Trường hợp thanh toán giỏ hàng
    $sql = "SELECT cd.product_id, cd.quantity, p.name, p.price, p.image
            FROM cart_details cd
            JOIN carts c ON cd.cart_id = c.id
            JOIN products p ON cd.product_id = p.id
            WHERE c.user_id = $user_id";

    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        die("Giỏ hàng trống.");
    }

    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total_price += $subtotal;

        $items[] = $row;
    }
}

// Xử lý đặt hàng khi submit form
if (isset($_POST['checkout_submit'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment = $conn->real_escape_string($_POST['payment_method']);

    // Thêm đơn hàng
    $sql_order = "INSERT INTO orders (user_id, fullname, phone, address, total_price, payment_method, status, created_at)
                  VALUES ($user_id, '$fullname', '$phone', '$address', $total_price, '$payment', 'Pending', NOW())";

    if ($conn->query($sql_order) === TRUE) {
        $order_id = $conn->insert_id;

        // Thêm chi tiết đơn hàng
        foreach ($items as $item) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            $price = $item['price'];

            // Kiểm tra số lượng còn trong kho
            $check_sql = "SELECT stock FROM products WHERE id = $pid";
            $check_result = $conn->query($check_sql);

            if (!$check_result) {
                die("Lỗi SQL khi kiểm tra tồn kho: " . $conn->error);
            }

            $row = $check_result->fetch_assoc();

            if ($row['stock'] < $qty) {
                die("<div class='alert alert-danger text-center'>Sản phẩm " . htmlspecialchars($item['name']) . " không đủ số lượng trong kho.</div>");
            }

            // Thêm vào order_details
            $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price)
               VALUES ($order_id, $pid, $qty, $price)";
            $conn->query($sql_detail);
        }

        // Nếu không phải "mua ngay" thì xóa giỏ hàng
        if (!isset($_POST['buy_now'])) {
            $conn->query("DELETE cd FROM cart_details cd
                          JOIN carts c ON cd.cart_id = c.id
                          WHERE c.user_id = $user_id");
        }


        if ($payment === "QR") {
            echo '<div class="container mt-5">
                <div class="card shadow-lg p-4 text-center d-flex align-items-center justify-content-center" style="max-width: 600px; margin: auto;">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 3rem;"></i>
                    <h3 class="text-success mb-3 mt-2">Đặt hàng thành công!</h3>
                    <p>Cảm ơn bạn đã mua sắm tại cửa hàng.</p>
                    <p>Mã đơn hàng của bạn là: <strong>MDH' . $order_id . '</strong></p>
                    <p class="fw-bold">Lưu ý vui lòng quay video khi mở hàng!</p>
                    <p>Vui lòng quét mã QR bên dưới để chuyển khoản:</p>
                    
                    <div class="d-flex justify-content-center">
                        <img src="https://img.vietqr.io/image/970415-103875747430-compact2.png?amount=' . $total_price . '&addInfo=MDH' . $order_id . '" 
                            alt="QR thanh toán" class="img-fluid my-3" style="max-width:300px;">
                    </div>

                    <p><strong>Ngân hàng:</strong> VietinBank<br>
                    <strong>Số tài khoản:</strong> 103875747430<br>
                    <strong>Nội dung:</strong> MDH' . $order_id . '<br>
                    <strong>Số tiền:</strong> ' . number_format($total_price, 0, ",", ".") . '₫</p>
                    <a href="index.php" class="btn btn-primary mt-3"><i class="fa-solid fa-house"></i> Quay lại trang chủ</a>
                </div>
            </div>';
            exit;
        } else if ($payment === "COD") {
            echo '<div class="container mt-5">
        <div class="card shadow-lg p-4 text-center d-flex align-items-center justify-content-center" style="max-width: 600px; margin: auto;">
            <i class="fa-solid fa-circle-check text-success" style="font-size: 3rem;"></i>
            <h3 class="text-success mb-3 mt-2">Đặt hàng thành công!</h3>
            <p>Cảm ơn bạn đã mua sắm tại cửa hàng.</p>
            <p>Mã đơn hàng của bạn là: <strong>MDH#' . $order_id . '</strong></p>
            <p class="fw-bold">Lưu ý vui lòng quay video khi mở hàng!</p>
            <a href="index.php" class="btn btn-primary mt-3"><i class="fa-solid fa-house"></i> Quay lại trang chủ</a>
        </div>
      </div>';
            exit;
        } else {
            die("Lỗi khi đặt hàng: " . $conn->error);
        }
    }
}

?>
<?php
// Lấy danh sách địa chỉ kèm fullname từ users
$sql_contacts = "
    SELECT uc.*, u.fullname 
    FROM user_contacts uc
    JOIN users u ON uc.user_id = u.id
    WHERE uc.user_id = $user_id
    ORDER BY uc.is_default DESC, uc.id DESC
";
$contacts = $conn->query($sql_contacts);
?>


<body class="bg-light">
    <div class="container mt-4">
        <h3>Xác nhận đơn hàng</h3>

        <form method="POST" action="checkout.php">
            <?php if (isset($_POST['buy_now'])): ?>
                <input type="hidden" name="buy_now" value="1">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($_POST['product_id']) ?>">
                <input type="hidden" name="quantity" value="<?= htmlspecialchars($_POST['quantity']) ?>">
            <?php endif; ?>

            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Ảnh</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $subtotal = $item['price'] * $item['quantity'];
                        $img = explode(",", $item['image'])[2] ?? "no-image.png";
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><img src="public/uploads/<?= htmlspecialchars($img) ?>" width="80"></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                        <td class="fw-bold text-danger"><?= number_format($total_price, 0, ',', '.') ?>₫</td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Địa chỉ nhận hàng:</label>
                <?php if ($contacts->num_rows > 0): ?>
                    <select id="addressSelect" class="form-select w-75" required>
                        <?php while ($c = $contacts->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($c['address']) ?>"
                                data-phone="<?= htmlspecialchars($c['phone']) ?>"
                                data-fullname="<?= htmlspecialchars($c['fullname']) ?>" <?= $c['is_default'] ? 'selected' : '' ?>><?= htmlspecialchars($c['fullname']) ?> | <?= htmlspecialchars($c['phone']) ?> -
                                <?= htmlspecialchars($c['address']) ?>
                                <?= $c['is_default'] ? '(Mặc định)' : '' ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <!-- Hiển thị thông tin địa chỉ -->
                    <div id="addressInfo" class="mt-3 p-3 border rounded bg-secondary bg-opacity-25 w-75">
                        <p><strong>Họ tên:</strong> <span id="showFullname"></span></p>
                        <p><strong>Số điện thoại:</strong> <span id="showPhone"></span></p>
                        <p><strong>Địa chỉ:</strong> <span id="showAddress"></span></p>
                    </div>

                    <!-- Hidden inputs để submit -->
                    <input type="hidden" name="fullname" id="fullnameInput">
                    <input type="hidden" name="phone" id="phoneInput">
                    <input type="hidden" name="address" id="addressInput">

                <?php else: ?>
                    <div class="alert alert-warning">
                        Bạn chưa có địa chỉ nào. Vui lòng thêm trong <a href="profile.php">Hồ sơ</a>.
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Phương thức thanh toán:</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="COD"
                        checked>
                    <label class="form-check-label" for="payment_cod">
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="payment_qr" value="QR">
                    <label class="form-check-label" for="payment_qr">
                        Thanh toán Online (QR)
                    </label>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="cart.php" class="btn btn-secondary">Quay lại giỏ hàng</a>
                <button type="submit" name="checkout_submit" class="btn btn-success">Xác nhận đặt hàng</button>
            </div>
        </form>
    </div>
</body>

<?php include "includes/footer.php"; ?>

<script>
    const addressSelect = document.getElementById("addressSelect");
    const fullnameInput = document.getElementById("fullnameInput");
    const phoneInput = document.getElementById("phoneInput");
    const addressInput = document.getElementById("addressInput");

    // Thêm các phần hiển thị thông tin
    const showFullname = document.getElementById("showFullname");
    const showPhone = document.getElementById("showPhone");
    const showAddress = document.getElementById("showAddress");

    function updateContact() {
        let selected = addressSelect?.selectedOptions[0];
        if (selected) {
            const fullname = selected.dataset.fullname || "";
            const phone = selected.dataset.phone || "";
            const address = selected.value || "";

            // Cập nhật hidden input
            fullnameInput.value = fullname;
            phoneInput.value = phone;
            addressInput.value = address;

            // Cập nhật phần hiển thị ra giao diện
            showFullname.textContent = fullname;
            showPhone.textContent = phone;
            showAddress.textContent = address;
        }
    }

    if (addressSelect) {
        addressSelect.addEventListener("change", updateContact);
        updateContact(); // hiển thị địa chỉ mặc định khi load trang
    }
</script>