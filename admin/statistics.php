<?php
session_start();
require "../config/db.php";

// CẤU HÌNH NĂM THỐNG KÊ
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// LẤY SỐ LƯỢNG BÁN THEO THÁNG
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

// TỔNG DOANH THU CẢ NĂM
$sql_total = "
    SELECT SUM(od.quantity * od.price) AS total
    FROM order_details od
    INNER JOIN orders o ON od.order_id = o.id
    WHERE YEAR(o.created_at) = $year AND o.status = 'Completed'
";
$total = $conn->query($sql_total)->fetch_assoc()['total'] ?? 0;

// LỢI NHUẬN THEO THÁNG & TỔNG NĂM
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

// CHI TIẾT SẢN PHẨM THEO THÁNG
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
                    <button class="btn btn-sm btn-primary">Xem</button>
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
                                    <tbody id="accordionMonths">
                                        <?php foreach (range(1, 12) as $m): ?>
                                            <tr>
                                                <td><strong><?= $m ?>/<?= $year ?></strong></td>
                                                <td><?= $monthlySold[$m] ?></td>
                                                <td class="<?= $monthlyProfit[$m] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= number_format($monthlyProfit[$m], 0, ',', '.') ?> ₫
                                                </td>
                                                <td>
                                                    <?php if (!empty($detailsByMonth[$m])): ?>
                                                        <button class="btn btn-sm btn-info fw-bold"
                                                            data-target="#month<?= $m ?>">
                                                            Xem
                                                        </button>
                                                    <?php else: ?> -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <?php if (!empty($detailsByMonth[$m])): ?>
                                                <tr>
                                                    <td colspan="4" class="p-0">
                                                        <div id="month<?= $m ?>" class="accordion-collapse collapse">
                                                            <div class="card card-body">
                                                                <table class="table table-sm table-bordered text-center mb-0">
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
                                                                                <td><?= htmlspecialchars($row['product_name']) ?>
                                                                                </td>
                                                                                <td><?= htmlspecialchars($row['category_name']) ?>
                                                                                </td>
                                                                                <td><?= htmlspecialchars($row['brand_name']) ?></td>
                                                                                <td><?= $row['total_sold'] ?></td>
                                                                                <td
                                                                                    class="<?= $row['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                                    <?= number_format($row['profit'], 0, ',', '.') ?>
                                                                                    ₫
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
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
                labels: Array.from({ length: 12 }, (_, i) => `Tháng ${i + 1}`),
                datasets: [{
                    label: 'Sản phẩm bán ra',
                    data: <?= json_encode(array_values($monthlySold)) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#66BB6A', '#BA68C8',
                        '#26C6DA', '#EF5350', '#D4E157', '#8D6E63'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            color: '#333',
                            font: { size: 13 }
                        }
                    }
                }
            }
        });

        const ctx = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
                datasets: [{
                    label: 'Lợi nhuận (₫)',
                    data: <?= json_encode(array_values($monthlyProfit)) ?>,
                    borderColor: 'rgb(40,167,69)',
                    tension: 0.4,
                    fill: false
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
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.btn[data-target]').forEach(button => {
                button.addEventListener('click', function () {
                    const target = document.querySelector(this.dataset.target);
                    const collapseInstance = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });

                    if (target.classList.contains('show')) {
                        collapseInstance.hide(); // Nếu đang mở thì đóng lại
                    } else {
                        document.querySelectorAll('.accordion-collapse.show').forEach(openItem => {
                            bootstrap.Collapse.getOrCreateInstance(openItem, { toggle: false }).hide();
                        });
                        collapseInstance.show(); // Mở mục hiện tại
                    }
                });
            });
        });
    </script>
</body>

</html>