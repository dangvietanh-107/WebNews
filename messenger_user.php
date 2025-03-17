<?php
session_start();
include 'includes/db.php'; // Kết nối database

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION["username"];
$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);
// Lấy danh sách bạn bè
$friendsQuery = "SELECT u.id, u.username, u.avatar FROM friends f
    JOIN user u ON (u.id = f.user1_id OR u.id = f.user2_id)
    WHERE (f.user1_id = ? OR f.user2_id = ?) AND u.id != ?";
$stmt = $conn->prepare($friendsQuery);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$friends = $stmt->get_result();

// Lấy thông tin người dùng hiện tại
$userQuery = "SELECT username, avatar FROM user WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger</title>
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

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        .message-box {
            display: flex;
            flex-direction: column;
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 10px;
            max-width: 70%;
        }

        .message.sent {
            background-color: #1abc9c;
            color: white;
            align-self: flex-end;
        }

        .message.received {
            background-color: #ecf0f1;
            color: #333;
            align-self: flex-start;
        }

        .input-box {
            display: flex;
            margin-top: 10px;
        }

        .input-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .input-box button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .input-box button:hover {
            background-color: #16a085;
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
        <a href="../messenger_user.php">Nhắn tin</a>
        <a href="../add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="../profile_user.php" class="right">Hồ sơ</a>
    </div>

    <div class="container">
        <div class="sidebar-left">
            <input type="text" placeholder="Tìm kiếm bạn bè...">
            <ul class="friend-list">
                <?php while ($friend = $friends->fetch_assoc()) { ?>
                    <li onclick="openChat(<?php echo $friend['id']; ?>, '<?php echo $friend['username']; ?>', '<?php echo $friend['avatar']; ?>')">
                        <img src="<?php echo $friend['avatar']; ?>" alt="" style="width: 60px; height: 60px; border-radius: 30%;">
                        <span><?php echo $friend['username']; ?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="chat-box">
            <div class="chat-header">
                <img id="chat-avatar" src="" alt="">
                <span id="chat-username">Chọn bạn để nhắn tin</span>
            </div>
            <div class="chat-messages" id="messages"></div>
            <div class="chat-input">
                <input type="text" id="message" placeholder="Nhập tin nhắn...">
                <button onclick="sendMessage()">Gửi</button>
            </div>
        </div>
        <div class="sidebar-right">
            <img src="<?php echo $user['avatar']; ?>" alt="">
            <p><?php echo $user['username']; ?></p>
            <button onclick="window.location.href='profile.php'">Trang cá nhân</button>
        </div>
    </div>

    <script>
        let currentChatUserId = null;

        function openChat(userId, username, avatar) {
            document.getElementById('chat-avatar').src = 'avatars/' + avatar;
            document.getElementById('chat-username').innerText = username;
            currentChatUserId = userId;
            loadMessages();
        }

        function sendMessage() {
            let message = document.getElementById('message').value;
            if (message.trim() === '' || currentChatUserId === null) return;

            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: currentChatUserId,
                    message: message
                })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    document.getElementById('message').value = '';
                    loadMessages();
                }
            });
        }

        function loadMessages() {
            if (currentChatUserId === null) return;
            fetch('get_messages.php?user_id=' + currentChatUserId)
                .then(response => response.json())
                .then(data => {
                    let messagesBox = document.getElementById('messages');
                    messagesBox.innerHTML = '';
                    data.messages.forEach(msg => {
                        let msgElement = document.createElement('div');
                        msgElement.classList.add(msg.sender_id == <?php echo $user_id; ?> ? 'my-message' : 'their-message');
                        msgElement.innerText = msg.content;
                        messagesBox.appendChild(msgElement);
                    });
                });
        }

        setInterval(loadMessages, 3000);
    </script>
</body>

</html>