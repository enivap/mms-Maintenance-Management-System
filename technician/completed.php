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

$repairs = $conn->query("
    SELECT id, request_book_no, request_no, location, job_type, created_at, due_date
    FROM repair_requests
    WHERE status = 'completed'
    ORDER BY created_at ASC
");
$job_type_th = [
    'structure' => 'โครงสร้างอาคาร',
    'electric'  => 'ระบบไฟฟ้า',
    'machine'   => 'เครื่องจักร',
    'facility'  => 'สิ่งอำนวยความสะดวก',
    'pest'      => 'Pest Control',
    'other'     => 'งานอื่น ๆ'
];

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>งานดำเนินการสำเร็จ</title>
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
                <h3>งานดำเนินการสำเร็จ (Completed)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>เลขที่ใบแจ้ง</th>
                            <th>สถานที่</th>
                            <th>ประเภทงาน</th>
                            <th>วันที่แจ้ง</th>
                            <th>กำหนดแล้วเสร็จ</th>
                            <th>สถานะ</th>
                            <th>วันที่แล้วเสร็จ</th>
                            <th style="text-align:center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($repairs->num_rows): ?>
                            <?php while ($row = $repairs->fetch_assoc()): ?>
                                <tr class="status-in_progress">
                                    <td><?= $row['request_book_no'] ?>/<?= $row['request_no'] ?></td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td>
                                        <span class="job_type <?= $row['job_type'] ?>">
                                            <?= $job_type_th[$row['job_type']] ?? $row['job_type'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['due_date'] ? date('d/m/Y', strtotime($row['due_date'])) : '-' ?></td>
                                    <td>
                                        <span class="status completed">ดำเนินการสำเร็จ</span>
                                    </td>
                                    <td></td>
                                    <td class="action-cell">
                                        <a href="../auth/repair_detail.php?id=<?= $row['id'] ?>"
                                            target="_blank"
                                            class="btn-view">
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
                                <td colspan="6" style="text-align:center; padding:24px;">
                                    ไม่มีงานรอดำเนินการ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal" id="repairModal">
                <div class="modal-content">
                    <h3>บันทึกผลการซ่อม</h3>
                    <form action="save_repair_log.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="repair_request_id" id="repair_request_id">
                        <input type="hidden" name="technician_id" value="<?= $user_id ?>">
                        <label>ผลการซ่อม</label>
                        <select name="repair_result" required>
                            <option value="">-- เลือกผลการซ่อม --</option>
                            <option value="success">ซ่อมสำเร็จ</option>
                            <option value="cannot_fix">ไม่สามารถซ่อมได้</option>
                        </select>
                        <label>รายละเอียดการซ่อม</label>
                        <textarea name="repair_detail" required></textarea>
                        <<label>วันที่ซ่อมเสร็จ</label>
                            <input
                                type="date"
                                name="repair_completed_date"
                                required
                                value="<?= date('Y-m-d') ?>">
                            <label>รูปหลังซ่อมเสร็จ</label>
                            <input type="file"
                                name="repair_images[]"
                                multiple
                                accept="image/*">
                            <div class="modal-actions">
                                <button type="button" class="btn-cancel" onclick="closeRepairModal()">ยกเลิก</button>
                                <button type="submit" class="btn-save">บันทึก</button>
                            </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        function openRepairModal(repairId) {
            document.getElementById('repair_request_id').value = repairId;
            document.getElementById('repairModal').classList.add('show');
        }

        function closeRepairModal() {
            document.getElementById('repairModal').classList.remove('show');
        }
    </script>
</body>

</html>