<?php
session_start();
include 'includes/db.php'; // Kết nối database

$user_id = $_SESSION['user_id'];

$username = $_SESSION["username"];

$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);


// Lấy danh sách lời mời kết bạn đang chờ xử lý
$sql_pending = "SELECT friend_requests.id, user.id AS sender_id, user.username, user.avatar 
                FROM friend_requests
                JOIN user ON friend_requests.sender_id = user.id
                WHERE friend_requests.receiver_id = ? AND friend_requests.status = 'pending'";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pending_requests = $stmt_pending->get_result();

// Lấy danh sách bạn bè
$sql_friends = "SELECT user.id, user.username, user.avatar 
                FROM friends
                JOIN user ON (friends.user1_id = user.id OR friends.user2_id = user.id)
                WHERE (friends.user1_id = ? OR friends.user2_id = ?) AND user.id != ?";
$stmt_friends = $conn->prepare($sql_friends);
$stmt_friends->bind_param("iii", $user_id, $user_id, $user_id);
$stmt_friends->execute();
$friends = $stmt_friends->get_result();

// Xử lý chấp nhận lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accept_request'])) {
    $request_id = $_POST['request_id'];
    $sender_id = $_POST['sender_id'];

    // Cập nhật trạng thái lời mời thành "accepted"
    $sql_accept = "UPDATE friend_requests SET status = 'accepted' WHERE id = ?";
    $stmt_accept = $conn->prepare($sql_accept);
    $stmt_accept->bind_param("i", $request_id);
    $stmt_accept->execute();

    // Thêm vào danh sách bạn bè
    $sql_add_friend = "INSERT INTO friends (user1_id, user2_id) VALUES (?, ?)";
    $stmt_add_friend = $conn->prepare($sql_add_friend);
    $stmt_add_friend->bind_param("ii", $user_id, $sender_id);
    $stmt_add_friend->execute();

    header("Location: notification.php");
    exit();
}

// Xử lý từ chối lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['decline_request'])) {
    $request_id = $_POST['request_id'];

    $sql_decline = "UPDATE friend_requests SET status = 'declined' WHERE id = ?";
    $stmt_decline = $conn->prepare($sql_decline);
    $stmt_decline->bind_param("i", $request_id);
    $stmt_decline->execute();

    header("Location: notification.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_friend'])) {
    $friend_id = $_POST['friend_id'];

    // Xóa bạn bè khỏi bảng `friends`
    $sql_remove_friend = "DELETE FROM friends WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt_remove_friend = $conn->prepare($sql_remove_friend);
    $stmt_remove_friend->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt_remove_friend->execute();

    header("Location: notification.php");
    exit();
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Thông báo kết bạn</title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .results img {
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }

        .user-info span {
            font-weight: bold;
            color: #333;
        }

        .friend-button {
            display: flex;
            gap: 10px;
            /* Khoảng cách giữa hai button */
            margin-left: auto;
        }

        .friend-button button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;

        }

        .friend-button button:hover {
            background: #16a085;
        }

        .friend-button button:disabled {
            background: gray;
            cursor: not-allowed;
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
        <a href="../search_post.php">Tìm kiếm</a>
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
        <a href="../friend_user.php">tìm kiếm bạn bè</a>
        <a href="../notification.php">Thông báo</a>
        <a href="../add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="../profile_user.php" class="right">Hồ sơ</a>
    </div>

    <div class="search-container">
        <h2>Lời mời kết bạn</h2>
        <?php while ($row = $pending_requests->fetch_assoc()): ?>
            <div class="results">
                <div class="user-info">
                    <img src="<?= $row['avatar'] ?>" alt="Avatar" width="50">
                    <span><?= $row['username'] ?></span>
                </div>
                <form method="POST" class="friend-button">
                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="sender_id" value="<?= $row['sender_id'] ?>">
                    <button type="submit" name="accept_request">Chấp nhận</button>
                    <button type="submit" name="decline_request">Từ chối</button>
                </form>
            </div>

        <?php endwhile; ?>
    </div>
    <div class="search-container">
    <h2>Danh sách bạn bè</h2>
    <?php while ($row = $friends->fetch_assoc()): ?>
        <div class="results">
            <div class="user-info">
                <img src="<?= $row['avatar'] ?>" alt="Avatar" width="50">
                <span><?= $row['username'] ?></span>
            </div>
            <form method="POST" class="friend-button">
                <input type="hidden" name="friend_id" value="<?= $row['id'] ?>">
                <button type="submit" name="remove_friend">Xóa bạn bè</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>
</body>

</html>