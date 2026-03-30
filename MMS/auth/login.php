<?php
session_start();
require_once '../config/connect.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $sql = "SELECT id, fullname, role_id FROM users 
                WHERE username = ? AND password = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $role_id = (int)$user['role_id'];
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role_id']  = $role_id;
            $_SESSION['fullname'] = $user['fullname'];
            switch ($role_id) {
                case 1:
                    header("Location: ../admin/dashboard.php");
                    break;
                case 2:
                    header("Location: ../qa/dashboard.php");
                    break;
                case 3:
                    header("Location: ../technician/dashboard.php");
                    break;
                case 4:
                    header("Location: ../informant/dashboard.php");
                    break;
                default:
                    $error = "ไม่พบสิทธิ์ผู้ใช้งาน";
            }
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบ';
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <img src="" alt="">
    <meta charset="UTF-8">
    <title>Login | Maintenance Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <a href="../index.php" class="btn-back-page">← ย้อนกลับ</a>
    <div class="login-container">
        <div class="login-card">
            <div class="login-card-left">
                <h1>MMS Login</h1>
                <p>Maintenance Management System</p>
                <?php if ($error): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter password">
                    </div>
                    <button type="submit" class="btn-login">
                        Login →
                    </button>
                </form>
            </div>
            <div class="login-card-right">
                <img src="../uploads/others/login.jpg" alt="Login Illustration">
            </div>
        </div>
</body>

</html>