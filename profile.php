<?php
session_start();
require "config/db.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// XỬ LÝ ĐỊA CHỈ
$action = $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default) {
        $conn->query("UPDATE user_contacts SET is_default = 0 WHERE user_id = $user_id");
    }

    $sql = "INSERT INTO user_contacts (user_id, phone, address, is_default) 
            VALUES ($user_id, '$phone', '$address', $is_default)";
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Thêm địa chỉ thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi thêm địa chỉ.";
    }
    header("Location: profile.php");
    exit;
}

if ($action === 'set_default' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $check = $conn->query("SELECT id FROM user_contacts WHERE id = $id AND user_id = $user_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE user_contacts SET default = 0 WHERE user_id = $user_id");
        $conn->query("UPDATE user_contacts SET default = 1 WHERE id = $id");
        $_SESSION['success'] = "Đã chọn địa chỉ mặc định.";
    } else {
        $_SESSION['error'] = "Không tìm thấy địa chỉ.";
    }
    header("Location: profile.php");
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $check = $conn->query("SELECT id FROM user_contacts WHERE id = $id AND user_id = $user_id");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM user_contacts WHERE id = $id");
        $_SESSION['success'] = "Xóa địa chỉ thành công.";
    } else {
        $_SESSION['error'] = "Không tìm thấy địa chỉ.";
    }
    header("Location: profile.php");
    exit;
}

// CẬP NHẬT THÔNG TIN NGƯỜI DÙNG
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($action)) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $fullname = $conn->real_escape_string(trim($_POST['fullname']));
    $email = $conn->real_escape_string(trim($_POST['email']));

    if (empty($username) || empty($email)) {
        $_SESSION['error'] = "Tên đăng nhập và Email không được để trống!";
    } else {
        $sql = "UPDATE users 
                SET username='$username', fullname='$fullname', email='$email'
                WHERE id=$user_id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Cập nhật thông tin thành công!";
            $_SESSION['fullname'] = $fullname;
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra, vui lòng thử lại.";
        }
    }
    header("Location: profile.php");
    exit;
}

// LẤY THÔNG TIN NGƯỜI DÙNG
$sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Không tìm thấy thông tin người dùng.");
}
$users = $result->fetch_assoc();

// Lấy danh sách địa chỉ
$contacts = $conn->query("SELECT * FROM user_contacts WHERE user_id = $user_id ORDER BY is_default DESC, id DESC");
include "includes/header.php";
include "includes/navbar.php";
?>

<!-- Hiển thị thông báo -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="text-center p-3">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center"
                        style="width:120px; height:120px;">
                        <i class="fa-solid fa-user" style="font-size:60px; color:#6c757d;"></i>
                    </div>
                    <h5 class="mt-2"><?= htmlspecialchars($users['fullname']) ?></h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fa-solid fa-user"></i> Hồ sơ
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
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

        <!-- Thông tin người dùng -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Thông tin cá nhân
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Tên đăng nhập</label>
                            <div class="col-sm-9">
                                <input type="text" name="username" class="form-control"
                                    value="<?= htmlspecialchars($users['username']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Tên hiển thị</label>
                            <div class="col-sm-9">
                                <input type="text" name="fullname" class="form-control"
                                    value="<?= htmlspecialchars($users['fullname']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($users['email']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Ngày tham gia</label>
                            <div class="col-sm-9 pt-2">
                                <span class="text-muted"><?= date("d/m/Y", strtotime($users['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Lưu
                                thay đổi</button>
                        </div>
                    </form>

                    <hr>

                    <!-- form địa chỉ -->
                    <h5>Địa chỉ giao hàng (Trước sáp nhập)</h5>
                    <?php if ($contacts->num_rows > 0): ?>
                        <ul class="list-group mb-3">
                            <?php while ($c = $contacts->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($c['phone']) ?></strong><br>
                                        <?= htmlspecialchars($c['address']) ?>
                                        <?php if ($c['is_default']): ?>
                                            <span class="badge bg-success">Mặc định</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if (!$c['is_default']): ?>
                                            <a href="profile.php?action=set_default&id=<?= $c['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                Chọn mặc định
                                            </a>
                                        <?php endif; ?>
                                        <a href="profile.php?action=delete&id=<?= $c['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Xóa địa chỉ này?')">Xóa</a>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Bạn chưa có địa chỉ nào.</p>
                    <?php endif; ?>

                    <h6>Thêm địa chỉ mới</h6>
                    <form method="POST" action="profile.php?action=add">
                        <div class="mb-2">
                            <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select id="province" class="form-select">
                                    <option value="">-- Tỉnh/Thành phố --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="district" class="form-select">
                                    <option value="">-- Quận/Huyện --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="ward" class="form-select">
                                    <option value="">-- Phường/Xã --</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="text" id="street" class="form-control" placeholder="Số nhà, tên đường">
                        </div>
                        <input type="hidden" name="address" id="full_address">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default">
                            <label class="form-check-label" for="is_default">Đặt làm mặc định</label>
                        </div>
                        <button type="submit" class="btn btn-success mt-2">+ Thêm địa chỉ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
    async function loadProvinces() {
        let res = await fetch("https://provinces.open-api.vn/api/v1/?depth=1");
        let data = await res.json();
        let provinceSelect = document.getElementById("province");
        data.forEach(p => {
            let opt = document.createElement("option");
            opt.value = p.code;
            opt.text = p.name;
            provinceSelect.add(opt);
        });
    }

    document.getElementById("province").addEventListener("change", async function () {
        let code = this.value;
        let res = await fetch(`https://provinces.open-api.vn/api/v1/p/${code}?depth=2`);
        let data = await res.json();
        let districtSelect = document.getElementById("district");
        districtSelect.innerHTML = "<option value=''>-- Quận/Huyện --</option>";
        data.districts.forEach(d => {
            let opt = document.createElement("option");
            opt.value = d.code;
            opt.text = d.name;
            districtSelect.add(opt);
        });
    });

    document.getElementById("district").addEventListener("change", async function () {
        let code = this.value;
        let res = await fetch(`https://provinces.open-api.vn/api/v1/d/${code}?depth=2`);
        let data = await res.json();
        let wardSelect = document.getElementById("ward");
        wardSelect.innerHTML = "<option value=''>-- Phường/Xã --</option>";
        data.wards.forEach(w => {
            let opt = document.createElement("option");
            opt.value = w.code;
            opt.text = w.name;
            wardSelect.add(opt);
        });
    });

    document.querySelector("form[action='profile.php?action=add']").addEventListener("submit", function (e) {
        let province = document.getElementById("province").selectedOptions[0]?.text || "";
        let district = document.getElementById("district").selectedOptions[0]?.text || "";
        let ward = document.getElementById("ward").selectedOptions[0]?.text || "";
        let street = document.getElementById("street").value || "";
        document.getElementById("full_address").value = `${street}, ${ward}, ${district}, ${province}`;
    });

    loadProvinces();
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
</style>