<?php
session_start();
require '../config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$technician_id = $_SESSION['user_id'];
$repair_request_id      = $_POST['repair_request_id'] ?? null;
$repair_result          = $_POST['repair_result'] ?? null;
$repair_detail          = trim($_POST['repair_detail'] ?? '');
$repair_completed_at  = $_POST['repair_completed_at'] ?? null;
if (
    !$repair_request_id ||
    !$repair_result ||
    !$repair_detail ||
    !$repair_completed_at
) {
    die('ข้อมูลไม่ครบ');
}
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO repair_logs
        (
            repair_request_id,
            technician_id,
            repair_result,
            repair_detail,
            repair_completed_at
        )
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iisss",
        $repair_request_id,
        $technician_id,
        $repair_result,
        $repair_detail,
        $repair_completed_at
    );
    $stmt->execute();
    $repair_log_id = $stmt->insert_id;
    $stmt->close();
    if (!empty($_FILES['repair_images']['name'][0])) {
        $upload_dir = "../uploads/repair_result/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        foreach ($_FILES['repair_images']['tmp_name'] as $i => $tmp) {

            if ($_FILES['repair_images']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = pathinfo($_FILES['repair_images']['name'][$i], PATHINFO_EXTENSION);
            $filename = 'result_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $path = $upload_dir . $filename;
            if (move_uploaded_file($tmp, $path)) {
                $img = $conn->prepare("
                    INSERT INTO repair_result_images
                    (repair_log_id, image_path)
                    VALUES (?, ?)
                ");
                $img->bind_param("is", $repair_log_id, $filename);
                $img->execute();
                $img->close();
            }
        }
    }
    $new_status = ($repair_result === 'success')
        ? 'completed'
        : 'need_fix';
    $upd = $conn->prepare("
        UPDATE repair_requests
        SET status = ?
        WHERE id = ?
    ");
    $upd->bind_param("si", $new_status, $repair_request_id);
    $upd->execute();
    $upd->close();
    $conn->commit();
    header("Location: progress.php?success=1");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
