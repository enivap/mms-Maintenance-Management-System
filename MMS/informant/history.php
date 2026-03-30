<?php
session_start();
require '../config/connect.php';
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$user = $conn->query("
    SELECT fullname, department
    FROM users
    WHERE id = $user_id
")->fetch_assoc();

$repairs = $conn->query("
    SELECT
        id,
        request_book_no,
        request_no,
        location,
        job_type,
        status,
        due_date,
        created_at
    FROM repair_requests
    WHERE requester_id = $user_id
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
    <title>Repair Request History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
    <div class="top-bar">
        <a href="dashboard.php" class="system-name">
            MMS | Maintenance Management System
        </a>
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
                <a href="history.php"
                    class="<?= $current_page == 'history.php' ? 'active' : '' ?>">
                    ประวัติการแจ้งซ่อมของฉัน
                </a>
            </nav>
        </aside>
        <main class="content">
            <div class="table-box">
                <h3>ประวัติการแจ้งซ่อมของฉัน</h3>
                <table>
                    <thead>
                        <tr>
                            <th>เลขที่ใบแจ้งซ่อม</th>
                            <th>สถานที่</th>
                            <th>ประเภทงาน</th>
                            <th>วันที่แจ้ง</th>
                            <th>กำหนดแล้วเสร็จ</th>
                            <th>สถานะ</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($repairs->num_rows > 0): ?>
                            <?php while ($row = $repairs->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row['request_book_no']) ?> /
                                        <?= htmlspecialchars($row['request_no']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td> <span class="job_type <?= $row['job_type'] ?>">
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
                                            style="color:#1976d2; font-weight:600;" class="btn-view">
                                            🔍 ดูรายละเอียด
                                        </a>
                                        <a href="../auth/repair_detail_print.php?id=<?= $row['id'] ?>"
                                            target="_blank"
                                            class="btn-print">
                                            🖨️
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px;">
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