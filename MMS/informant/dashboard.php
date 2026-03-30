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

$summary = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status='pending') AS pending,
        SUM(status='in_progress') AS in_progress,
        SUM(status='need_fix') AS need_fix,
        SUM(status='closed') AS closed,
        SUM(status='completed') AS completed
    FROM repair_requests
    WHERE requester_id = $user_id
")->fetch_assoc();

$latest_repairs = $conn->query("
    SELECT id, request_book_no, request_no, location, job_type, status, created_at, due_date
    FROM repair_requests
    WHERE requester_id = $user_id
    ORDER BY created_at DESC
    LIMIT 5
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
    'structure'  => 'โครงสร้างอาคาร (พื้น, ผนัง, เพดาน)',
    'electric' => 'ระบบไฟฟ้า, แสงสว่าง (เช่น หลอดไฟ, ฝาครอบหลอดไฟ)',
    'machine' => 'เครื่องจักรอุปกรณ์ (เช่น ท่อ, แท้งค์, สายยาง)',
    'facility'   => 'สิ่งอำนวยความสะดวก (เช่น โต๊ะ, เก้าอี้, ก๊อกน้ำ)',
    'pest'         => 'Pest Control (เช่น เครื่องดักแมลง, ม่านพลาสติก, ประตูชำรุด, ตะแกรงปิดร่องระบายน้ำ)',
    'other'      => 'งานอื่น ๆ'
];

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Informant Dashboard</title>
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
                <img src="../uploads/others/informant.jpg" class="avatar" alt="">
                <strong><?= htmlspecialchars($user['fullname']) ?></strong>
                <small><?= htmlspecialchars($user['department']) ?></small>
            </div>
            <nav class="menu">
                <a href="create_repair.php"
                    class="<?= $current_page == 'create_repair.php' ? 'active' : '' ?>">
                    เพิ่มการแจ้งซ่อมใหม่
                </a>
                <a href="history.php" class="<?= $current_page == 'history.php' ? 'active' : '' ?>">ประวัติการแจ้งซ่อมของฉัน</a>
            </nav>

        </aside>
        <main class="content">

            <div class="cards">
                <div class="card">
                    <h3>แจ้งซ่อมทั้งหมด</h3>
                    <div class="num"><?= $summary['total'] ?></div>
                </div>
                <div class="card">
                    <h3>รอดำเนินการ</h3>
                    <div class="num"><?= $summary['pending'] ?></div>
                </div>
                <div class="card">
                    <h3>กำลังดำเนินงาน</h3>
                    <div class="num"><?= $summary['in_progress'] ?></div>
                </div>
                <div class="card">
                    <h3>ดำเนินการสำเร็จ</h3>
                    <div class="num"><?= $summary['completed'] ?></div>
                </div>
                <div class="card">
                    <h3>ต้องแก้ไข</h3>
                    <div class="num"><?= $summary['need_fix'] ?></div>
                </div>
                <div class="card">
                    <h3>ปิดงานแล้ว</h3>
                    <div class="num"><?= $summary['closed'] ?></div>
                </div>
            </div>
            <div class="table-box">
                <h3>งานแจ้งซ่อมล่าสุดของฉัน</h3>
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
                                    <td>
                                        <?= $row['due_date']
                                            ? date('d/m/Y', strtotime($row['due_date']))
                                            : '-' ?>
                                    </td>
                                    <td>
                                        <span class="status <?= $row['status'] ?>">
                                            <?= $status_th[$row['status']] ?? $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../auth/repair_detail.php?id=<?= $row['id'] ?>"
                                            target="_blank"
                                            style="color:#1976d2; font-weight:600;">
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
            </div>
        </main>
    </div>

</body>

</html>