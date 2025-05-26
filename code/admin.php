<?php
session_start();

$admin_password = 'adminsupersecret';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_secret'])) {
    if ($_POST['admin_secret'] === $admin_password) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "❌ Falsches Passwort";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Judo Turnier</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f2f2f2; }
    form, .match { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    input, button { padding: 10px; margin: 5px 0; font-size: 16px; width: 100%; }
    .match button { background-color: #e74c3c; color: white; border: none; cursor: pointer; }
  </style>
</head>
<body>
<?php if (!isset($_SESSION['is_admin'])): ?>
  <h2>Admin Login</h2>
  <?php if (isset($error)) echo "<p>$error</p>"; ?>
  <form method="POST">
    <label for="admin_secret">Admin Passwort:</label>
    <input type="password" name="admin_secret" id="admin_secret" required>
    <button type="submit">Anmelden</button>
  </form>
<?php else: ?>
  <h2>Admin Panel – Matches verwalten</h2>
  <p><a href="?logout=1">🚪 Abmelden</a></p>
  <?php
  require_once "config.php";

  if (isset($_POST['delete_id'])) {
      $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$_POST['delete_id']]);
      echo "<p>✅ Match gelöscht.</p>";
  }

  $matches = $pdo->query("SELECT * FROM matches ORDER BY tatami, id DESC")->fetchAll();

  foreach ($matches as $match) {
      echo "<div class='match'>
              <strong>Tatami {$match['tatami']}</strong><br>
              Rot: {$match['player_a']}<br>
              Blau: {$match['player_b']}<br>
              Status: {$match['status']}<br>
              Sieger: " . ($match['winner'] ?: '-') . "<br>
              <form method='POST'>
                <input type='hidden' name='delete_id' value='{$match['id']}'>
                <button type='submit'>Löschen</button>
              </form>
            </div>";
  }
  ?>
<?php endif; ?>
</body>
</html>
