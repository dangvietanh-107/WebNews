<?php
session_start();
require "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    die("Bạn cần đăng nhập để bình luận.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_id"]) && isset($_POST["comment_content"])) {
    $user_id = $_SESSION["user_id"];
    $post_id = $_POST["post_id"];
    $content = trim($_POST["comment_content"]);

    if (empty($content)) {
        die("Nội dung bình luận không được để trống.");
    }

    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $content);

    if ($stmt->execute()) {
        header("Location: post_detail.php?id=" . $post_id); // Chuyển hướng về trang bài viết sau khi bình luận
        exit();
    } else {
        die("Lỗi khi thêm bình luận. Vui lòng thử lại!");
    }
} else {
    die("Dữ liệu không hợp lệ!");
}
?>
