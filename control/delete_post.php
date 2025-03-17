<?php
session_start();
require "../includes/db.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION["user_id"])) {
    die("Bạn cần đăng nhập để thực hiện hành động này.");
}

// Kiểm tra id bài viết
if (!isset($_GET["id"])) {
    die("Bài viết không tồn tại!");
}

$post_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

// Xóa bài viết nếu user sở hữu
$sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Xóa bài viết thành công!";
} else {
    die("Bạn không có quyền xóa bài viết này!");
}

header("Location: my_posts.php");
exit();
?>
