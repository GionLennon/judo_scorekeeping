<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Punkteingabe - Judo</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 10px; margin: 0; background: #f2f2f2; }
    form { background: white; padding: 15px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    input, select, button { width: 100%; padding: 12px; margin: 8px 0; font-size: 16px; box-sizing: border-box; }
    .buttons button { width: 48%; margin: 1%; }
    .buttons { display: flex; flex-wrap: wrap; justify-content: space-between; }
    .reset-btn { background-color: #e74c3c; color: white; border: none; }
    #remainingTime { font-weight: bold; }
  </style>
</head>
<body>
<h2 style="text-align:center;">Judo Punkteingabe</h2>
<form id="scoreForm">
  <div style="text-align:center; font-size: 24px; margin-bottom: 10px;">
    Verbleibende Zeit: <span id="remainingTime">-</span>
  </div>

  <label for="tatami">Tatami Nummer:</label>
  <select name="tatami" id="tatami" required>
    <option value="1">Tatami 1</option>
    <option value="2">Tatami 2</option>
    <option value="3">Tatami 3</option>
    <option value="4">Tatami 4</option>
    <option value="5">Tatami 5</option>
    <option value="6">Tatami 6</option>
    <option value="7">Tatami 7</option>
    <option value="8">Tatami 8</option>
  </select>

  Kämpfer Rot: <input type="text" name="player_a" id="player_a" required>
  Kämpfer Blau: <input type="text" name="player_b" id="player_b" required>
  Passwort: <input type="password" name="secret" id="secret" required>

  <div class="buttons">
    <button type="button" onclick="submitScore('ippon_a')">Ippon Rot</button>
    <button type="button" onclick="submitScore('ippon_b')">Ippon Blau</button>
    <button type="button" onclick="submitScore('wazaari_a')">Waza-ari Rot</button>
    <button type="button" onclick="submitScore('wazaari_b')">Waza-ari Blau</button>
    <button type="button" onclick="submitScore('shido_a')">Shido Rot</button>
    <button type="button" onclick="submitScore('shido_b')">Shido Blau</button>
  </div>

  <button type="button" onclick="controlTimer('start_timer')">▶️ Timer Starten</button>
  <button type="button" onclick="controlTimer('pause_timer')">⏸ Timer Pausieren</button>
  <button type="button" onclick="controlTimer('reset_timer')">🔁 Timer Zurücksetzen</button>
  <button type="button" class="reset-btn" onclick="resetMatch()">❌ Match Zurücksetzen</button>
</form>

<script>
function submitScore(action) {
    const form = document.getElementById("scoreForm");
    const formData = new FormData(form);
    formData.append("action", action);
    fetch("api/update_score.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(alert)
    .catch(err => alert("Fehler: " + err));
}

function resetMatch() {
    const form = document.getElementById("scoreForm");
    const formData = new FormData(form);
    formData.append("action", "reset");
    fetch("api/update_score.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(alert)
    .catch(err => alert("Fehler: " + err));
}

function controlTimer(action) {
    const form = document.getElementById("scoreForm");
    const formData = new FormData(form);
    formData.append("action", action);
    fetch("api/update_score.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(alert)
    .catch(err => alert("Fehler: " + err));
}

function fetchTimer() {
  const tatami = document.getElementById("tatami").value;
  fetch("api/get_scores.php")
    .then(res => res.json())
    .then(data => {
      const match = data.find(m => m.tatami == tatami);
      const span = document.getElementById("remainingTime");
      if (!match || !match.start_time) {
        span.textContent = "-";
        return;
      }

      const start = Date.parse(match.start_time) / 1000;
      const now = Date.now() / 1000;
      const duration = parseInt(match.match_duration || 240);
      let elapsed;
      if (match.status === "done" && match.ended_at) {
        const end = Date.parse(match.ended_at) / 1000;
        elapsed = end - start;
      } else if (match.is_paused && match.paused_time) {
        const paused = Date.parse(match.paused_time) / 1000;
        elapsed = paused - start;
      } else {
        elapsed = now - start;
      }

      if (match.status === "done" && match.ended_at) {
        const end = Date.parse(match.ended_at) / 1000;
        elapsed = end - start;
      }

      let remaining = duration - elapsed;
      if (remaining < 0) remaining = 0;

      const m = Math.floor(remaining / 60);
      const s = Math.floor(remaining % 60).toString().padStart(2, '0');
      span.textContent = `${m}:${s}`;
    });
}
setInterval(fetchTimer, 1000);
fetchTimer();
</script>
</body>
</html>
