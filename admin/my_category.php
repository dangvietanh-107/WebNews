<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Kết nối database
$username = $_SESSION["username"];

// Lấy danh sách danh mục
$sql = "SELECT * FROM danhmuc ORDER BY id DESC";
$result = $conn->query($sql);

// Xóa danh mục nếu có yêu cầu
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Kiểm tra xem danh mục có bài viết nào không
    $check_sql = "SELECT COUNT(*) AS total FROM posts WHERE category_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $check_result = $stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo "<script>alert('Không thể xóa danh mục đang có bài viết!');</script>";
    } else {
        $delete_sql = "DELETE FROM danhmuc WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $category_id);
        if ($stmt->execute()) {
            echo "<script>alert('Xóa danh mục thành công!'); window.location='my_category.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi xóa danh mục!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            padding: 10px;
            text-align: center;
            background: #1abc9c;
            color: white;
        }
        .navbar {
            overflow: hidden;
            background-color: #333;
            position: sticky;
            top: 0;
        }
        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }
        .navbar a.right {
            float: right;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
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
        <h2>Danh sách danh mục</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Hành động</th>
            </tr>
            <?php while ($category = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo htmlspecialchars($category['ten_danhmuc']); ?></td>
                    <td>
                        <a href="edit_category.php?id=<?php echo $category['id']; ?>">Sửa</a> |
                        <a href="my_category.php?delete=<?php echo $category['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa danh mục này không?');">Xóa</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
