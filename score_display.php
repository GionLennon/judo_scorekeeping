<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Judo Live Scoreboard</title>
  <style>
    body { background: black; color: white; font-family: monospace; margin: 0; padding: 20px; }
    .scoreboard { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
    .tatami {
      background: #111;
      border: 2px solid #fff;
      border-radius: 10px;
      padding: 20px;
      width: 300px;
      text-align: center;
    }
    .score-row { display: flex; justify-content: space-between; font-size: 20px; margin: 5px 0; }
  </style>
</head>
<body>
<h1 style="text-align:center;">Judo Live Scoreboard</h1>
<div class="scoreboard" id="scoreboard"></div>
<script>
function fetchScores() {
    fetch("api/get_scores.php")
        .then(res => res.json())
        .then(data => {
            const html = data.map(match => `
                <div class="tatami">
                    <h3>Tatami ${match.tatami}</h3>
                    <div class="score-row"><strong>Rot:</strong> ${match.player_a}</div>
                    <div class="score-row"><strong>Blau:</strong> ${match.player_b}</div>
                    <div class="score-row">Ippon: ${match.ippon_a} - ${match.ippon_b}</div>
                    <div class="score-row">Waza-ari: ${match.wazaari_a} - ${match.wazaari_b}</div>
                    <div class="score-row">Shido: ${match.shido_a} - ${match.shido_b}</div>
                    ${match.winner ? `<div style='margin-top:10px;'>🏆 Sieger: <strong>${match.winner}</strong></div>` : ""}
                </div>
            `).join("");
            document.getElementById("scoreboard").innerHTML = html;
        });
}
setInterval(fetchScores, 2000);
fetchScores();
</script>
</body>
</html>
