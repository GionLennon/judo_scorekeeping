<?php
$pdo = new PDO("mysql:host=localhost;dbname=YOUR_DB", "YOUR_USER", "YOUR_PASS", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
?>