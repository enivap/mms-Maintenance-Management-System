<?php
session_start();
require '../config/connect.php';

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

function resizeImage($source, $destination, $maxWidth = 1280, $quality = 75)
{
    [$width, $height, $type] = getimagesize($source);

    if ($width <= $maxWidth) {
        move_uploaded_file($source, $destination);
        return true;
    }

    $ratio = $width / $height;
    $newWidth  = $maxWidth;
    $newHeight = $maxWidth / $ratio;

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($type == IMAGETYPE_JPEG) {
        $sourceImage = imagecreatefromjpeg($source);
    } elseif ($type == IMAGETYPE_PNG) {
        $sourceImage = imagecreatefrompng($source);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    } else {
        return false;
    }

    imagecopyresampled(
        $newImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    if ($type == IMAGETYPE_JPEG) {
        imagejpeg($newImage, $destination, $quality);
    } else {
        imagepng($newImage, $destination, 6);
    }

    imagedestroy($sourceImage);
    imagedestroy($newImage);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $request_book_no  = $_POST['request_book_no'];
    $request_no       = $_POST['request_no'];
    $has_machine      = $_POST['has_machine'];
    $machine_status   = $_POST['machine_status'] ?? null;
    $location         = $_POST['location'];
    $department       = $_POST['department'];
    $job_type         = $_POST['job_type'];
    $problem_detail   = $_POST['problem_detail'];
    $repair_objective = $_POST['repair_objective'];
    $due_date         = $_POST['due_date'];

    $stmt = $conn->prepare("
        INSERT INTO repair_requests (
            request_book_no,
            request_no,
            has_machine,
            machine_status,
            location,
            department,
            job_type,
            problem_detail,
            repair_objective,
            due_date,
            status,
            requester_id,
            created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?, 'pending', ?, NOW())
    ");
    $stmt->bind_param(
        "iissssssssi",
        $request_book_no,
        $request_no,
        $has_machine,
        $machine_status,
        $location,
        $department,
        $job_type,
        $problem_detail,
        $repair_objective,
        $due_date,
        $user_id
    );
    $stmt->execute();
    $repair_id = $stmt->insert_id;
    $uploadPath = "../uploads/repair_requests/";
    if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
    foreach ($_FILES['problem_images']['tmp_name'] as $i => $tmp) {
        if ($tmp) {
            $ext  = pathinfo($_FILES['problem_images']['name'][$i], PATHINFO_EXTENSION);
            $name = uniqid('prob_') . '.' . $ext;
            resizeImage($tmp, $uploadPath . $name);
            $conn->query("
                INSERT INTO repair_request_images (repair_request_id, image_path)
                VALUES ($repair_id, '$name')
            ");
        }
    }
    $uploadPath = "../uploads/repair_license/";
    if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
    foreach ($_FILES['license_images']['tmp_name'] as $i => $tmp) {
        if ($tmp) {
            $ext  = pathinfo($_FILES['license_images']['name'][$i], PATHINFO_EXTENSION);
            $name = uniqid('lic_') . '.' . $ext;
            resizeImage($tmp, $uploadPath . $name);
            $conn->query("
                INSERT INTO repair_license_images (repair_request_id, image_path)
                VALUES ($repair_id, '$name')
            ");
        }
    }
    echo "<script>alert('บันทึกใบแจ้งซ่อมเรียบร้อย');location='history.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Creare Repair Request</title>
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
                <a href="create_repair.php" class="active">เพิ่มการแจ้งซ่อม</a>
                <a href="history.php">ประวัติการแจ้งซ่อมของฉัน</a>
            </nav>
        </aside>
        <main class="content">
            <div class="form-box">
                <h2>สร้างใบแจ้งซ่อม</h2>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <label>เล่มที่</label>
                            <input type="number" name="request_book_no" required>
                        </div>
                        <div>
                            <label>เลขที่</label>
                            <input type="number" name="request_no" required>
                        </div>
                        <div>
                            <label>มีเครื่องจักร</label>
                            <select name="has_machine" id="has_machine" required>
                                <option value="">-- เลือก --</option>
                                <option value="yes">มีเครื่องจักร</option>
                                <option value="no">ไม่มีเครื่องจักร</option>
                            </select>
                        </div>
                        <div id="machine_status_box">
                            <label>สถานะเครื่องจักร</label>
                            <select name="machine_status">
                                <option value="">-- เลือก --</option>
                                <option value="working">เครื่องกำลังทำงาน</option>
                                <option value="not_working">เครื่องหยุดการทำงาน</option>
                            </select>
                        </div>
                        <div class="full">
                            <label>สถานที่</label>
                            <input type="text" name="location" required>
                        </div>
                        <div>
                            <label>แผนก</label>
                            <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" readonly>
                        </div>
                        <div>
                            <label>ประเภทงาน</label>
                            <select name="job_type" required>
                                <option value="">-- เลือก --</option>
                                <option value="structure">โครงสร้างอาคาร</option>
                                <option value="electric">ระบบไฟฟ้า</option>
                                <option value="machine">เครื่องจักรอุปกรณ์</option>
                                <option value="facility">สิ่งอำนวยความสะดวก</option>
                                <option value="pest">Pest Control</option>
                                <option value="other">อื่นๆ</option>
                            </select>
                        </div>
                        <div class="full">
                            <label>รายละเอียดปัญหา</label>
                            <textarea name="problem_detail" required></textarea>
                        </div>
                        <div class="full">
                            <label>จุดประสงค์การซ่อม</label>
                            <textarea name="repair_objective"></textarea>
                        </div>
                        <div>
                            <label>วันที่ต้องการแล้วเสร็จ</label>
                            <input type="date" name="due_date">
                        </div>
                        <div>
                            <label>รูปปัญหา</label>
                            <input type="file" name="problem_images[]" multiple>
                        </div>
                        <div class="full">
                            <label>รูปใบแจ้งซ่อม</label>
                            <input type="file" name="license_images[]" multiple>
                        </div>
                    </div>
                    <button class="btn-primary">บันทึกใบแจ้งซ่อม</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>