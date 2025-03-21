<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    exit();
}

$current_user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'];

$sql = "SELECT sender_id, content, created_at FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $class = ($row['sender_id'] == $current_user_id) ? 'sent' : 'received';
    echo "<div class='$class'><b>" . ($row['sender_id'] == $current_user_id ? 'Bạn' : 'Họ') . ":</b> " . htmlspecialchars($row['content']) . "</div>";

}
?>
