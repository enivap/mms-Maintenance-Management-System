<?php
require '../config/connect.php';
if (!isset($_GET['id'])) exit('ไม่พบข้อมูล');
$repair_id = (int)$_GET['id'];

$repair = $conn->query("
    SELECT r.*, u.fullname, u.department
    FROM repair_requests r
    JOIN users u ON r.requester_id = u.id
    WHERE r.id = $repair_id
")->fetch_assoc();
if (!$repair) exit('ไม่พบใบแจ้งซ่อม');

$problem_images = $conn->query("
    SELECT image_path FROM repair_request_images
    WHERE repair_request_id = $repair_id
");
$license_images = $conn->query("
    SELECT image_path FROM repair_license_images
    WHERE repair_request_id = $repair_id
");
$repair_result_images = $conn->query("
    SELECT rri.image_path
    FROM repair_result_images rri
    JOIN repair_logs rl ON rri.repair_log_id = rl.id
    WHERE rl.repair_request_id = $repair_id
");
$repair_logs = $conn->query("
    SELECT rl.*, u.fullname AS technician_name
    FROM repair_logs rl
    JOIN users u ON rl.technician_id = u.id
    WHERE rl.repair_request_id = $repair_id
");
$qa_logs = $conn->query("
    SELECT q.*, u.fullname AS qa_name
    FROM qa_inspection_logs q
    JOIN users u ON q.qa_id = u.id
    WHERE q.repair_request_id = $repair_id
");

$status_th = [
    'pending' => 'รอดำเนินการ',
    'in_progress' => 'กำลังดำเนินงาน',
    'completed' => 'ซ่อมเสร็จ',
    'need_fix' => 'ต้องแก้ไขเพิ่มเติม',
    'closed' => 'ปิดงานแล้ว'
];
$has_machine_th = ['yes' => 'มีเครื่องจักร', 'no' => 'ไม่มีเครื่องจักร'];
$machine_status_th = ['working' => 'เครื่องจักรกำลังทำงาน', 'not_working' => 'ไม่สามารถใช้งานได้'];
$job_type_th = [
    'structure' => 'โครงสร้างอาคาร',
    'electric' => 'ระบบไฟฟ้า',
    'machine' => 'เครื่องจักร',
    'facility' => 'สิ่งอำนวยความสะดวก',
    'pest' => 'Pest Control',
    'other' => 'งานอื่น ๆ'
];
$repair_result_th = ['success' => 'ซ่อมสำเร็จ', 'cannot_fix' => 'ไม่สามารถซ่อมได้'];
$result_th = ['pass' => 'ผ่านการตรวจสอบ', 'fail' => 'ต้องแก้ไขเพิ่มเติม'];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบแจ้งซ่อม</title>

    <style>
        body {
            font-family: "Sarabun", "Segoe UI", sans-serif;
            background: #eaeef5;
            padding: 30px;
            color: #222
        }

        .paper {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 48px 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08)
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 26px
        }

        .header h2 {
            margin: 0;
            font-size: 22px
        }

        .meta {
            font-size: 14px;
            line-height: 1.6;
            text-align: right
        }

        .section {
            margin-top: 26px
        }

        .section-title {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px
        }

        .box {
            border: 1px solid #000;
            padding: 12px 14px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px
        }

        td {
            padding: 6px 8px;
            vertical-align: top
        }

        td.label {
            width: 180px;
            font-weight: bold
        }

        tr+tr td {
            border-top: 1px solid #ccc
        }

        .text {
            white-space: pre-line;
            line-height: 1.6
        }

        .images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px
        }

        .images img {
            width: 180px;
            border: 1px solid #aaa;
            border-radius: 4px
        }

        .log {
            border: 1px solid #000;
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 14px
        }

        .log b {
            display: inline-block;
            width: 140px
        }

        .page-break {
            page-break-before: always
        }

        @media print {
            body {
                background: none;
                padding: 0
            }

            .paper {
                box-shadow: none;
                padding: 30px
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="paper">

        <div class="header">
            <h2>ใบแจ้งซ่อม / ใบสั่งงาน</h2>
            <div class="meta">
                เลขที่ใบแจ้ง: <?= $repair['request_book_no'] ?>/<?= $repair['request_no'] ?><br>
                วันที่แจ้ง: <?= date('d/m/Y H:i', strtotime($repair['created_at'])) ?><br>
                สถานะ: <?= $status_th[$repair['status']] ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">ข้อมูลผู้แจ้ง</div>
            <div class="box">
                <table>
                    <tr>
                        <td class="label">ชื่อผู้แจ้ง</td>
                        <td><?= htmlspecialchars($repair['fullname']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">แผนก</td>
                        <td><?= htmlspecialchars($repair['department']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="section">
            <div class="section-title">รายละเอียดงาน</div>
            <div class="box">
                <table>
                    <tr>
                        <td class="label">สถานที่</td>
                        <td><?= htmlspecialchars($repair['location']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">เครื่องจักร</td>
                        <td><?= $has_machine_th[$repair['has_machine']] ?></td>
                    </tr>
                    <tr>
                        <td class="label">สถานะเครื่องจักร</td>
                        <td><?= $machine_status_th[$repair['machine_status']] ?></td>
                    </tr>
                    <tr>
                        <td class="label">ประเภทงาน</td>
                        <td><?= $job_type_th[$repair['job_type']] ?></td>
                    </tr>
                    <tr>
                        <td class="label">กำหนดแล้วเสร็จ</td>
                        <td><?= $repair['due_date'] ? date('d/m/Y', strtotime($repair['due_date'])) : '-' ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="section">
            <div class="section-title">รายละเอียดปัญหา</div>
            <div class="box text"><?= htmlspecialchars($repair['problem_detail']) ?></div>
        </div>
        <div class="section">
            <div class="section-title">จุดประสงค์การซ่อม</div>
            <div class="box text"><?= htmlspecialchars($repair['repair_objective']) ?></div>
        </div>
        <?php if ($problem_images->num_rows): ?>
            <div class="section page-break">
                <div class="section-title">รูปก่อนซ่อม</div>
                <div class="images">
                    <?php while ($img = $problem_images->fetch_assoc()): ?>
                        <img src="../uploads/repair_requests/<?= $img['image_path'] ?>">
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($license_images->num_rows): ?>
            <div class="section">
                <div class="section-title">รูปใบแจ้งซ่อม</div>
                <div class="images">
                    <?php while ($img = $license_images->fetch_assoc()): ?>
                        <img src="../uploads/repair_license/<?= $img['image_path'] ?>">
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($repair_result_images->num_rows): ?>
            <div class="section">
                <div class="section-title">รูปหลังซ่อมเสร็จ</div>
                <div class="images">
                    <?php while ($img = $repair_result_images->fetch_assoc()): ?>
                        <img src="../uploads/repair_result/<?= $img['image_path'] ?>">
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($repair_logs->num_rows): ?>
            <div class="section page-break">
                <div class="section-title">บันทึกการซ่อม</div>
                <?php while ($log = $repair_logs->fetch_assoc()): ?>
                    <div class="log">
                        <b>ช่าง:</b> <?= $log['technician_name'] ?><br>
                        <b>ผลการซ่อม:</b> <?= $repair_result_th[$log['repair_result']] ?><br>
                        <b>รายละเอียด:</b> <?= nl2br(htmlspecialchars($log['repair_detail'])) ?><br>
                        <b>วันที่ซ่อมเสร็จ:</b> <?= date('d/m/Y H:i', strtotime($log['repair_completed_at'])) ?><br>
                        <b>วันที่บันทึก:</b> <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php if ($qa_logs->num_rows): ?>
            <div class="section">
                <div class="section-title">ผลการตรวจสอบ QA / QS</div>
                <?php while ($qa = $qa_logs->fetch_assoc()): ?>
                    <div class="log">
                        <b>รอบที่:</b> <?= $qa['inspection_round'] ?><br>
                        <b>ผู้ตรวจ:</b> <?= $qa['qa_name'] ?><br>
                        <b>ผล:</b> <?= $result_th[$qa['result']] ?><br>
                        <b>หมายเหตุ:</b> <?= nl2br(htmlspecialchars($qa['comment'])) ?><br>
                        <b>วันที่ตรวจ:</b> <?= date('d/m/Y', strtotime($qa['created_at'])) ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>