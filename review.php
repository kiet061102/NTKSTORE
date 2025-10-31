<?php
session_start();
require "config/db.php";

$user_id = intval($_SESSION['user_id']);
$product_id = intval($_POST['product_id']);
$order_id = intval($_POST['order_id']);
$rating = intval($_POST['rating']);
$comment = trim($_POST['comment']);

if ($rating < 1 || $rating > 5)
    die("Vui lòng chọn số sao hợp lệ.");
if ($comment === "")
    die("Vui lòng nhập nội dung đánh giá.");

// Kiểm tra trùng
$check = $conn->query("SELECT id FROM reviews WHERE user_id=$user_id AND product_id=$product_id AND order_id=$order_id");
if ($check && $check->num_rows > 0) {
    die("Bạn đã đánh giá sản phẩm này rồi.");
}

$sql = "INSERT INTO reviews (user_id, product_id, order_id, rating, comment, created_at) 
        VALUES ($user_id, $product_id, $order_id, $rating, '{$conn->real_escape_string($comment)}', NOW())";

if ($conn->query($sql)) {
    echo "Cám ơn bạn đã đánh giá sản phẩm!";
} else {
    echo "Lỗi đánh giá.";
}
?>