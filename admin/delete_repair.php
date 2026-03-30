<?php
session_start();
require '../config/connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pending.php");
    exit;
}
$repair_id = (int)$_POST['repair_id'];
$conn->query("
    DELETE rri FROM repair_result_images rri
    JOIN repair_logs rl ON rri.repair_log_id = rl.id
    WHERE rl.repair_request_id = $repair_id
");
$conn->query("
    DELETE FROM repair_logs
    WHERE repair_request_id = $repair_id
");
$conn->query("
    DELETE FROM qa_inspection_logs
    WHERE repair_request_id = $repair_id
");
$conn->query("
    DELETE FROM repair_request_images
    WHERE repair_request_id = $repair_id
");
$conn->query("
    DELETE FROM repair_license_images
    WHERE repair_request_id = $repair_id
");
$conn->query("
    DELETE FROM repair_requests
    WHERE id = $repair_id
");
header("Location: pending.php");
exit;
