<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Kết nối database

$username = $_SESSION["username"];
$user_id = $_SESSION["user_id"];

// Kiểm tra nếu có tham số id trên URL để xem hồ sơ của người khác
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $user_id;

$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);

// Lấy thông tin người dùng
$userQuery = "SELECT username, avatar, bio FROM user WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $profile_id); // Sử dụng profile_id thay vì user_id
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Kiểm tra xem có dữ liệu người dùng hay không
if (!$userData) {
    echo "<script>alert('Người dùng không tồn tại!'); window.location.href='index.php';</script>";
    exit();
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ</title>
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

        .profile-container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-header .info {
            flex-grow: 1;
            margin-left: 20px;
        }

        .profile-header .actions button {
            margin-left: 10px;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .profile-header .actions .btn-post {
            background-color: #1abc9c;
            color: white;
        }

        .profile-header .actions .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .profile-content {
            display: flex;
            margin-top: 20px;
        }

        .about-section {
            width: 30%;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .posts-section {
            width: 70%;
            padding: 20px;
        }

        .posts-section .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
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
        <a href="../search_post.php">Quản lý bài đăng</a>
        <div class="dropdown">
            <a href="#" class="dropbtn">Danh mục ▼</a>
            <div class="dropdown-content">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="category.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['ten_danhmuc']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <a href="../add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="../profile_user.php" class="right">Hồ sơ</a>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" alt="Avatar">
            <div class="info">
                <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                <p><?php echo htmlspecialchars($userData['bio']); ?></p>
            </div>
            <div class="actions">
                <?php if ($profile_id == $user_id): ?>
                    <a href="../add_post"> <button class="btn-post">Đăng bài</button> </a>
                    <a href="../edit_profile.php"> <button class="btn-edit">Chỉnh sửa trang cá nhân</button> </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-content">
            <div class="about-section">
                <h3>Giới thiệu</h3>
                <p><?php echo htmlspecialchars($userData['bio']); ?></p>
            </div>
            <div class="posts-section">
                <div class="actions">
                    <?php if ($profile_id == $user_id): ?>
                        <a href="admin/my_posts.php"> <button>Quản lý bài viết</button> </a>
                    <?php endif; ?>
                    <button>Chế độ xem</button>
                </div>
                <div class="posts-list">
                    <?php
                    $postQuery = "SELECT id, title, content, banner ,created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC";
                    $stmt = $conn->prepare($postQuery);
                    $stmt->bind_param("i", $profile_id);
                    $stmt->execute();
                    $posts = $stmt->get_result();

                    if ($posts->num_rows > 0):
                        while ($post = $posts->fetch_assoc()): ?>
                            <div class="card">
                                <div class="card-content">
                                    <h3 class="title">
                                        <a class="like2" href="../admin/post_detail.php?id=<?php echo $post['id']; ?>">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h3>
                                    <img class="card-image" src="<?php echo htmlspecialchars($post['banner']); ?>" alt="Banner">



                                    <div class="meta">
                                        <span>Ngày đăng: <?php echo date("d-m-Y H:i", strtotime($post['created_at'])); ?></span>
                                        <?php if ($profile_id == $user_id): ?>
                                        <a href="../admin/edit_post.php?id=<?php echo $post['id']; ?>" class="like2">Chỉnh sửa</a>
                                        <a href="../admin/delete_post.php?id=<?php echo $post['id']; ?>" class="like2" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');">Xóa</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
                    else: ?>
                        <p>Chưa có bài đăng nào.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</body>

</html>