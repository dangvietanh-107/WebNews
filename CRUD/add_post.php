<?php
session_start();
require_once "../includes/db.php"; // Sử dụng require_once để tránh include nhiều lần

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION["username"];
$user_id = (int)$_SESSION["user_id"];

// Kiểm tra user tồn tại
$checkUser = $conn->prepare("SELECT id FROM user WHERE id = ?");
$checkUser->bind_param("i", $user_id);
$checkUser->execute();
$result = $checkUser->get_result();

if ($result->num_rows == 0) {
    die("User không tồn tại trong cơ sở dữ liệu.");
}

// Lấy danh sách danh mục từ database
$result = $conn->query("SELECT * FROM danhmuc");
$danhmucs = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int)$_SESSION["user_id"];
    $category_id = (int)trim($_POST["category_id"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    // Xử lý upload ảnh banner
    $target_dir = "../uploads/";

    // Kiểm tra thư mục tồn tại, nếu không thì tạo
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $banner_name = basename($_FILES["banner"]["name"]);
    $target_file = $target_dir . time() . "_" . $banner_name; // Tránh trùng tên file

    // Kiểm tra định dạng file
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file_ext = strtolower(pathinfo($banner_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        die("Chỉ chấp nhận file hình ảnh (jpg, jpeg, png, gif)");
    }

    // Kiểm tra kích thước file (giới hạn 5MB)
    if ($_FILES["banner"]["size"] > 5000000) {
        die("File quá lớn, vui lòng chọn file nhỏ hơn 5MB");
    }

    if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
        // Lưu vào database với category_id (không phải category)
        $sql = "INSERT INTO posts (user_id, banner, category_id, title, content) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiss", $user_id, $target_file, $category_id, $title, $content);

        if ($stmt->execute()) {
            // Đặt thông báo thành công vào session
            $_SESSION['post_success'] = true;
            header("Location: ../index.php"); // Quay lại trang chính
            exit();
        } else {
            echo "Lỗi khi đăng bài: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Lỗi khi upload ảnh!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm bài viết</title>
    <!-- Thêm CKEditor -->
    <script src="https://cdn.ckeditor.com/4.20.1/standard-all/ckeditor.js"></script>
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

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
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
        textarea:focus,
        select:focus {
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

        /* Thêm style cho editor */
        .ck-editor__editable {
            min-height: 300px;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            display: block;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body>
<div class="header">
    <h1>NewsHub</h1>
    <p>Trung tâm tin tức, nơi mọi người trao đổi thông tin</p>
    </div>
    <div class="navbar">
        <a href="../index.php" class="active">Home</a>
        <div class="dropdown">
            <a href="#" class="dropbtn">Danh mục ▼</a>
            <div class="dropdown-content">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="../category.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['ten_danhmuc']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <a href="search_post.php">Tìm kiếm</a>
        <a href="../friend_user.php">tìm kiếm bạn bè</a>
        <a href="../friends.php">Bạn bè</a>
        <a href="../messenger_user.php">Nhắn tin</a>
        <a href="../my_report.php" >Báo cáo</a>
        <a href="add_post.php" class="right">Đăng bài</a>
        <a href="../logout.php" class="right">Đăng xuất</a>
        <a href="../profile_user.php" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
    </div>

    <div class="container">
        <h2>Thêm bài viết mới</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label>Ảnh banner:</label>
            <input type="file" name="banner" required accept="image/*">

            <label>Danh mục:</label>
            <select name="category_id" required>
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($danhmucs as $dm) { ?>
                    <option value="<?= $dm['id']; ?>"><?= htmlspecialchars($dm['ten_danhmuc']); ?></option>
                <?php } ?>
            </select>

            <label>Tiêu đề:</label>
            <input type="text" name="title" required>

            <label>Nội dung:</label>
            <textarea name="content" id="editor" required></textarea>

            <button type="submit">Đăng bài</button>
        </form>
    </div>

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
            filebrowserUploadUrl: '../upload.php',
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