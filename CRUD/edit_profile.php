<?php
session_start();
require_once "../includes/db.php"; // Kết nối database

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION["username"];
$user_id = (int)$_SESSION["user_id"];

// Lấy thông tin user từ database
$sql = "SELECT username, email, date_of_birth, gender, avatar, bio FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Người dùng không tồn tại!";
    exit();
}

$user = $result->fetch_assoc();
$error_message = "";

// Xử lý cập nhật thông tin
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $date_of_birth = $_POST["date_of_birth"];
    $gender = $_POST["gender"];
    $bio = trim($_POST["bio"]);
    $avatar = $user["avatar"]; // Giữ ảnh cũ nếu không thay đổi

    // Kiểm tra mật khẩu nếu có nhập mới
    if (!empty($_POST["password"])) {
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    } else {
        $password = null; // Không cập nhật nếu không nhập mật khẩu mới
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ!";
    }

    // Kiểm tra email có trùng với user khác không
    if (empty($error_message)) {
        $check_email_sql = "SELECT id FROM user WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email đã tồn tại!";
        }
    }

    // Xử lý upload ảnh mới nếu có
    if (!empty($_FILES["avatar"]["name"]) && empty($error_message)) {
        $target_dir = "../uploads/avatars/";

        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $avatar_name = time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $avatar_name;
        $file_ext = strtolower(pathinfo($avatar_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_types)) {
            $error_message = "Chỉ chấp nhận ảnh JPG, JPEG, PNG, GIF!";
        } elseif ($_FILES["avatar"]["size"] > 5000000) {
            $error_message = "Ảnh quá lớn, tối đa 5MB!";
        } elseif (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            // Xóa ảnh cũ nếu không phải ảnh mặc định
            if ($user["avatar"] !== "default.jpg" && file_exists($user["avatar"])) {
                unlink($user["avatar"]);
            }
            $avatar = $target_file;
        } else {
            $error_message = "Lỗi khi upload ảnh!";
        }
    }

    // Nếu không có lỗi, cập nhật thông tin user
    if (empty($error_message)) {
        if ($password !== null) {
            $update_sql = "UPDATE user SET username=?, password=?, email=?, date_of_birth=?, gender=?, avatar=?, bio=? WHERE id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssssssi", $username, $password, $email, $date_of_birth, $gender, $avatar, $bio, $user_id);
        } else {
            $update_sql = "UPDATE user SET username=?, email=?, date_of_birth=?, gender=?, avatar=?, bio=? WHERE id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssssi", $username, $email, $date_of_birth, $gender, $avatar, $bio, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION["username"] = $username; // Cập nhật session
            header("Location: ../profile_user.php?update=success");
            exit();
        } else {
            $error_message = "Lỗi cập nhật thông tin: " . $stmt->error;
        }
    }
}
?>


<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa hồ sơ</title>
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
        input[type="email"],
        input[type="password"],
        input[type="date"],
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
        input[type="file"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        
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
    <h2>Chỉnh sửa hồ sơ</h2>

    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Tên người dùng:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu mới (bỏ trống nếu không đổi):</label>
            <input type="password" name="password" id="password">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="date_of_birth">Ngày sinh:</label>
            <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>" required>
        </div>

        <div class="form-group">
            <label for="gender">Giới tính:</label>
            <select name="gender" id="gender" required>
                <option value="Nam" <?php echo ($user['gender'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                <option value="Nữ" <?php echo ($user['gender'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                <option value="Khác" <?php echo ($user['gender'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
            </select>
        </div>

        <div class="form-group">
            <label for="bio">Giới thiệu bản thân:</label>
            <textarea name="bio" id="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="avatar">Ảnh đại diện:</label>
            <input type="file" name="avatar" id="avatar" accept="image/*">

            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg'): ?>
                <div class="avatar-preview">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" width="100">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit">Cập nhật</button>
    </form>
</div>


</body>

</html>