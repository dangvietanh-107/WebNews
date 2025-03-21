<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kiểm tra dữ liệu gửi lên
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Chỉ hỗ trợ POST']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
    exit();
}

if (!isset($_POST['message']) || !isset($_POST['receiver_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Tin nhắn trống']);
    exit();
}

// Kiểm tra ID người nhận hợp lệ
$user_check = $conn->prepare("SELECT id FROM user WHERE id = ?");
$user_check->bind_param("i", $receiver_id);
$user_check->execute();
$user_result = $user_check->get_result();
if ($user_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Người nhận không tồn tại']);
    exit();
}
$user_check->close();

// Chèn tin nhắn vào database
$sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    exit();
}

$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Tin nhắn đã gửi']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi khi gửi tin nhắn: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
