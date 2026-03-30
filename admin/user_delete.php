<?php
session_start();
require '../config/connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$login_user_id = $_SESSION['user_id'];
$delete_id = intval($_GET['id'] ?? 0);
if ($delete_id === $login_user_id) {
    header("Location: users.php?error=self_delete");
    exit;
}
$check = $conn->query("SELECT id FROM users WHERE id = $delete_id");
if ($check->num_rows === 0) {
    header("Location: users.php?error=not_found");
    exit;
}
$conn->query("DELETE FROM users WHERE id = $delete_id");
header("Location: users.php?success=deleted");
exit;
