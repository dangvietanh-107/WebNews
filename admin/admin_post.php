<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];

// Lấy danh mục một lần duy nhất
$sql_danhmuc = "SELECT * FROM danhmuc";
$result_danhmuc = $conn->query($sql_danhmuc);
$categories = $result_danhmuc->fetch_all(MYSQLI_ASSOC);

// Xử lý tìm kiếm
$search_query = $category_filter = "";
$posts = [];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search_query']);
    $category_filter = $_GET['category_filter'] ?? "all";

    $sql = "SELECT posts.*, user.username FROM posts 
            JOIN user ON posts.user_id = user.id 
            WHERE title LIKE ?";
    
    $params = ["%$search_query%"];
    $types = "s";

    if ($category_filter !== "all") {
        $sql .= " AND category_id = ?";
        $params[] = $category_filter;
        $types .= "i";
    }

    $sql .= " ORDER BY posts.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
}

// Xử lý xóa bài viết
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $delete_sql = "DELETE FROM posts WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $post_id);
    $delete_stmt->execute();
    
    // Reload lại trang sau khi xóa bài
    header("Location: admin_post.php?search_query=" . urlencode($search_query) . "&category_filter=" . urlencode($category_filter));
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý bài viết</title>
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
        .search-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-container input, .search-container select, .search-container button {
            padding: 8px;
            margin: 5px;
            width: 98%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .search-container button {
            background: #1abc9c;
            color: white;
            cursor: pointer;
        }

        .results {
            max-width: 800px;
            margin: 20px auto;
        }

        .card {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-4px);
        }

        .card a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
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

    <div class="search-container">
        <form method="GET" action="admin_post.php">
            <input type="text" name="search_query" placeholder="Nhập tiêu đề bài viết..." value="<?= htmlspecialchars($search_query); ?>">
            <select name="category_filter">
                <option value="all">Tất cả danh mục</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id']; ?>" <?= ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category['ten_danhmuc']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="search">Tìm kiếm</button>
        </form>
    </div>

    <div class="results">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card">
                    <h3>
                        <a href="admin_post_detail.php?id=<?= $post['id']; ?>">
                            <?= htmlspecialchars($post['title']); ?>
                        </a>
                    </h3>
                    <p>Đăng bởi: <?= htmlspecialchars($post['username']); ?> | Ngày: <?= date("d/m/Y H:i", strtotime($post['created_at'])); ?></p>
                    <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                        <button type="submit" name="delete_post">Xóa</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($_GET['search'])): ?>
            <p>Không tìm thấy bài viết nào phù hợp.</p>
        <?php endif; ?>
    </div>
</body>
</html>
