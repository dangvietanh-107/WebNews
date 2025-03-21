<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["request_id"])) {
    header("Location: ../friends.php");
    exit();
}

$request_id = intval($_POST["request_id"]);
$user_id = $_SESSION["user_id"];
$sender_id = intval($_POST["sender_id"]);

if (isset($_POST["accept"])) {
    // Cập nhật trạng thái thành 'accepted'
    $sql = "UPDATE friend_requests SET status = 'accepted' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();

    // Thêm vào bảng bạn bè
    $sql = "INSERT INTO friends (user1_id, user2_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $sender_id);
    $stmt->execute();
} elseif (isset($_POST["decline"])) {
    // Xóa yêu cầu kết bạn
    $sql = "DELETE FROM friend_requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
}

header("Location: ../friends.php");
?>
