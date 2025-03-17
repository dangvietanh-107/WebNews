<?php
include 'db.php';

/**
 * Lấy danh sách danh mục tin tức
 */
function getCategories() {
    global $conn;
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($sql);

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

/**
 * Lấy danh sách bài viết mới nhất
 */
function getLatestPosts($limit = 10) {
    global $conn;
    $sql = "SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy bài viết theo ID
 */
function getPostById($id) {
    global $conn;
    $sql = "SELECT posts.*, categories.name as category_name, users.username 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id 
            LEFT JOIN users ON posts.user_id = users.id 
            WHERE posts.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Đăng xuất người dùng
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

?>
