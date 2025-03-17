<?php 
session_start();
require "../includes/db.php";

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["username"])) {  // Thay đổi từ user_id thành username
    die("Bạn cần đăng nhập để xem bài viết của mình.");
}

$username = $_SESSION["username"];  // Lấy username thay vì user_id

// Lấy danh sách bài viết của user
$sql = "SELECT id, title, created_at FROM posts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);  // Thay đổi "i" thành "s" vì là string
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài viết của tôi</title>
    <style>
                body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }
        .header {
            padding: 10px;
            text-align: center;
            background: #1abc9c;
            color: white;
        }
        .header h1 {
            font-size: 40px;
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
        .navbar a.active {
            background-color: #666;
            color: white;
        }
        .like {
            display: inline-block;
            margin-top: 10px;
            color: #009688;
            text-decoration: none;
        }
        .like2 {
            display: inline-block;
            margin-top: 0px;
            color: #009688;
            text-decoration: none;
        }

        form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="header">
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="../index.php" class="active">Home</a>
        <a href="../admin/my_posts.php" >Quản lý bài đăng</a>
        <a href="../add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="#" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="../profile_user.php" class="right">Hồ sơ</a>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
    <h1>Bài viết của tôi</h1>
    <a class="like" href="../add_post.php">Thêm bài viết mới</a>
    <hr>
   
    <?php while ($post = $result->fetch_assoc()) { ?>
        <h3><?php echo htmlspecialchars($post["title"]); ?></h3>
        <p>Ngày đăng: <?php echo date("d/m/Y H:i", strtotime($post["created_at"])); ?></p>
        <a class="like" href="edit_post.php?id=<?php echo $post["id"]; ?>">Sửa</a>
        | 
        <a class="like" href="delete_post.php?id=<?php echo $post["id"]; ?>" onclick="return confirm('Bạn có chắc muốn xóa bài này không?');">Xóa</a>
        <hr>
    <?php } ?>
    </form>

</body>
</html>
