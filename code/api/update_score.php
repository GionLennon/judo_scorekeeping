<?php
header("Content-Type: text/plain");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$tatami = $_POST['tatami'] ?? null;
$action = $_POST['action'] ?? '';
$secret = $_POST['secret'] ?? '';
$player_a = $_POST['player_a'] ?? null;
$player_b = $_POST['player_b'] ?? null;
$scorekeeper_password = 'keepitsecret';

if ($secret !== $scorekeeper_password) {
    echo "❌ Falsches Passwort";
    exit;
}

if (!$tatami) {
    echo "❌ Tatami fehlt";
    exit;
}

// Reset logic (admin button)
if ($action === 'reset') {
    $pdo->prepare("DELETE FROM matches WHERE tatami = ?")->execute([$tatami]);
    echo "✅ Match zurückgesetzt";
    exit;
}

// Get existing match
$stmt = $pdo->prepare("SELECT * FROM matches WHERE tatami = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$tatami]);
$match = $stmt->fetch();

// Start new match only if needed
if ($player_a && $player_b) {
    $shouldStartNewMatch = (
        !$match ||
        $match['status'] === 'done' ||
        $match['player_a'] !== $player_a ||
        $match['player_b'] !== $player_b
    );

    if ($shouldStartNewMatch) {
        $pdo->prepare("DELETE FROM matches WHERE tatami = ?")->execute([$tatami]);
        $pdo->prepare("INSERT INTO matches (tatami, player_a, player_b) VALUES (?, ?, ?)")->execute([$tatami, $player_a, $player_b]);
        $stmt->execute([$tatami]);
        $match = $stmt->fetch();
    }
}

if (!$match) {
    echo "❌ Kein Match gefunden";
    exit;
}

if ($match['status'] === 'done' && strtotime($match['expired_at']) > time()) {
    echo "✅ Match bereits beendet";
    exit;
}

// Update the requested score field
$pdo->prepare("UPDATE matches SET $action = $action + 1 WHERE id = ?")->execute([$match['id']]);

// Fetch updated state
$stmt->execute([$tatami]);
$match = $stmt->fetch();

// Check for auto-win conditions
$winner = null;
if ($match['ippon_a'] >= 1) $winner = $match['player_a'];
if ($match['ippon_b'] >= 1) $winner = $match['player_b'];

if (!$winner) {
    if ($match['wazaari_a'] >= 2) {
        $pdo->prepare("UPDATE matches SET ippon_a = 1 WHERE id = ?")->execute([$match['id']]);
        $winner = $match['player_a'];
    } elseif ($match['wazaari_b'] >= 2) {
        $pdo->prepare("UPDATE matches SET ippon_b = 1 WHERE id = ?")->execute([$match['id']]);
        $winner = $match['player_b'];
    }
}

if (!$winner) {
    if ($match['shido_a'] >= 3) $winner = $match['player_b'];
    if ($match['shido_b'] >= 3) $winner = $match['player_a'];
}

if ($winner) {
    $now = new DateTime();
    $expire = clone $now;
    $expire->modify('+1 minute');
    $pdo->prepare("UPDATE matches SET winner = ?, status = 'done', ended_at = ?, expired_at = ? WHERE id = ?")
        ->execute([$winner, $now->format('Y-m-d H:i:s'), $expire->format('Y-m-d H:i:s'), $match['id']]);
    echo "🏁 Match beendet. Sieger: $winner";
} else {
    echo "✅ Punkt hinzugefügt";
}
?>
