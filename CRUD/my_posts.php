<?php
session_start();
require "../includes/db.php";

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION["username"])) {
    die("Bạn cần đăng nhập để xem bài viết của mình.");
}
$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result_category = $conn->query($sql);
$username = $_SESSION["username"];


// Lấy user_id từ username
$sql_user = "SELECT id FROM user WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Không tìm thấy người dùng.");
}

$user = $result_user->fetch_assoc();
$user_id = $user["id"]; // Lấy user_id của người dùng

// Lấy danh sách bài viết của user theo user_id
$sql = "SELECT id, title, created_at FROM posts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Đổi "s" thành "i" vì user_id là INT
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
            overflow: visible;
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

        form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            display: block;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>NewsHub</h1>
        <p>Trung tâm tin tức, nơi mọi người trao đổi thông tin</p>
    </div>
    <div class="navbar">
        <a href="../index.php" class="active">Home</a>

        <div class="dropdown">
            <a href="#" class="dropbtn">Danh mục ▼</a>
            <div class="dropdown-content">
                <?php while ($row = $result_category->fetch_assoc()): ?>
                    <a href="../category.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['ten_danhmuc']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <a href="search_post.php">Tìm kiếm</a>
        <a href="../friend_user.php">tìm kiếm bạn bè</a>
        <a href="../friends.php">Bạn bè</a>
        <a href="../messenger_user.php">Nhắn tin</a>
        <a href="../my_report.php">Báo cáo</a>
        <a href="add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
        <h1>Bài viết của tôi</h1>
        <a class="like" href="add_post.php">Thêm bài viết mới</a>
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