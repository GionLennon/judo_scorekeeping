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
let lastMatches = {};

function renderMatch(match) {
  return `
    <div class="tatami" id="tatami-${match.tatami}">
      <h3>Tatami ${match.tatami}</h3>
      <div class="score-row"><strong>Rot:</strong> ${match.player_a}</div>
      <div class="score-row"><strong>Blau:</strong> ${match.player_b}</div>
      <div class="score-row">Ippon: ${match.ippon_a} - ${match.ippon_b}</div>
      <div class="score-row">Waza-ari: ${match.wazaari_a} - ${match.wazaari_b}</div>
      <div class="score-row">Shido: ${match.shido_a} - ${match.shido_b}</div>
      <div class="score-row">Zeit: <span class="time"
        data-tatami="${match.tatami}"
        data-start="${match.start_time}"
        data-end="${match.ended_at}"
        data-status="${match.status}"
        data-duration="${match.match_duration}"
        data-is_paused="${match.is_paused}"
        data-paused_time="${match.paused_time}">--:--</span></div>
      ${match.winner ? `<div style='margin-top:10px;'>🏆 Sieger: <strong>${match.winner}</strong></div>` : ""}
    </div>
  `;
}

function fetchScores() {
  fetch("api/get_scores.php")
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("scoreboard");
      data.forEach(match => {
        const key = `tatami-${match.tatami}`;
        const last = lastMatches[key];
        if (!last || JSON.stringify(last) !== JSON.stringify(match)) {
          lastMatches[key] = match;
          const newHTML = renderMatch(match);
          const existing = document.getElementById(key);
          if (existing) {
            existing.outerHTML = newHTML;
          } else {
            container.insertAdjacentHTML("beforeend", newHTML);
          }
        }
      });
    });
}

function updateTimers() {
  const now = Date.now() / 1000;
  document.querySelectorAll(".time").forEach(span => {
    const start = span.dataset.start;
    const end = span.dataset.end;
    const status = span.dataset.status;
    const duration = parseInt(span.dataset.duration || "240");
    const isPaused = span.dataset.is_paused === "1";
    const pausedTime = span.dataset.paused_time;

    if (!start) {
      span.textContent = "--:--";
      return;
    }

    const startSec = Date.parse(start) / 1000;
    if (isNaN(startSec)) {
      span.textContent = "--:--";
      return;
    }

    let elapsed;
    if (status === "done" && end) {
      const endSec = Date.parse(end) / 1000;
      elapsed = endSec - startSec;
    } else if (isPaused && pausedTime) {
      const pausedSec = Date.parse(pausedTime) / 1000;
      elapsed = pausedSec - startSec;
    } else {
      elapsed = now - startSec;
    }

    let remaining = duration - elapsed;
    if (remaining < 0) remaining = 0;

    const m = Math.floor(remaining / 60);
    const s = Math.floor(remaining % 60).toString().padStart(2, '0');
    span.textContent = `${m}:${s}`;
  });
}

setInterval(fetchScores, 2000);
setInterval(() => {
  fetch("api/update_score.php?action=check_timers");
}, 5000);
setInterval(updateTimers, 1000);

fetchScores();
</script>
</body>
</html>
