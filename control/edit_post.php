<?php
session_start();
require "../includes/db.php";

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION["username"]; // Lấy username từ session
$post_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$user_id = $_SESSION["user_id"];

// Lấy danh sách danh mục
$danhmuc = [];
$sql = "SELECT id, ten_danhmuc FROM danhmuc";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $danhmuc[] = $row;
}

// Lấy thông tin bài viết
$sql = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Bạn không có quyền chỉnh sửa bài viết này!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $category_id = (int) $_POST["category"];
    $content = trim($_POST["content"]);
    $banner = $post["banner"];

    // Kiểm tra nếu có tải ảnh mới
    if (!empty($_FILES["banner"]["name"])) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["banner"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra định dạng ảnh hợp lệ
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Chỉ cho phép các định dạng ảnh JPG, JPEG, PNG, GIF.");
        }

        // Lưu ảnh mới
        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
            $banner = $target_file;
        } else {
            die("Lỗi khi tải ảnh lên.");
        }
    }
    

    // Cập nhật bài viết
    $sql = "UPDATE posts SET title = ?, category_id = ?, content = ?, banner = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $title, $category_id, $content, $banner, $post_id, $user_id);
    

    if ($stmt->execute()) {
        echo "Cập nhật thành công!";
        header("Location: my_posts.php");
        exit();
    } else {
        die("Lỗi khi cập nhật bài viết.");
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa bài viết</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .header {
            padding: 10px;
            text-align: center;
            background: #1abc9c;
            color: white;
        }

        .header h1 {
            font-size: 40px;
        }

        .navbar {
            overflow: hidden;
            background-color: #333;
            position: sticky;
            top: 0;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        .navbar a.right {
            float: right;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        .navbar a.active {
            background-color: #666;
            color: white;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="text"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #009688;
            box-shadow: 0 0 0 2px rgba(0, 150, 136, 0.1);
        }

        textarea {
            height: 300px;
            resize: vertical;
            min-height: 150px;
            line-height: 1.6;
        }

        button {
            background-color: #009688;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            display: block;
            margin: 20px auto 0;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #00796b;
        }

        input[type="file"] {
            background: #f8f9fa;
            padding: 12px;
            cursor: pointer;
        }

        /* Custom styling for file input */
        input[type="file"]::file-selector-button {
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.2s;
        }

        input[type="file"]::file-selector-button:hover {
            background: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="../index.php" class="active">Home</a>
        <a href="../admin/my_posts.php">Quản lý bài đăng</a>
        <a href="../add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="#" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
        <a href="../profile_user.php" class="right">Hồ sơ</a>
    </div>

    <h2>Chỉnh sửa bài viết</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Tiêu đề:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br>

        <select name="category" required>
            <?php foreach ($danhmuc as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $post['category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['ten_danhmuc']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Nội dung:</label>
        <textarea name="content" id="editor" required><?php echo isset($post['content']) ? htmlspecialchars($post['content']) : ''; ?></textarea><br>

        <label>Ảnh hiện tại:</label><br>
        <img src="../<?php echo $post['banner']; ?>" alt="Banner" style="width:400px;"><br>

        <label>Chọn ảnh mới (nếu muốn thay đổi):</label>
        <input type="file" name="banner"><br>

        <button type="submit">Cập nhật</button>
    </form>
    <script src="https://cdn.ckeditor.com/4.20.1/standard-all/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('editor', {
            // Cấu hình thanh công cụ với các chức năng như Word
            toolbar: [{
                    name: 'document',
                    items: ['Source']
                },
                {
                    name: 'clipboard',
                    items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                },
                {
                    name: 'editing',
                    items: ['Find', 'Replace', '-', 'SelectAll']
                },
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat']
                },
                '/',
                {
                    name: 'paragraph',
                    items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                },
                {
                    name: 'links',
                    items: ['Link', 'Unlink', 'Anchor']
                },
                {
                    name: 'insert',
                    items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']
                },
                '/',
                {
                    name: 'styles',
                    items: ['Styles', 'Format', 'Font', 'FontSize']
                },
                {
                    name: 'colors',
                    items: ['TextColor', 'BGColor']
                },
                {
                    name: 'tools',
                    items: ['Maximize', 'ShowBlocks']
                }
            ],
            // Cho phép tải lên hình ảnh
            filebrowserUploadUrl: 'upload.php',
            filebrowserUploadMethod: 'form',
            // Cài đặt các plugin bổ sung
            extraPlugins: 'colorbutton,font,justify,indentblock',
            // Cài đặt chiều cao
            height: 400,
            // Cho phép nội dung tạm thời
            removePlugins: 'elementspath',
            resize_enabled: true
        });
    </script>
</body>

</html>