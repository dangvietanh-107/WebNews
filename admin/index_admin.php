<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Kết nối database
$username = $_SESSION["username"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_danhmuc = trim($_POST["ten_danhmuc"]);

    // Kiểm tra rỗng
    if (empty($ten_danhmuc)) {
        $message = "<p style='color: red;'>Tên danh mục không được để trống!</p>";
    } else {
        // Kiểm tra trùng tên
        $query = $conn->prepare("SELECT * FROM danhmuc WHERE ten_danhmuc = ?");
        $query->bind_param("s", $ten_danhmuc);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $message = "<p style='color: red;'>Danh mục này đã tồn tại!</p>";
        } else {
            // Thêm danh mục vào database
            $stmt = $conn->prepare("INSERT INTO danhmuc (ten_danhmuc) VALUES (?)");
            $stmt->bind_param("s", $ten_danhmuc);
            if ($stmt->execute()) {
                $message = "<p style='color: green;'>Thêm danh mục thành công!</p>";
            } else {
                $message = "<p style='color: red;'>Lỗi khi thêm danh mục!</p>";
            }
            $stmt->close();
        }
        $query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Danh Mục</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; }
        .header { padding: 0.5px; text-align: center; background: #1abc9c; color: white; }
        .header h1 { font-size: 40px; }
        .navbar { overflow: hidden; background-color: #333; position: sticky; top: 0; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a.right { float: right; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .navbar a.active { background-color: #666; color: white; }
        .container { width: 50%; margin: auto; padding: 20px; text-align: center; }
        input[type="text"] { width: 80%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 20px; background: #1abc9c; color: white; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #16a085; }
    </style>
</head>
<body>

<div class="header">
    <h1>NewsHub</h1>
    <p>Trung tâm tin tức, nơi mọi người trao đổi thông tin</p>
</div>

<div class="navbar">
    <a href="index_admin.php" class="active">Trang chủ Admin</a>
    <a href="my_category.php">Danh mục</a>
    <a href="notification_admin.php">Thông báo</a>
    <a href="admin_post.php">Quản lý bài viết</a>
    <a href="delete_user.php">Quản lý người dùng</a>
    <a href="report_admin.php">Report</a>
    <a href="../logout.php" class="right">Đăng xuất</a>
    <a href="" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
    <a href="../index.php" class="right">Trở về Website</a>
</div>

<div class="container">
    <h2>Thêm Danh Mục</h2>
    <form method="POST" action="">
        <input type="text" name="ten_danhmuc" id="ten_danhmuc" placeholder="Nhập tên danh mục">
        <br>
        <button type="submit">Thêm danh mục</button>
    </form>
    <br>
    <div id="message"><?php echo $message; ?></div>
</div>

<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        setTimeout(() => {
            document.getElementById("ten_danhmuc").value = "";
        }, 100);
    });
</script>

</body>
</html>
