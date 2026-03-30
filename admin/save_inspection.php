<?php
session_start();
require '../config/connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: inspect.php");
    exit;
}
$repair_id = (int)$_POST['repair_request_id'];
$qa_id     = (int)$_POST['qa_id'];
$result    = $_POST['result'];
$comment   = trim($_POST['comment']);
if (!$repair_id || !$qa_id || !$result || !$comment) {
    exit('ข้อมูลไม่ครบ');
}
$round_q = $conn->query("
    SELECT COUNT(*) total 
    FROM qa_inspection_logs 
    WHERE repair_request_id = $repair_id
");
$inspection_round = $round_q->fetch_assoc()['total'] + 1;
$conn->query("
    INSERT INTO qa_inspection_logs (
        repair_request_id,
        qa_id,
        inspection_round,
        result,
        comment
    ) VALUES (
        $repair_id,
        $qa_id,
        $inspection_round,
        '$result',
        '$comment'
    )
");
if ($result === 'pass') {
    $conn->query("
        UPDATE repair_requests 
        SET status = 'closed'
        WHERE id = $repair_id
    ");
} else {
    $conn->query("
        UPDATE repair_requests 
        SET status = 'need_fix'
        WHERE id = $repair_id
    ");
}
header("Location: inspect.php");
exit;
