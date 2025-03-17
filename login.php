<?php
session_start();
include 'includes/db.php'; // Kết nối database

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["name"]);
    $password = trim($_POST["password"]);

    // Kiểm tra user trong database
    $stmt = $conn->prepare("SELECT id, password FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Kiểm tra mật khẩu
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            header("Location: index.php"); // Chuyển hướng đến trang index.php
            exit();
        } else {
            $error_message = "Mật khẩu không đúng!";
        }
    } else {
        $error_message = "Tên đăng nhập không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <style>
        .error {
            color: red;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }
        .form {
            max-width: 400px;
            margin: 0 auto;
            background-color:whitesmoke;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid gray;
            border-radius: 4px;
        }
        .submit {
            background-color: #04AA6D;
            color: white;
            padding: 14px 20px;
            border: none;
            cursor: pointer;
            width: 50%;
            opacity: 0.9;
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
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .navbar a.active {
            background-color: #666;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="login.php" class="active">Đăng nhập</a>
        <a href="register.php">Đăng ký</a>
    </div>
    <div class="form">
        <h2>Đăng nhập</h2>
        <?php if ($error_message) echo "<p class='error'>$error_message</p>"; ?>

        <form id="loginForm" action="login.php" method="POST">
            <input type="text" id="name" name="name" placeholder="Tên đăng nhập" required>
            <span id="nameError" class="error"></span><br>

            <input type="password" id="password" name="password" placeholder="Mật khẩu" required>
            <span id="passwordError" class="error"></span><br>

            <input class="submit" type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>
