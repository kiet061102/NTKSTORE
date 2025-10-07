<?php
// Thông tin kết nối
$host = "localhost";   // thường là localhost
$user = "root";        // user MySQL (mặc định root trong XAMPP)
$pass = "";            // mật khẩu MySQL (nếu trống thì để rỗng)
$dbname = "ct501e";    // tên CSDL bạn đã tạo

// Kết nối MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");
?>