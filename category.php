<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Kết nối database

$username = $_SESSION["username"];

// Kiểm tra xem có id danh mục trong URL không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Danh mục không hợp lệ!");
}
$category_id = (int)$_GET['id'];

// Lấy thông tin danh mục
$sql_cat = "SELECT ten_danhmuc FROM danhmuc WHERE id = ?";
$stmt_cat = $conn->prepare($sql_cat);
$stmt_cat->bind_param("i", $category_id);
$stmt_cat->execute();
$result_cat = $stmt_cat->get_result();
$category_info = $result_cat->fetch_assoc();

if (!$category_info) {
    die("Danh mục không tồn tại!");
}

// Lấy danh sách bài viết thuộc danh mục này
$sql_posts = "SELECT posts.*, user.username 
              FROM posts
              JOIN user ON posts.user_id = user.id
              WHERE posts.category_id = ?
              ORDER BY posts.created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $category_id);
$stmt_posts->execute();
$posts_result = $stmt_posts->get_result();

// Lấy danh sách tất cả danh mục
$sql_danhmuc = "SELECT * FROM danhmuc";
$result_danhmuc = $conn->query($sql_danhmuc);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh mục: <?php echo htmlspecialchars($category_info['ten_danhmuc']); ?></title>
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

        .category-title {
            font-size: 22px;
            color: #333;
            margin-top: 30px;
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 5px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-content {
            padding: 16px;
        }

        .title {
            font-size: 16px;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #888;
            font-size: 14px;
        }

        .like2 {
            display: inline-block;
            margin-top: 0px;
            color: #009688;
            text-decoration: none;
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
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="index.php" class="active">Home</a>
        <a href="admin/my_posts.php">Quản lý bài đăng</a>
        <div class="dropdown">
            <a href="#" class="dropbtn">Danh mục ▼</a>
            <div class="dropdown-content">
                <?php while ($row = $result_danhmuc->fetch_assoc()): ?>
                    <a href="category.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['ten_danhmuc']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <a href="add_post.php" style="float: right;">Đăng bài</a>
        <a href="logout.php" style="float: right;">Đăng xuất</a>
        <a href="#" style="float: right;">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="profile_user.php" style="float: right;">Hồ sơ</a>
    </div>

    <div class="container">
        <h2 class="category-title">Danh mục: <?php echo htmlspecialchars($category_info['ten_danhmuc']); ?></h2>

        <div class="grid-container">
            <?php if ($posts_result->num_rows > 0): ?>
                <?php while ($row = $posts_result->fetch_assoc()): ?>
                    <article class="card">
                        <img class="card-image" src="<?php echo htmlspecialchars($row['banner']); ?>" alt="Banner">
                        <div class="card-content">
                            <h2 class="title">
                                <a class="like2" href="admin/post_detail.php?id=<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </a>
                            </h2>
                            <div class="meta">
                                <span>Đăng bởi: <?php echo htmlspecialchars($row['username']); ?></span>
                                <span><?php echo date("d/m/Y H:i", strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có bài viết nào trong danh mục này.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
