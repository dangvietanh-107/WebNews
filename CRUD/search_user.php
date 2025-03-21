<?php
session_start();
include '../includes/db.php'; // Kết nối database

if (!isset($_SESSION["user_id"])) {
    echo "<li>Vui lòng đăng nhập để xem danh sách bạn bè</li>";
    exit();
}

$user_id = $_SESSION["user_id"];
$query = isset($_GET['query']) ? trim($_GET['query']) : "";

// Truy vấn danh sách bạn bè
if ($query === "") {
    // Nếu không có từ khóa tìm kiếm, lấy toàn bộ danh sách bạn bè
    $sql = "SELECT u.id, u.username, u.avatar FROM friends f
            JOIN user u ON (u.id = f.user1_id OR u.id = f.user2_id)
            WHERE (f.user1_id = ? OR f.user2_id = ?) AND u.id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
} else {
    // Nếu có từ khóa tìm kiếm, lọc danh sách bạn bè theo tên
    $sql = "SELECT u.id, u.username, u.avatar FROM friends f
            JOIN user u ON (u.id = f.user1_id OR u.id = f.user2_id)
            WHERE (f.user1_id = ? OR f.user2_id = ?) 
            AND u.id != ? AND u.username LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("iiis", $user_id, $user_id, $user_id, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($friend = $result->fetch_assoc()) {
        echo '<li class="friend-item" onclick="openChat(' . $friend['id'] . ', \'' . htmlspecialchars($friend['username']) . '\')">
                <img src="' . htmlspecialchars($friend['avatar']) . '" alt="">
                ' . htmlspecialchars($friend['username']) . '
              </li>';
    }
} else {
    echo "<li>Không tìm thấy bạn bè nào</li>";
}

$stmt->close();
$conn->close();
