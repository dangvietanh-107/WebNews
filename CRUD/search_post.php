<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);
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
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm bài viết</title>
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
            width: 100%;
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
        <a href="../index.php" class="active">Home</a>
        <div class="dropdown">
            <a href="#" class="dropbtn">Danh mục ▼</a>
            <div class="dropdown-content">
                <?php while ($row = $result->fetch_assoc()): ?>
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
        <a href="../my_report.php" >Báo cáo</a>
        <a href="add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
    </div>

    <div class="search-container">
        <form method="GET" action="search_post.php">
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
                        <a href="post_detail.php?id=<?= $post['id']; ?>">
                            <?= htmlspecialchars($post['title']); ?>
                        </a>
                    </h3>
                    <p>Đăng bởi: <?= htmlspecialchars($post['username']); ?> | Ngày: <?= date("d/m/Y H:i", strtotime($post['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($_GET['search'])): ?>
            <p>Không tìm thấy bài viết nào phù hợp.</p>
        <?php endif; ?>
    </div>
</body>

</html>
