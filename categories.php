<?php
require "config/db.php";

// Lấy danh mục
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $conn->query($sql_categories);
?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarCategories" role="button" data-bs-toggle="dropdown"
        aria-expanded="false">
        Danh mục
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarCategories">
        <li>
            <a class="dropdown-item" href="categories_list.php">Tất cả sản phẩm</a>
        </li>
        <?php if ($result_categories->num_rows > 0): ?>
            <?php while ($cat = $result_categories->fetch_assoc()): ?>
                <li>
                    <a class="dropdown-item" href="categories_list.php?id=<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li><span class="dropdown-item text-muted">Chưa có danh mục</span></li>
        <?php endif; ?>
    </ul>
</li>
