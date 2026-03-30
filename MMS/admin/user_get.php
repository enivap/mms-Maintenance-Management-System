<?php
require '../config/connect.php';
$id = intval($_GET['id'] ?? 0);
$q = $conn->query("
    SELECT id, username, fullname, department, role_id
    FROM users
    WHERE id = $id
    LIMIT 1
");
echo json_encode($q->fetch_assoc());
