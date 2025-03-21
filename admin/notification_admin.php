<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"]) ) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Kết nối database

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Xử lý cập nhật trạng thái báo cáo
if (isset($_POST['update_status'])) {
    $report_id = $_POST['report_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE reports SET status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $report_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Cập nhật trạng thái báo cáo thành công!";
    } else {
        $error_message = "Lỗi khi cập nhật: " . $conn->error;
    }
    $update_stmt->close();
}

// Lọc báo cáo theo trạng thái
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "";

if ($status_filter !== '') {
    $where_clause = "WHERE r.status = '$status_filter'";
}

// Lấy danh sách báo cáo với thông tin người dùng và bài viết
$sql = "
    SELECT r.*, u.username as reporter_name, p.title as post_title 
    FROM reports r
    LEFT JOIN user u ON r.user_id = u.id
    LEFT JOIN posts p ON r.post_id = p.id
    $where_clause
    ORDER BY 
        CASE 
            WHEN r.status = 'pending' THEN 1
            WHEN r.status = 'reviewed' THEN 2
            WHEN r.status = 'resolved' THEN 3
        END,
        r.created_at DESC
";

$result = $conn->query($sql);

// Lấy danh mục để hiển thị menu
$category_sql = "SELECT id, ten_danhmuc FROM danhmuc";
$category_result = $conn->query($category_sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý báo cáo</title>
    <style>
body { font-family: Arial, Helvetica, sans-serif; margin: 0; }
        .header { padding: 0.5px; text-align: center; background: #1abc9c; color: white; }
        .header h1 { font-size: 40px; }
        .navbar { overflow: hidden; background-color: #333; position: sticky; top: 0; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a.right { float: right; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .navbar a.active { background-color: #666; color: white; }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-button {
            background-color: #f1f1f1;
            border: none;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        .filter-button.active {
            background-color: #1abc9c;
            color: white;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .report-table th {
            background-color: #f2f2f2;
        }

        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .report-table tr:hover {
            background-color: #f1f1f1;
        }

        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }

        .status-reviewed {
            color: #2196F3;
            font-weight: bold;
        }

        .status-resolved {
            color: #4CAF50;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .review-btn {
            background-color: #2196F3;
        }

        .resolve-btn {
            background-color: #4CAF50;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }

        .alert-danger {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }

        .post-link {
            color: #1abc9c;
            text-decoration: none;
        }

        .post-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
<div class="header">
    <h1>NewsHub</h1>
    <p>Trung tâm tin tức, nơi mọi người trao đổi thông tin</p>
</div>

<div class="navbar">
    <a href="index_admin.php" class="active">Trang chủ Admin</a>
    <a href="my_category.php">Danh mục</a>
    <a href="notification_admin.php">Thông báo</a>
    <a href="admin_post.php">Quản lý bài viết</a>
    <a href="delete_user.php">Quản lý người dùng</a>
    <a href="report_admin.php">Report</a>
    <a href="../logout.php" class="right">Đăng xuất</a>
    <a href="" class="right">Xin chào, <?php echo htmlspecialchars($username); ?>!</a>
    <a href="../index.php" class="right">Trở về Website</a>
</div>

    <div class="container">
        <h2>Danh sách báo cáo</h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="filter-container">
            <div>
                <a href="notification_admin.php" class="filter-button <?php echo $status_filter === '' ? 'active' : ''; ?>">Tất cả</a>
                <a href="notification_admin.php?status=pending" class="filter-button <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Chờ xử lý</a>
                <a href="notification_admin.php?status=reviewed" class="filter-button <?php echo $status_filter === 'reviewed' ? 'active' : ''; ?>">Đang xem xét</a>
                <a href="notification_admin.php?status=resolved" class="filter-button <?php echo $status_filter === 'resolved' ? 'active' : ''; ?>">Đã giải quyết</a>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người báo cáo</th>
                        <th>Bài viết</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Cập nhật</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <a href="../profile_user.php?id=<?php echo $row['user_id']; ?>">
                                    <?php echo htmlspecialchars($row['reporter_name']); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($row['post_id']): ?>
                                    <a href="admin_post_detail.php?id=<?php echo $row['post_id']; ?>" class="post-link">
                                        <?php echo htmlspecialchars($row['post_title']); ?>
                                    </a>
                                <?php else: ?>
                                    <em>Không có bài viết</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td>
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php 
                                        switch($row['status']) {
                                            case 'pending':
                                                echo 'Chờ xử lý';
                                                break;
                                            case 'reviewed':
                                                echo 'Đang xem xét';
                                                break;
                                            case 'resolved':
                                                echo 'Đã giải quyết';
                                                break;
                                        }
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['updated_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="new_status" value="reviewed">
                                            <button type="submit" name="update_status" class="review-btn">Xem xét</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['status'] === 'pending' || $row['status'] === 'reviewed'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="new_status" value="resolved">
                                            <button type="submit" name="update_status" class="resolve-btn">Giải quyết</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Không có báo cáo nào.</p>
        <?php endif; ?>
    </div>
</body>

</html>