<?php
include '../includes/db.php';

if (isset($_GET['user_id'])) {
    $chat_user_id = intval($_GET['user_id']);
    $query = "SELECT username, avatar FROM user WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $chat_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chatUser = $result->fetch_assoc();

    if ($chatUser) {
        echo '<img src="' . htmlspecialchars($chatUser['avatar']) . '" alt="Avatar">';
        echo '<p>' . htmlspecialchars($chatUser['username']) . '</p>';
        echo '<button onclick="viewProfile(' . $chat_user_id . ')">Trang cá nhân</button>';
    } else {
        echo '<p>Người dùng không tồn tại</p>';
    }
} else {
    echo '<p>Chưa chọn người trò chuyện</p>';
}
?>
