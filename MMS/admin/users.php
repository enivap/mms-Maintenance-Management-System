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
$users = $conn->query("
    SELECT 
        u.id,
        u.username,
        u.fullname,
        u.department,
        r.role_name,
        u.role_id
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้</title>
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
            <div class="table-box">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3>จัดการผู้ใช้ระบบ</h3>
                    <button class="btn-primary" onclick="openAddUser()">เพิ่มผู้ใช้</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อ-นามสกุล</th>
                            <th>Username</th>
                            <th>แผนก</th>
                            <th>บทบาท</th>
                            <th style="text-align:center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td>
                                    <span class="badge role"><?= htmlspecialchars($row['role_name']) ?></span>
                                </td>
                                <td class="action-cell">
                                    <button class="icon-btn edit"
                                        onclick="openEditUser(<?= $row['id'] ?>)">✏️</button>

                                    <?php if ($row['id'] != $user_id): ?>
                                        <a class="icon-btn delete"
                                            href="user_delete.php?id=<?= $row['id'] ?>"
                                            onclick="return confirm('ยืนยันลบผู้ใช้นี้?')">🗑️</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <div class="moda" id="addUserModal">
        <div class="moda-box">
            <h3>เพิ่มผู้ใช้</h3>
            <form action="user_add_process.php" method="post">
                <label>Username</label>
                <input type="text" name="username" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>ชื่อ-นามสกุล</label>
                <input type="text" name="fullname" required>
                <label>แผนก</label>
                <input type="text" name="department" required>
                <label>บทบาท</label>
                <select name="role_id" required>
                    <option value="1">Admin</option>
                    <option value="2">QA / QS</option>
                    <option value="3">Technician</option>
                    <option value="4">Informant</option>
                </select>
                <div class="moda-actions">
                    <button class="btn-primary">บันทึก</button>
                    <button type="button" class="btn-cancel" onclick="closeAddUser()">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>
    <div class="moda" id="editUserModal">
        <div class="moda-box">
            <h3>แก้ไขผู้ใช้</h3>
            <form action="user_edit_process.php" method="post">
                <input type="hidden" name="id" id="edit_id">
                <label>Username</label>
                <input type="text" id="edit_username" readonly>
                <label>ชื่อ-นามสกุล</label>
                <input type="text" name="fullname" id="edit_fullname" required>
                <label>แผนก</label>
                <input type="text" name="department" id="edit_department" required>
                <label>บทบาท</label>
                <select name="role_id" id="edit_role">
                    <option value="1">Admin</option>
                    <option value="2">QA / QS</option>
                    <option value="3">Technician</option>
                    <option value="4">Informant</option>
                </select>
                <label>รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)</label>
                <input type="password" name="password" placeholder="********">
                <div class="modal-actions">
                    <button class="btn-primary">บันทึก</button>
                    <button type="button" class="btn-cancel" onclick="closeEditUser()">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function openAddUser() {
            document.getElementById('addUserModal').classList.add('active');
        }

        function closeAddUser() {
            document.getElementById('addUserModal').classList.remove('active');
        }

        function openEditUser(id) {
            fetch('user_get.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_username').value = data.username;
                    document.getElementById('edit_fullname').value = data.fullname;
                    document.getElementById('edit_department').value = data.department;
                    document.getElementById('edit_role').value = data.role_id;
                    document.getElementById('editUserModal').classList.add('active');
                });
        }

        function closeEditUser() {
            document.getElementById('editUserModal').classList.remove('active');
        }
    </script>
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
</body>

</html>