<?php
session_start();
include '../includes/db.php'; // Kết nối database

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["receiver_id"])) {
    $receiver_id = intval($_POST["receiver_id"]);

    // Kiểm tra xem đã có lời mời kết bạn nào chưa
    $sql = "SELECT status FROM friend_requests WHERE 
        (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();

    $status = null; // Gán giá trị mặc định
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    if ($status !== null) {  // Kiểm tra có giá trị hay không
        $_SESSION['message'] = "Đã gửi yêu cầu hoặc đã là bạn bè.";
    } else {
        $sql = "INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $receiver_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Đã gửi yêu cầu kết bạn.";
        } else {
            $_SESSION['message'] = "Lỗi khi gửi yêu cầu.";
        }
        $stmt->close();
    }
}

header("Location: ../friend_user.php");
exit();
