<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Kết nối database

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("Không có ID danh mục hợp lệ.");
}

$id = $_GET["id"];
$error = "";

// Lấy thông tin danh mục hiện tại
$sql = "SELECT * FROM danhmuc WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Danh mục không tồn tại.");
}

$category = $result->fetch_assoc();

// Cập nhật danh mục
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_danhmuc = trim($_POST["ten_danhmuc"]);

    if (empty($ten_danhmuc)) {
        $error = "Tên danh mục không được để trống.";
    } else {
        // Kiểm tra xem tên danh mục có bị trùng không
        $check_sql = "SELECT id FROM danhmuc WHERE ten_danhmuc = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $ten_danhmuc, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Tên danh mục đã tồn tại.";
        } else {
            // Cập nhật danh mục nếu hợp lệ
            $update_sql = "UPDATE danhmuc SET ten_danhmuc = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $ten_danhmuc, $id);
            
            if ($stmt->execute()) {
                header("Location: my_category.php");
                exit();
            } else {
                $error = "Cập nhật thất bại.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa danh mục</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .header { background: #1abc9c; color: white; text-align: center; padding: 15px; font-size: 20px; }
        .form-container { max-width: 400px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        input[type="text"], input[type="submit"], .back-btn { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 4px; }
        input[type="submit"] { background: #1abc9c; color: white; border: none; cursor: pointer; }
        input[type="submit"]:hover { background: #16a085; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
        .back-btn { background: #ccc; color: black; text-align: center; text-decoration: none; display: block; }
        .back-btn:hover { background: #bbb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sửa danh mục</h1>
    </div>

    <div class="form-container">
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label for="ten_danhmuc">Tên danh mục:</label>
            <input type="text" name="ten_danhmuc" id="ten_danhmuc" value="<?php echo htmlspecialchars($category["ten_danhmuc"]); ?>" required>
            <input type="submit" value="Cập nhật">
            <a href="my_category.php" class="back-btn">Quay lại</a>
        </form>
    </div>
</body>
</html>
