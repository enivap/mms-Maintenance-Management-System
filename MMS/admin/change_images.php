<?php
session_start();
require '../config/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: dashboard.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user = $conn->query("
    SELECT fullname, department 
    FROM users 
    WHERE id = $user_id
")->fetch_assoc();
function countStatus($conn, $status)
{
    $q = $conn->query("SELECT COUNT(*) total FROM repair_requests WHERE status='$status'");
    return $q->fetch_assoc()['total'];
}
$pending    = countStatus($conn, 'pending');
$progress   = countStatus($conn, 'in_progress');
$completed  = countStatus($conn, 'completed');
$needfix    = countStatus($conn, 'need_fix');
$closed     = countStatus($conn, 'closed');
$total      = $pending + $progress + $completed + $needfix + $closed;
$images = [
    'login'       => 'login.jpg',
    'admin'       => 'admin.jpg',
    'technician'  => 'technician.jpg',
    'qaqc'        => 'qaqc.jpg',
    'informant'   => 'informant.jpg'
];
?>
<!DOCTYPE html>
<html lang="th">

<head>

    <head>
        <meta charset="UTF-8">
        <title>เปลี่ยนรูปภาพระบบ</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../style/style.css">
        <style>

        </style>
    </head>

<body>
    <div class="top-bar">
        <a href="dashboard.php" class="system-name">MMS | Maintenance Management System</a>
        <div class="user-box">
            <div>
                <strong><?= htmlspecialchars($user['fullname']) ?></strong><br>
                <small><?= htmlspecialchars($user['department']) ?></small>
            </div>
            <a class="logout" href="../auth/logout.php">ออกจากระบบ</a>
        </div>
    </div>
    <div class="layout">
        <aside class="sidebar">
            <br><br>
            <div class="profile">
                <img src="../uploads/others/admin.jpg" class="avatar" alt="">
                <strong><?= htmlspecialchars($user['fullname']) ?></strong>
                <small><?= htmlspecialchars($user['department']) ?></small>
            </div>
            <nav class="menu">
                <h4>งานซ่อมทั้งหมด</h4>
                <a href="all_repair_request.php" class="<?= $current_page == 'all_repair_request.php' ? 'active' : '' ?>">
                    งานซ่อมทั้งหมด
                </a>
                <a href="pending.php" class="<?= $current_page == 'pending.php' ? 'active' : '' ?>">
                    รอดำเนินการ
                    <?php if ($pending > 0): ?>
                        <span class="notify"><?= $pending ?></span>
                    <?php endif; ?>
                </a>
                <a href="progress.php" class="<?= $current_page == 'progress.php' ? 'active' : '' ?>">
                    กำลังดำเนินการ
                    <?php if ($progress > 0): ?>
                        <span class="notify"><?= $progress ?></span>
                    <?php endif; ?>
                </a>
                <a href="completed.php" class="<?= $current_page == 'completed.php' ? 'active' : '' ?>">
                    ดำเนินการสำเร็จ
                </a>
                <a href="needfix.php" class="<?= $current_page == 'needfix.php' ? 'active' : '' ?>">
                    ที่ต้องแก้ไข
                    <?php if ($needfix > 0): ?>
                        <span class="notify"><?= $needfix ?></span>
                    <?php endif; ?>
                </a>
                <h4>งานตรวจงานซ่อม</h4>
                <a href="inspect.php" class="<?= $current_page == 'inspect.php' ? 'active' : '' ?>">งานตรวจงานซ่อม
                    <?php if ($completed > 0): ?>
                        <span class="notify"><?= $completed ?></span>
                    <?php endif; ?>
                </a>
                <h4>ประวัติงาน</h4>
                <a href="closed.php" class="<?= $current_page == 'closed.php' ? 'active' : '' ?>">งานที่ปิดแล้ว</a>
                <h4>สำหรับผู้ดูแลระบบ</h4>
                <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>">จัดการผู้ใช้</a>
                <a href="change_images.php" class="<?= $current_page == 'change_images.php' ? 'active' : '' ?>">เปลี่ยนรูปภาพระบบ</a>
            </nav>
        </aside>
        <main class="content">

            <div class="img-grid">
                <?php foreach ($images as $key => $file): ?>
                    <div class="img-card">
                        <h4><?= ucfirst($key) ?></h4>
                        <img src="../uploads/others/<?= $file ?>?t=<?= time() ?>" alt="">
                        <form action="change_images_process.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="target" value="<?= $file ?>">
                            <input type="file" name="image" accept="image/*" required>
                            <br><br>
                            <button class="btn-start">เปลี่ยนรูป</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

</body>

</html>