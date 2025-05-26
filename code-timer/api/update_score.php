<?php
header("Content-Type: text/plain");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$tatami = $_POST['tatami'] ?? ($_GET['tatami'] ?? null);
$action = $_POST['action'] ?? '';
$mode = $_GET['action'] ?? '';
$secret = $_POST['secret'] ?? '';
$player_a = $_POST['player_a'] ?? null;
$player_b = $_POST['player_b'] ?? null;
$scorekeeper_password = 'keepitsecret';

if ($secret && $secret !== $scorekeeper_password) {
    echo "❌ Falsches Passwort";
    exit;
}

if (!$tatami) {
    echo "❌ Tatami fehlt";
    exit;
}

// Handle automatic timeout polling
if ($mode === 'check_timers') {
    $stmt = $pdo->query("SELECT * FROM matches WHERE status = 'in_progress' AND start_time IS NOT NULL");
    $now = time();
    foreach ($stmt as $match) {
        if ($match['is_paused']) continue;
        $start = strtotime($match['start_time']);
        $duration = $match['match_duration'] ?? 240;
        if ($now >= $start + $duration) {
            $pdo->prepare("UPDATE matches SET status = 'done', winner = NULL, expired_at = ? WHERE id = ?")
                ->execute([(new DateTime())->modify('+1 minute')->format('Y-m-d H:i:s'), $match['id']]);
        }
    }
    echo "⏱ Checked for expired timers.";
    exit;
}

// Reset logic
if ($action === 'reset') {
    $pdo->prepare("DELETE FROM matches WHERE tatami = ?")->execute([$tatami]);
    echo "✅ Match zurückgesetzt";
    exit;
}

// Get existing match
$stmt = $pdo->prepare("SELECT * FROM matches WHERE tatami = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$tatami]);
$match = $stmt->fetch();

// Create new match if needed
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

// Timer: START
if ($action === 'start_timer') {
    if ($match['is_paused'] && $match['paused_time']) {
        $pausedAt = strtotime($match['paused_time']);
        $now = time();
        $offset = $now - $pausedAt;
        $newStart = date('Y-m-d H:i:s', strtotime($match['start_time']) + $offset);
        $pdo->prepare("UPDATE matches SET start_time = ?, paused_time = NULL, is_paused = 0 WHERE id = ?")
            ->execute([$newStart, $match['id']]);
        echo "▶️ Timer fortgesetzt";
    } else {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $pdo->prepare("UPDATE matches SET start_time = ?, is_paused = 0 WHERE id = ?")->execute([$now, $match['id']]);
        echo "▶️ Timer gestartet";
    }
    exit;
}

// Timer: PAUSE
if ($action === 'pause_timer') {
    $now = (new DateTime())->format('Y-m-d H:i:s');
    $pdo->prepare("UPDATE matches SET paused_time = ?, is_paused = 1 WHERE id = ?")->execute([$now, $match['id']]);
    echo "⏸ Timer pausiert";
    exit;
}

// Timer: RESET
if ($action === 'reset_timer') {
    $pdo->prepare("UPDATE matches SET start_time = NULL, paused_time = NULL, is_paused = 0 WHERE id = ?")->execute([$match['id']]);
    echo "🔁 Timer zurückgesetzt";
    exit;
}

// Prevent scoring if match is over
if ($match['status'] === 'done' && strtotime($match['expired_at']) > time()) {
    echo "✅ Match beendet (Zeit abgelaufen oder Ippon). Bitte neuen Kampf starten.";
    exit;
}

// Auto-end logic (ignore if paused)
if (!$match['is_paused'] && $match['start_time']) {
    $start = strtotime($match['start_time']);
    $duration = $match['match_duration'] ?? 240;
    if (time() >= $start + $duration && $match['status'] !== 'done') {
        $pdo->prepare("UPDATE matches SET status = 'done', winner = NULL, expired_at = ? WHERE id = ?")
            ->execute([(new DateTime())->modify('+1 minute')->format('Y-m-d H:i:s'), $match['id']]);
        echo "⏱ Zeit abgelaufen. Kein Sieger.";
        exit;
    }
}

// Apply scoring
$pdo->prepare("UPDATE matches SET $action = $action + 1 WHERE id = ?")->execute([$match['id']]);

// Re-fetch updated match
$stmt->execute([$tatami]);
$match = $stmt->fetch();

// Win logic
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
