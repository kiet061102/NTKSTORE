<?php
session_start();
require "../config/db.php";

// Lấy năm từ request hoặc mặc định năm hiện tại
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// Truy vấn tổng số sản phẩm đã bán theo từng tháng trong năm
$sql = "
    SELECT MONTH(o.created_at) as month, SUM(od.quantity) as total_sold
    FROM order_details od
    INNER JOIN orders o ON od.order_id = o.id
    WHERE YEAR(o.created_at) = $year AND o.status = 'Completed'
    GROUP BY MONTH(o.created_at)
    ORDER BY MONTH(o.created_at)
";
$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}

$monthlyData = array_fill(1, 12, 0);
while ($row = $result->fetch_assoc()) {
    $monthlyData[intval($row['month'])] = intval($row['total_sold']);
}

// Chuẩn bị chi tiết theo từng tháng
$detailsByMonth = [];
for ($m = 1; $m <= 12; $m++) {
    if ($monthlyData[$m] > 0) {
        $sql_details = "
            SELECT p.name AS product_name, c.name AS category_name, b.name AS brand_name, SUM(od.quantity) AS total_sold
            FROM order_details od
            INNER JOIN orders o ON od.order_id = o.id
            INNER JOIN products p ON od.product_id = p.id
            INNER JOIN categories c ON p.category_id = c.id
            INNER JOIN brands b ON p.brand_id = b.id
            WHERE YEAR(o.created_at) = $year 
              AND MONTH(o.created_at) = $m
              AND o.status = 'Completed'
            GROUP BY p.id, p.name, c.name, b.name
            ORDER BY total_sold DESC
        ";
        $res_details = $conn->query($sql_details);
        if ($res_details) {
            while ($r = $res_details->fetch_assoc()) {
                $detailsByMonth[$m][] = $r;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thống kê bán hàng - Admin</title>
</head>

<body class="bg-light">
    <?php include "../admin/index.php" ?>
    <div class="container py-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                <a href="../admin/index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <span>Thống kê bán hàng năm <?= $year ?></span>
                <form method="GET" class="d-flex" style="gap:10px;">
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
                    <!-- Biểu đồ -->
                    <div class="col-md-6 mb-3">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>

                    <!-- Bảng -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-1 h-100">
                            <div class="card-header bg-dark text-white fw-bold">
                                Bảng số lượng sản phẩm đã bán theo tháng
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered text-center align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Tháng</th>
                                            <th>Số sản phẩm</th>
                                            <th>Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <tr>
                                                <td><?= $m ?>/<?= $year ?></td>
                                                <td><?= $monthlyData[$m] ?></td>
                                                <td>
                                                    <?php if ($monthlyData[$m] > 0): ?>
                                                        <button class="btn btn-sm btn-info fw-bold" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseMonth<?= $m ?>">
                                                            Xem
                                                        </button>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if (!empty($detailsByMonth[$m])): ?>
                                                <tr class="collapse" id="collapseMonth<?= $m ?>">
                                                    <td colspan="3">
                                                        <div class="card card-body">
                                                            <table class="table table-sm table-bordered text-center">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th>Sản phẩm</th>
                                                                        <th>Loại</th>
                                                                        <th>Hãng</th>
                                                                        <th>Số lượng bán</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($detailsByMonth[$m] as $row): ?>
                                                                        <tr>
                                                                            <td><?= $row['product_name'] ?></td>
                                                                            <td><?= $row['category_name'] ?></td>
                                                                            <td><?= $row['brand_name'] ?></td>
                                                                            <td><?= $row['total_sold'] ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- end col-md-6 -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [
                'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
                'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
                'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
            ],
            datasets: [{
                label: 'Sản phẩm đã bán',
                data: <?= json_encode(array_values($monthlyData)) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)',
                    'rgba(255, 99, 71, 0.7)',
                    'rgba(60, 179, 113, 0.7)',
                    'rgba(123, 104, 238, 0.7)',
                    'rgba(255, 215, 0, 0.7)',
                    'rgba(106, 90, 205, 0.7)'
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
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let value = context.raw;
                            return context.label + ': ' + value + ' sản phẩm';
                        }
                    }
                }
            }
        }
    });
</script>