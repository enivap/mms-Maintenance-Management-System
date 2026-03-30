<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Maintenance Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <div class="top-bar">
        <a href="dashboard.php" class="system-name">MMS | Maintenance Management System</a>
        <div class="user-box">
            <a class="logout" href="auth/login.php">Login</a>
        </div>
    </div>
    <main class="main-container">
        <section class="left-content">
            <h1>ระบบแจ้งซ่อมและติดตามงานซ่อม <br> ภายในองค์กร</h1>
            <p>
                Maintenance Management System (MMS) <br>
                ช่วยให้การแจ้งซ่อม ติดตามสถานะ และตรวจสอบคุณภาพงานซ่อม<br>
                เป็นไปอย่างมีระบบ โปร่งใส และตรวจสอบย้อนหลังได้
            </p>
            <ul class="features">
                <li>ติดตามสถานะงานซ่อมแบบ Real-time</li>
                <li>รองรับการตรวจสอบโดย QS / QA</li>
                <li>เก็บประวัติงานซ่อมอย่างเป็นระบบ</li>
            </ul>
        </section>
        <section class="right-content">
            <h2 class="role-title">Choose Your Access</h2>
            <div class="role-grid">
                <div class="role-card admin">
                    <div class="role-header">
                        <h3>Admin</h3>
                        <span>System Control</span>
                    </div>
                    <p>จัดการผู้ใช้งาน และควบคุมระบบทั้งหมด</p>
                    <a href="auth/login.php" class="role-login">Login →</a>
                </div>
                <div class="role-card qa">
                    <div class="role-header">
                        <h3>QS / QA</h3>
                        <span>Inspection</span>
                    </div>
                    <p>ตรวจสอบคุณภาพงานซ่อม และให้ผลการตรวจ</p>
                    <a href="auth/login.php" class="role-login">Login →</a>
                </div>
                <div class="role-card tech">
                    <div class="role-header">
                        <h3>Technician</h3>
                        <span>Repair</span>
                    </div>
                    <p>ดำเนินการซ่อม และบันทึกผลการซ่อม</p>
                    <a href="auth/login.php" class="role-login">Login →</a>
                </div>
                <div class="role-card user">
                    <div class="role-header">
                        <h3>Informant</h3>
                        <span>Request</span>
                    </div>
                    <p>แจ้งซ่อม และติดตามสถานะงานของตนเอง</p>
                    <a href="auth/login.php" class="role-login">Login →</a>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer">
        © 2026 Maintenance Management System (Internal Use Only) <br>
        Developed by <strong>Kitsanarat Ongsatta</strong>
    </footer>

</body>

</html>