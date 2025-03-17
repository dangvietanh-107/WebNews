<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Kết nối database

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);

// Lấy danh sách user (trừ bản thân)
$sql = "SELECT id, username FROM user WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = []; // Lưu dữ liệu vào mảng
while ($row = $result->fetch_assoc()) {
    // Đảm bảo không có bản thân mình trong danh sách
    if ($row['id'] != $user_id) {
        $users[] = $row;
    }
}

// Kiểm tra trạng thái kết bạn
function getFriendStatus($conn, $user_id, $other_id)
{
    $sql = "SELECT status FROM friend_requests WHERE 
            (sender_id = ? AND receiver_id = ?) 
            OR (sender_id = ? AND receiver_id = ?)";
    $status = null; // Mặc định chưa kết bạn
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $other_id, $other_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close(); // Đóng statement để tránh lỗi kết nối
    return $status ?? null;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách người dùng</title>
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
            let users = document.querySelectorAll("#userList li");

            users.forEach(user => {
                let username = user.querySelector("a").innerText.toLowerCase();
                if (username.includes(input)) {
                    user.style.display = "flex";
                } else {
                    user.style.display = "none";
                }
            });
        }
    </script>
</head>

<body>
    <div class="header">
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="../index.php" class="active">Home</a>
        <a href="../search_post.php">Quản lý bài đăng</a>
        <!-- Dropdown danh mục -->
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

    <div class="search-container">
        <h2>Danh sách người dùng</h2>
        <input type="text" id="search" placeholder="Tìm kiếm username..." onkeyup="searchUser()">
        <div class="results">
            <ul id="userList">
                <?php foreach ($users as $row): ?>
                    <?php if ($row['id'] !== $user_id): // Ẩn chính mình 
                    ?>
                        <?php $friendStatus = getFriendStatus($conn, $user_id, $row['id']); ?>
                        <li class="card">
                            <div class="user-info">
                                <a href="profile_user.php?id=<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </a>
                                <div class="friend-button">
                                    <?php if ($friendStatus === 'pending'): ?>
                                        <button disabled>Đã gửi yêu cầu</button>
                                    <?php elseif ($friendStatus === 'accepted'): ?>
                                        <button disabled>Bạn bè</button>
                                    <?php else: ?>
                                        <form method="POST" action="send_friend_request.php">
                                            <input type="hidden" name="receiver_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit">Kết bạn</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

</body>

</html>