<?php
include 'includes/db.php'; // Kết nối database

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $email = trim($_POST["email"]);
    $date_of_birth = trim($_POST["date_of_birth"]);
    $gender = trim($_POST["gender"]);

    // Kiểm tra thông tin không được để trống
    if (empty($username) || empty($password) || empty($email) || empty($date_of_birth) || empty($gender)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    }
    // Kiểm tra độ dài mật khẩu (tối thiểu 6 ký tự)
    elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự!";
    }
    // Kiểm tra định dạng email hợp lệ
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Kiểm tra username hoặc email đã tồn tại chưa
        $check_user = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $check_user->bind_param("ss", $username, $email);
        $check_user->execute();
        $check_user->store_result();

        if ($check_user->num_rows > 0) {
            $error_message = "Tên đăng nhập hoặc email đã tồn tại!";
        } else {
            // Thêm vào database
            $stmt = $conn->prepare("INSERT INTO user (username, password, email, date_of_birth, gender) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashed_password, $email, $date_of_birth, $gender);

            if ($stmt->execute()) {
                $success_message = "Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a>";
            } else {
                $error_message = "Lỗi: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_user->close();
    }
    $conn->close();
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Đăng ký</title>
    <style>
        .error {
            color: red;
        }

        .success {
            color: green;
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
            background-color: whitesmoke;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
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

        .form-group {
            width: 105%;
            margin-top: 15px;
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Job Easy</h1>
        <p>Giới thiệu việc làm nhanh chóng và chất lượng</p>
    </div>
    <div class="navbar">
        <a href="login.php">Đăng nhập</a>
        <a href="register.php" class="active">Đăng ký</a>
    </div>
    <div class="form">
        <h2>Đăng ký tài khoản</h2>
        <?php if ($success_message) echo "<p class='success'>$success_message</p>"; ?>
        <?php if ($error_message) echo "<p class='error'>$error_message</p>"; ?>

        <form id="registerForm" action="register.php" method="POST" onsubmit="return validateForm()">
            <input type="text" id="username" name="username" placeholder="Tên đăng nhập" required>
            <span id="usernameError" class="error"></span><br>

            <input type="password" id="password" name="password" placeholder="Mật khẩu" required>
            <span id="passwordError" class="error"></span><br>

            <input type="email" id="email" name="email" placeholder="Email" required>
            <span id="emailError" class="error"></span><br>



            <input type="date" id="date_of_birth" name="date_of_birth" placeholder="Ngày sinh" required>


            <!-- Trường Giới tính -->

            <div class="form-group">
                
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Chọn giới tính</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>


            <input class="submit" type="submit" value="Đăng ký">
        </form>

    </div>

    <script>
        function validateForm() {
            let username = document.getElementById("username").value.trim();
            let password = document.getElementById("password").value.trim();
            let email = document.getElementById("email").value.trim();
            let date_of_birth = document.getElementById("date_of_birth").value.trim();
            let gender = document.getElementById("gender").value.trim();

            if (username === "" || password === "" || email === "" || date_of_birth === "" || gender === "") {
                alert("Vui lòng điền đầy đủ thông tin!");
                return false;
            }
            if (password.length < 6) {
                alert("Mật khẩu phải có ít nhất 6 ký tự!");
                return false;
            }
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Email không hợp lệ!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>