<?php
session_start();
require "../config/db.php";

// ========================
// CẤU HÌNH NĂM THỐNG KÊ
// ========================
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// ========================
// 1️⃣ LẤY SỐ LƯỢNG BÁN THEO THÁNG
// ========================
$sql_sold = "
    SELECT MONTH(o.created_at) AS month, SUM(od.quantity) AS total_sold
    FROM order_details od
    INNER JOIN orders o ON od.order_id = o.id
    WHERE YEAR(o.created_at) = $year AND o.status = 'Completed'
    GROUP BY MONTH(o.created_at)
";
$result = $conn->query($sql_sold);

$monthlySold = array_fill(1, 12, 0);
while ($row = $result->fetch_assoc()) {
    $monthlySold[intval($row['month'])] = intval($row['total_sold']);
}

// ========================
// 2️⃣ TỔNG DOANH THU CẢ NĂM
// ========================
$sql_total = "
    SELECT SUM(od.quantity * od.price) AS total
    FROM order_details od
    INNER JOIN orders o ON od.order_id = o.id
    WHERE YEAR(o.created_at) = $year AND o.status = 'Completed'
";
$total = $conn->query($sql_total)->fetch_assoc()['total'] ?? 0;

// ========================
// 3️⃣ LỢI NHUẬN THEO THÁNG & TỔNG NĂM
// ========================
$sql_profit = "
    SELECT 
        MONTH(o.created_at) AS month,
        SUM((od.price - i.import_price) * od.quantity) AS profit
    FROM order_details od
    INNER JOIN orders o ON od.order_id = o.id
    INNER JOIN products p ON od.product_id = p.id
    LEFT JOIN (
        SELECT product_id, import_price
        FROM imports
        WHERE id IN (SELECT MAX(id) FROM imports GROUP BY product_id)
    ) i ON i.product_id = p.id
    WHERE YEAR(o.created_at) = $year AND o.status = 'Completed'
    GROUP BY MONTH(o.created_at)
";
$res_profit = $conn->query($sql_profit);

$monthlyProfit = array_fill(1, 12, 0);
$totalProfit = 0;
while ($row = $res_profit->fetch_assoc()) {
    $m = intval($row['month']);
    $monthlyProfit[$m] = floatval($row['profit']);
    $totalProfit += floatval($row['profit']);
}

// ========================
// 4️⃣ CHI TIẾT SẢN PHẨM THEO THÁNG
// ========================
$detailsByMonth = [];
foreach (range(1, 12) as $m) {
    if ($monthlySold[$m] == 0)
        continue;

    $sql_details = "
        SELECT p.name AS product_name, c.name AS category_name, b.name AS brand_name, 
               SUM(od.quantity) AS total_sold,
               SUM((od.price - i.import_price) * od.quantity) AS profit
        FROM order_details od
        INNER JOIN orders o ON od.order_id = o.id
        INNER JOIN products p ON od.product_id = p.id
        INNER JOIN categories c ON p.category_id = c.id
        INNER JOIN brands b ON p.brand_id = b.id
        LEFT JOIN (
            SELECT product_id, import_price
            FROM imports
            WHERE id IN (SELECT MAX(id) FROM imports GROUP BY product_id)
        ) i ON i.product_id = p.id
        WHERE YEAR(o.created_at) = $year AND MONTH(o.created_at) = $m AND o.status = 'Completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
    ";
    $res_details = $conn->query($sql_details);
    if ($res_details) {
        $detailsByMonth[$m] = $res_details->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thống kê bán hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">
    <?php include "../admin/index.php"; ?>

    <div class="container py-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i></a>
                <span>Thống kê bán hàng năm <?= $year ?></span>
                <form method="GET" class="d-flex gap-2">
                    <select name="year" class="form-select">
                        <?php for ($y = date("Y"); $y >= date("Y") - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Xem</button>
                </form>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Biểu đồ bán ra -->
                    <div class="col-md-6 mb-3">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                    <!-- Biểu đồ lợi nhuận -->
                    <div class="col-md-6 mb-3">
                        <canvas id="profitChart" height="300"></canvas>
                    </div>

                    <!-- Bảng dữ liệu -->
                    <div class="col-12 mt-4">
                        <div class="card border-1">
                            <div class="card-header bg-dark text-white fw-bold">
                                Bảng số lượng & lợi nhuận theo tháng
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered text-center align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Tháng</th>
                                            <th>Sản phẩm đã bán</th>
                                            <th>Lợi nhuận</th>
                                            <th>Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (range(1, 12) as $m): ?>
                                            <tr>
                                                <td><strong><?= $m ?>/<?= $year ?></strong></td>
                                                <td><?= $monthlySold[$m] ?></td>
                                                <td class="<?= $monthlyProfit[$m] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= number_format($monthlyProfit[$m], 0, ',', '.') ?> ₫
                                                </td>
                                                <td>
                                                    <?php if (!empty($detailsByMonth[$m])): ?>
                                                        <button class="btn btn-sm btn-info fw-bold toggle-btn"
                                                            data-target="#month<?= $m ?>">Xem</button>
                                                    <?php else: ?> -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <?php if (!empty($detailsByMonth[$m])): ?>
                                                <tr class="collapse" id="month<?= $m ?>">
                                                    <td colspan="4">
                                                        <div class="card card-body">
                                                            <table class="table table-sm table-bordered text-center">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th>Sản phẩm</th>
                                                                        <th>Loại</th>
                                                                        <th>Hãng</th>
                                                                        <th>Số lượng bán</th>
                                                                        <th>Lợi nhuận</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($detailsByMonth[$m] as $row): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                                                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                                                                            <td><?= htmlspecialchars($row['brand_name']) ?></td>
                                                                            <td><?= $row['total_sold'] ?></td>
                                                                            <td
                                                                                class="<?= $row['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                                <?= number_format($row['profit'], 0, ',', '.') ?> ₫
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>

                                        <tr class="table-success fw-bold">
                                            <td colspan="2">Tổng doanh thu</td>
                                            <td colspan="2"><?= number_format($total, 0, ',', '.') ?> ₫</td>
                                        </tr>
                                        <tr class="table-warning fw-bold">
                                            <td colspan="2">Tổng lợi nhuận</td>
                                            <td colspan="2"><?= number_format($totalProfit, 0, ',', '.') ?> ₫</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ChartJS -->
    <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'pie',
            data: {
                labels: Array.from({ length: 12 }, (_, i) => `Th${i + 1}`),
                datasets: [{
                    label: 'Sản phẩm bán ra',
                    data: <?= json_encode(array_values($monthlySold)) ?>,
                    backgroundColor: Array.from({ length: 12 }, () =>
                        `hsl(${Math.random() * 360}, 70%, 70%)`)
                }]
            },
            options: { responsive: true }
        });

        const profitCtx = document.getElementById('profitChart').getContext('2d');
        new Chart(profitCtx, {
            type: 'bar',
            data: {
                labels: Array.from({ length: 12 }, (_, i) => `Th${i + 1}`),
                datasets: [{
                    label: 'Lợi nhuận (₫)',
                    data: <?= json_encode(array_values($monthlyProfit)) ?>,
                    backgroundColor: 'rgba(40,167,69,0.6)',
                    borderColor: 'rgba(40,167,69,1)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => v.toLocaleString() + ' ₫' }
                    }
                }
            }
        });
    </script>

    <!-- Toggle xem/ẩn -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".toggle-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    const target = document.querySelector(btn.dataset.target);
                    const collapse = bootstrap.Collapse.getOrCreateInstance(target);
                    target.classList.contains("show") ? collapse.hide() : collapse.show();
                });
            });
        });
    </script>
</body>

</html>