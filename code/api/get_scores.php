<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT * FROM matches WHERE status = 'in_progress' OR (status = 'done' AND expired_at > NOW()) ORDER BY tatami");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
