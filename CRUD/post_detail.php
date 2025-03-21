<?php
session_start();
require "../includes/db.php";
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"]; // Lấy username từ session

if (!isset($_GET["id"])) {
    die("Bài viết không tồn tại!");
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment_content"])) {
    $post_id = $_POST["post_id"];
    $user_id = $_SESSION["user_id"];
    $comment_content = trim($_POST["comment_content"]);

    if (empty($comment_content)) {
        echo json_encode(["status" => "error", "message" => "Nội dung bình luận không được để trống."]);
        exit();
    }

    $sql = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $comment_content);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        $username = $_SESSION["username"];
        $created_at = date("d/m/Y H:i");
        echo json_encode(["status" => "success", "comment_id" => $comment_id, "username" => $username, "content" => htmlspecialchars($comment_content), "created_at" => $created_at]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi thêm bình luận."]);
    }
    exit();
}


$post_id = $_GET["id"];
$sql = "SELECT posts.*, user.username, danhmuc.ten_danhmuc as category_name
        FROM posts 
        JOIN user ON posts.user_id = user.id 
        JOIN danhmuc ON posts.category_id = danhmuc.id
        WHERE posts.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Bài viết không tồn tại!");
}

// Truy vấn số lượng cảm xúc
$sql_reactions = "SELECT reaction_type, COUNT(*) as count FROM post_reactions WHERE post_id = ? GROUP BY reaction_type";
$stmt = $conn->prepare($sql_reactions);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result_reactions = $stmt->get_result();

$reactions = ["like" => 0, "heart" => 0, "haha" => 0, "sad" => 0, "angry" => 0];
while ($row = $result_reactions->fetch_assoc()) {
    $reactions[$row['reaction_type']] = $row['count'];
}

// Nếu có gửi dữ liệu cảm xúc
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reaction_type"])) {
    $user_id = $_SESSION["user_id"];
    $reaction_type = $_POST["reaction_type"];

    // Kiểm tra xem người dùng đã bày tỏ cảm xúc trước đó chưa
    $check_sql = "SELECT reaction_type FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Nếu đã bày tỏ cảm xúc trước đó và chọn lại đúng cảm xúc đó => Xóa phản ứng
        if ($row['reaction_type'] == $reaction_type) {
            $delete_sql = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("ii", $post_id, $user_id);
            $stmt->execute();
        } else {
            // Nếu đã bày tỏ cảm xúc nhưng khác loại => Cập nhật loại cảm xúc
            $update_sql = "UPDATE post_reactions SET reaction_type = ? WHERE post_id = ? AND user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sii", $reaction_type, $post_id, $user_id);
            $stmt->execute();
        }
    } else {
        // Nếu chưa bày tỏ cảm xúc => Thêm mới
        $insert_sql = "INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iis", $post_id, $user_id, $reaction_type);
        $stmt->execute();
    }
}



// Lấy danh sách bình luận
$sql_comments = "SELECT comments.*, user.username 
                 FROM comments 
                 JOIN user ON comments.user_id = user.id 
                 WHERE comments.post_id = ? 
                 ORDER BY comments.created_at DESC";
$stmt = $conn->prepare($sql_comments);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .header { padding: 0.5px; text-align: center; background: #1abc9c; color: white; }

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

        .note {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .h1 {
            font-size: 24px;
            margin-bottom: 20px;
            line-height: 1.4;
            color: #333;
        }

        img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 12px;
            line-height: 1.6;
            color: #444;
        }

        strong {
            color: #333;
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        .like {
            display: inline-block;
            margin-top: 20px;
            color: #009688;
            text-decoration: none;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .post-title {
            font-size: 24px;
            color: #333;
        }

        .post-meta {
            font-size: 14px;
            color: gray;
            margin-bottom: 10px;
        }

        .post-content {
            line-height: 1.6;
        }

        .reactions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .reactions button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 25px;
        }

        .comments-section {
            margin-top: 20px;
        }

        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .comment-meta {
            font-size: 12px;
            color: gray;
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

    <h1>Bài viết</h1>
    <div class="container">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
            <span>Danh mục: <?php echo htmlspecialchars($post['category_name']); ?></span> |
            <span>Người đăng: <?php echo htmlspecialchars($post['username']); ?></span> |
            <span>Ngày đăng: <?php echo date("d/m/Y H:i", strtotime($post['created_at'])); ?></span>
        </div>
        <img src="../<?php echo $post['banner']; ?>" alt="Banner" style="width:100%; height:auto;">
        <p class="post-content"><?php echo nl2br($post['content']); ?></p>

        <div class="reactions">
            <form method="post">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <button type="submit" name="reaction_type" value="like">👍 <?php echo $reactions['like']; ?></button>
                <button type="submit" name="reaction_type" value="heart">❤️ <?php echo $reactions['heart']; ?></button>
                <button type="submit" name="reaction_type" value="haha">😂 <?php echo $reactions['haha']; ?></button>
                <button type="submit" name="reaction_type" value="sad">😢 <?php echo $reactions['sad']; ?></button>
                <button type="submit" name="reaction_type" value="angry">😡 <?php echo $reactions['angry']; ?></button>
                <div style="margin-top: 20px;">
    <a href="../report_post.php?id=<?php echo $post_id; ?>" style="display: inline-block; padding: 8px 12px; background: red; color: white; text-decoration: none; border-radius: 5px;">🚨 Báo cáo bài viết</a>
</div>
            </form>
        </div>

        <div class="comments-section">
            <h2>Bình luận</h2>
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    <div class="comment-meta">Bởi <?php echo htmlspecialchars($comment['username']); ?> - <?php echo date("d/m/Y H:i", strtotime($comment['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>

            <form id="commentForm" method="post">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <textarea name="comment_content" required placeholder="Nhập bình luận..."></textarea>
                <button type="submit">Bình luận</button>
            </form>

        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("form#commentForm").submit(function(e) {
                e.preventDefault(); // Ngăn form gửi bình thường
                $.ajax({
                    url: window.location.href, // Gửi bình luận đến cùng trang
                    type: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            let newComment = `<div class="comment">
                <p>${response.content}</p>
                <div class="comment-meta">Bởi ${response.username} - ${response.created_at}</div>
            </div>`;
                            $(".comments-section").append(newComment);
                            $("textarea[name='comment_content']").val(""); // Xóa nội dung nhập
                        } else {
                            alert(response.message);
                        }
                    }
                });

            });
        });
    </script>
</body>

</html>