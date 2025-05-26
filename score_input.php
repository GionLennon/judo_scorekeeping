<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Punkteingabe - Judo</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 10px;
      margin: 0;
      background: #f2f2f2;
    }
    h2 {
      text-align: center;
    }
    form {
      background: white;
      padding: 15px;
      border-radius: 8px;
      max-width: 400px;
      margin: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input, select, button {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      font-size: 16px;
      box-sizing: border-box;
    }
    .buttons button {
      width: 48%;
      margin: 1%;
    }
    .buttons {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .reset-btn {
      background-color: #e74c3c;
      color: white;
      border: none;
    }
  </style>
</head>
<body>
<h2>Judo Punkteingabe</h2>
<form id="scoreForm">
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

  Kämpfer Rot: <input type="text" name="player_a" required>
  Kämpfer Blau: <input type="text" name="player_b" required>
  Passwort: <input type="password" name="secret" required>

  <div class="buttons">
    <button type="button" onclick="submitScore('ippon_a')">Ippon Rot</button>
    <button type="button" onclick="submitScore('ippon_b')">Ippon Blau</button>
    <button type="button" onclick="submitScore('wazaari_a')">Waza-ari Rot</button>
    <button type="button" onclick="submitScore('wazaari_b')">Waza-ari Blau</button>
    <button type="button" onclick="submitScore('shido_a')">Shido Rot</button>
    <button type="button" onclick="submitScore('shido_b')">Shido Blau</button>
  </div>

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
    }).then(response => response.text()).then(alert);
}

function resetMatch() {
    const form = document.getElementById("scoreForm");
    const formData = new FormData(form);
    formData.append("action", "reset");
    fetch("api/update_score.php", {
        method: "POST",
        body: formData
    }).then(response => response.text()).then(alert);
}
</script>
</body>
</html>
