<?php
// Thông tin kết nối MySQL
$servername = "localhost"; // Hoặc IP máy chủ MySQL
$username = "root"; // Tên đăng nhập MySQL
$password = ""; // Mật khẩu MySQL (để trống nếu dùng localhost)
$dbname = "webnews"; // Tên cơ sở dữ liệu

// Kết nối CSDL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset để tránh lỗi font
$conn->set_charset("utf8mb4");

?>
