<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Kết nối database

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Lấy danh sách user (trừ bản thân)
$sql = "SELECT id, username FROM user WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Kiểm tra nếu có yêu cầu xóa user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];

    function tableExists($conn, $table)
    {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        return $result && $result->num_rows > 0;
    }

    // Xóa dữ liệu nếu bảng tồn tại
    if (tableExists($conn, 'post_reactions')) {
        $conn->query("DELETE FROM post_reactions WHERE user_id = $delete_id");
    }
    if (tableExists($conn, 'comment_reactions')) {
        $conn->query("DELETE FROM comment_reactions WHERE user_id = $delete_id");
    }
    if (tableExists($conn, 'comments')) {
        $conn->query("DELETE FROM comments WHERE user_id = $delete_id");
    }
    if (tableExists($conn, 'friend_requests')) {
        $conn->query("DELETE FROM friend_requests WHERE sender_id = $delete_id OR receiver_id = $delete_id");
    }
    if (tableExists($conn, 'friends')) {
        $conn->query("DELETE FROM friends WHERE user1_id = $delete_id OR user2_id = $delete_id");
    }
    if (tableExists($conn, 'messages')) {
        $conn->query("DELETE FROM messages WHERE sender_id = $delete_id OR receiver_id = $delete_id");
    }
    if (tableExists($conn, 'notifications')) {
        $conn->query("DELETE FROM notifications WHERE user_id = $delete_id OR sender_id = $delete_id");
    }
    if (tableExists($conn, 'reports')) {
        $conn->query("DELETE FROM reports WHERE user_id = $delete_id");
    }
    if (tableExists($conn, 'posts')) {
        $conn->query("DELETE FROM posts WHERE user_id = $delete_id");
    }

    // Xóa user
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Người dùng đã được xóa thành công!'); window.location.href='delete_user.php';</script>";
    } else {
        echo "<script>alert('Xóa thất bại! Hãy thử lại.'); window.location.href='delete_user.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa người dùng</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
        }

        .header {
            padding: 0.5px;
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


        .search-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-container input {
            padding: 8px;
            margin: 5px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .results {
            max-width: 600px;
            margin: 20px auto;
        }

        .card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .user-info a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            flex-grow: 1;
        }

        .friend-button {
            margin-left: 10px;
        }

        .friend-button form {
            display: inline;
        }

        .friend-button button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .friend-button button:disabled {
            background: gray;
            cursor: not-allowed;
        }
    </style>
    <script>
        function searchUser() {
            let input = document.getElementById("search").value.toLowerCase();
            let users = document.querySelectorAll("#userList .card");
            users.forEach(user => {
                let username = user.querySelector("a").innerText.toLowerCase();
                user.style.display = username.includes(input) ? "flex" : "none";
            });
        }
    </script>
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
        <h2>Danh sách người dùng</h2>
        <input type="text" id="search" placeholder="Tìm kiếm username..." onkeyup="searchUser()">
        <div class="results">
            <ul id="userList">
                <?php foreach ($users as $row): ?>
                    <li class="card">
                        <div class="user-info">
                            <a href="../profile_user.php?id=<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['username']); ?>
                            </a>
                            <form method="POST" action="delete_user.php" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>

</html>