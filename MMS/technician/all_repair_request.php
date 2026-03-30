<?php
session_start();
require '../config/connect.php';
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header("Location: ..auth/login.php");
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

$q = trim($_GET['q'] ?? '');
$where = '';

if ($q !== '') {
    $safe_q = $conn->real_escape_string($q);
    $where = "WHERE request_book_no LIKE '%$safe_q%'
              OR request_no LIKE '%$safe_q%'";
}

$latest_repairs = $conn->query("
    SELECT id, request_book_no, request_no, location, job_type, status, created_at, due_date
    FROM repair_requests
    $where
    ORDER BY created_at DESC
");
$status_th = [
    'pending'     => 'รอดำเนินการ',
    'in_progress' => 'กำลังดำเนินงาน',
    'completed'   => 'ซ่อมเสร็จ',
    'need_fix'    => 'ต้องแก้ไขเพิ่มเติม',
    'closed'      => 'ปิดงานแล้ว'
];
$has_machine_th = [
    'yes' => 'มีเครื่องจักร',
    'no'  => 'ไม่มีเครื่องจักร'
];
$machine_status_th = [
    'working'     => 'เครื่องจักรกำลังทำงาน',
    'not_working' => 'ไม่สามารถใช้งานได้'
];
$job_type_th = [
    'structure'  => 'โครงสร้างอาคาร',
    'electric' => 'ระบบไฟฟ้า, แสงสว่าง',
    'machine' => 'เครื่องจักรอุปกรณ์',
    'facility'   => 'สิ่งอำนวยความสะดวก',
    'pest'         => 'Pest Control',
    'other'      => 'งานอื่น ๆ'
];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
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
                <img src="../uploads/others/technician.jpg" class="avatar" alt="">
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
                <h4>ประวัติงาน</h4>
                <a href="closed.php" class="<?= $current_page == 'closed.php' ? 'active' : '' ?>">งานที่ปิดแล้ว</a>
            </nav>
        </aside>
        <main class="content">
            <div class="table-box">
                <h3>งานแจ้งซ่อมล่าสุด</h3>
                <form method="get" style="margin-bottom:15px; display:flex; gap:10px;">
                    <input type="text"
                        name="q"
                        placeholder="ค้นหาเลขที่ใบแจ้งซ่อม"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                        style="
            padding:8px 14px;
            border-radius:999px;
            border:1px solid #ddd;
            width:260px;
            outline:none;
        ">
                    <button type="submit" class="btn-view">ค้นหา</button>

                    <?php if (!empty($_GET['q'])): ?>
                        <a href="all_repair_request.php" class="btn-view">ล้าง</a>
                    <?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>เลขที่ใบแจ้ง</th>
                            <th>สถานที่</th>
                            <th>ประเภทงาน</th>
                            <th>วันที่แจ้ง</th>
                            <th>กำหนดแล้วเสร็จ</th>
                            <th>สถานะ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($latest_repairs->num_rows): ?>
                            <?php while ($row = $latest_repairs->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= $row['request_book_no'] ?>/<?= $row['request_no'] ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td><span class="job_type <?= $row['job_type'] ?>">
                                            <?= $job_type_th[$row['job_type']] ?? $row['job_type'] ?>
                                        </span></td>
                                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['due_date'] ? date('d/m/Y', strtotime($row['due_date'])) : '-' ?></td>
                                    <td>
                                        <span class="status <?= $row['status'] ?>">
                                            <?= $status_th[$row['status']] ?? $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <a href="../auth/repair_detail.php?id=<?= $row['id'] ?>"
                                            target="_blank"
                                            class="btn-view">
                                            🔍 ดูรายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px;">
                                    ยังไม่มีประวัติการแจ้งซ่อม
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

        </main>
    </div>

</body>

</html>