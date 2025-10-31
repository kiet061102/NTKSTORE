<?php
session_start();
include "includes/header.php";
require "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Lấy thông tin user
$sql_user = "SELECT fullname FROM users WHERE id = $user_id LIMIT 1";
$result_user = $conn->query($sql_user);
$users = $result_user->fetch_assoc();

// Lấy tất cả đơn hàng của user
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$result_orders = $conn->query($sql);

// Xử lý hủy đơn hàng
if (isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = intval($_SESSION['user_id']);

    // Chỉ hủy nếu đơn thuộc user và còn Pending
    $check_sql = "SELECT id FROM orders WHERE id=$order_id AND user_id=$user_id AND status='Pending'";
    $check = $conn->query($check_sql);

    if ($check && $check->num_rows > 0) {
        $conn->query("UPDATE orders SET status='Cancelled' WHERE id=$order_id");
    }

    header("Location: orders.php");
    exit;
}


include "includes/header.php";
include "includes/navbar.php";
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="col-md-12 text-center py-3">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center"
                        style="width:120px; height:120px;">
                        <i class="fa-solid fa-user" style="font-size:60px; color:#6c757d;"></i>
                    </div>
                    <h5 class="mt-2"><?= htmlspecialchars($users['fullname']) ?></h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-user"></i> Hồ sơ
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action active">
                        <i class="fa-solid fa-box"></i> Đơn hàng
                    </a>
                    <a href="changepassword.php" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-lock"></i> Đổi mật khẩu
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs">
                        <li class="nav-item">
                            <button class="nav-link active" id="orders-tab" data-bs-toggle="tab"
                                data-bs-target="#ordersTab" type="button">
                                <i class="fa-solid fa-box"></i> Đơn hàng của tôi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviewsTab"
                                type="button">
                                <i class="fa-solid fa-star"></i> Các đánh giá
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body tab-content">
                    <!-- TAB ĐƠN HÀNG -->
                    <div class="tab-pane fade show active" id="ordersTab">
                        <?php if ($result_orders->num_rows > 0): ?>
                            <div class="accordion" id="ordersAccordion">
                                <?php while ($order = $result_orders->fetch_assoc()): ?>
                                    <div class="accordion-item mb-2">
                                        <h2 class="accordion-header d-flex justify-content-between align-items-center"
                                            id="heading<?= $order['id'] ?>">
                                            <button class="accordion-button collapsed flex-grow-1 me-3" type="button"
                                                data-target="#collapse<?= $order['id'] ?>">
                                                <span>Mã đơn: MDH<?= $order['id'] ?> -
                                                    <?= date("d/m/Y H:i", strtotime($order['created_at'])) ?></span>
                                            </button>

                                            <div class="d-flex justify-content-center align-items-center text-center gap-2">
                                                <?php if ($order['status'] == 'Pending'): ?>
                                                    <span class="badge bg-warning text-dark small-badge">Đang chờ xác nhận</span>
                                                <?php elseif ($order['status'] == 'Confirmed'): ?>
                                                    <span class="badge bg-info small-badge">Đã xác nhận</span>
                                                <?php elseif ($order['status'] == 'Shipped'): ?>
                                                    <span class="badge bg-primary small-badge">Đang giao hàng</span>
                                                <?php elseif ($order['status'] == 'Completed'): ?>
                                                    <span class="badge bg-success small-badge">Hoàn thành</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger small-badge"><?= $order['status'] ?></span>
                                                <?php endif; ?>

                                                <?php if ($order['status'] == 'Pending'): ?>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                        <button type="submit" name="cancel_order"
                                                            class="btn btn-danger btn-sm small-btn">
                                                            <i class="fa-solid fa-xmark"></i> Hủy
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </h2>

                                        <div id="collapse<?= $order['id'] ?>" class="accordion-collapse collapse"
                                            aria-labelledby="heading<?= $order['id'] ?>" data-bs-parent="#ordersAccordion">
                                            <div class="accordion-body">
                                                <?php
                                                $order_id = $order['id'];
                                                $sql_details = "SELECT od.*, p.name AS product_name, p.image 
                                                        FROM order_details od
                                                        JOIN products p ON od.product_id = p.id
                                                        WHERE od.order_id = $order_id";
                                                $result_details = $conn->query($sql_details);
                                                ?>
                                                <?php if ($result_details->num_rows > 0): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered text-center align-middle">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Ảnh</th>
                                                                    <th>Sản phẩm</th>
                                                                    <th>Số lượng</th>
                                                                    <th>Giá</th>
                                                                    <th>Thành tiền</th>
                                                                    <th>Đánh giá</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $total = 0;
                                                                while ($item = $result_details->fetch_assoc()):
                                                                    $subtotal = $item['quantity'] * $item['price'];
                                                                    $total += $subtotal;
                                                                    $images = explode(',', $item['image']);
                                                                    $thirdImage = $images[2] ?? 'no-image.png';
                                                                    $product_id = intval($item['product_id']);
                                                                    $order_id = intval($order['id']);
                                                                    $check_review = $conn->query("SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id AND order_id = $order_id LIMIT 1");
                                                                    $has_review = $check_review && $check_review->num_rows > 0;
                                                                    ?>
                                                                    <tr>
                                                                        <td><img src="public/uploads/<?= htmlspecialchars($thirdImage) ?>"
                                                                                width="100"></td>
                                                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                                        <td><?= $item['quantity'] ?></td>
                                                                        <td><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                                                                        <td><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                                                                        <td>
                                                                            <?php if ($order['status'] == 'Completed'): ?>
                                                                                <?php if (!$has_review): ?>
                                                                                    <button class="btn btn-sm btn-outline-warning btn-review"
                                                                                        data-product-id="<?= $item['product_id'] ?>"
                                                                                        data-order-id="<?= $order['id'] ?>"
                                                                                        data-product-name="<?= htmlspecialchars($item['product_name']) ?>">
                                                                                        Đánh giá
                                                                                    </button>
                                                                                <?php else: ?>
                                                                                    <span class="text-success fw-semibold">
                                                                                        <i class="fa-solid fa-check-circle"></i>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            <?php else: ?>
                                                                                <span class="text-muted small">—</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-end fw-bold">Tổng cộng</td>
                                                                    <td class="fw-bold text-danger">
                                                                        <?= number_format($total, 0, ',', '.') ?>₫
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">Đơn hàng này chưa có sản phẩm nào.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                        <?php endif; ?>
                    </div>

                    <!-- TAB CÁC ĐÁNH GIÁ -->
                    <div class="tab-pane fade" id="reviewsTab">
                        <?php
                        $sql_reviews = "SELECT r.*, p.name AS product_name, p.image 
                                FROM reviews r
                                JOIN products p ON r.product_id = p.id
                                WHERE r.user_id = $user_id
                                ORDER BY r.created_at DESC";
                        $result_reviews = $conn->query($sql_reviews);
                        ?>
                        <?php if ($result_reviews && $result_reviews->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($rev = $result_reviews->fetch_assoc()):
                                    $images = explode(',', $rev['image']);
                                    $img = $images[2] ?? 'no-image.png';
                                    ?>
                                    <div class="list-group-item d-flex gap-3">
                                        <img src="public/uploads/<?= htmlspecialchars($img) ?>" width="120" class="rounded">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($rev['product_name']) ?></h6>
                                            <div class="text-warning mb-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fa<?= $i <= $rev['rating'] ? '-solid' : '-regular' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="mb-1"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                            <small
                                                class="text-muted"><?= date("d/m/Y H:i", strtotime($rev['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Bạn chưa có đánh giá nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal đánh giá -->
        <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="reviewForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reviewModalLabel">Đánh giá sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Tên sản phẩm: <strong id="productName"></strong></p>
                            <input type="hidden" name="product_id" id="reviewProductId">
                            <input type="hidden" name="order_id" id="reviewOrderId">

                            <div class="mb-3 text-center">
                                <div id="ratingStars" class="fs-3 text-warning">
                                    <i class="fa-regular fa-star" data-value="1"></i>
                                    <i class="fa-regular fa-star" data-value="2"></i>
                                    <i class="fa-regular fa-star" data-value="3"></i>
                                    <i class="fa-regular fa-star" data-value="4"></i>
                                    <i class="fa-regular fa-star" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" value="0">
                            </div>

                            <div class="mb-3">
                                <label for="reviewComment" class="form-label">Nội dung đánh giá</label>
                                <textarea name="comment" id="reviewComment" class="form-control" rows="3"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success">Gửi đánh giá</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include "includes/footer.php"; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
            </script>
        <style>
            .list-group-item {
                border: none;
                padding: 12px 20px;
                font-size: 15px;
            }

            .list-group-item.active {
                background-color: #ff5722;
                color: #fff;
                font-weight: bold;
            }

            .list-group-item:hover {
                background-color: #ffe0d6;
                color: #ff5722;
            }

            .small-badge {
                font-size: 12px;
                padding: 3px 6px;
            }

            .small-btn {
                font-size: 12px;
                padding: 2px 8px;
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll('.accordion-button').forEach(function (button) {
                    button.addEventListener('click', function () {
                        let target = document.querySelector(this.getAttribute('data-target'));
                        let collapseInstance = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });

                        if (target.classList.contains('show')) {
                            collapseInstance.hide();
                            this.classList.remove('bg-info', 'text-dark', 'fw-bold');
                        } else {
                            document.querySelectorAll('.accordion-button').forEach(btn => {
                                btn.classList.remove('bg-info', 'text-dark', 'fw-bold');
                            });
                            collapseInstance.show();
                            this.classList.add('bg-info', 'text-dark', 'fw-bold');
                        }
                    });
                });
            });


            document.addEventListener("DOMContentLoaded", function () {
                const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
                const form = document.getElementById('reviewForm');
                const productNameEl = document.getElementById('productName');
                const ratingStars = document.querySelectorAll('#ratingStars i');
                const ratingValue = document.getElementById('ratingValue');

                // Khi bấm nút "Đánh giá"
                document.querySelectorAll('.btn-review').forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.getElementById('reviewProductId').value = this.dataset.productId;
                        document.getElementById('reviewOrderId').value = this.dataset.orderId;
                        productNameEl.textContent = this.dataset.productName;
                        form.reset();
                        ratingValue.value = 0;
                        ratingStars.forEach(star => star.classList.replace('fa-solid', 'fa-regular'));
                        reviewModal.show();
                    });
                });

                // Chọn sao
                ratingStars.forEach(star => {
                    star.addEventListener('click', function () {
                        let val = parseInt(this.dataset.value);
                        ratingValue.value = val;
                        ratingStars.forEach(s => {
                            s.classList.toggle('fa-solid', parseInt(s.dataset.value) <= val);
                            s.classList.toggle('fa-regular', parseInt(s.dataset.value) > val);
                        });
                    });
                });

                // Gửi form AJAX
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    let res = await fetch('review.php', { method: 'POST', body: formData });
                    let result = await res.text();
                    alert(result);
                    reviewModal.hide();
                    location.reload(); // tải lại để cập nhật icon đã đánh giá
                });
            });

        </script>