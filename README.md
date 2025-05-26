# Judo Tournament Scoreboard

A lightweight web-based scoreboard and admin system for managing judo matches across multiple tatamis in real time.

## Project Structure

```
/config.php                # database config
/score_input.php           # scorekeeper interface
/score_display.php         # live scoreboard display
/admin.php                 # admin panel
/api/
  ├── update_score.php     # handles scoring actions and match logic
  └── get_scores.php       # fetches current matches for display
```

## Setup Instructions

### 1. Upload Files

Upload all files to web server.

### 2. Create MySQL Database

Run the following SQL using phpMyAdmin:

```sql
CREATE TABLE matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tatami INT NOT NULL,
  player_a VARCHAR(50),
  player_b VARCHAR(50),
  ippon_a TINYINT DEFAULT 0,
  ippon_b TINYINT DEFAULT 0,
  wazaari_a TINYINT DEFAULT 0,
  wazaari_b TINYINT DEFAULT 0,
  shido_a TINYINT DEFAULT 0,
  shido_b TINYINT DEFAULT 0,
  winner VARCHAR(50) DEFAULT NULL,
  status ENUM('in_progress', 'done') DEFAULT 'in_progress',
  ended_at DATETIME DEFAULT NULL,
  expired_at DATETIME DEFAULT NULL,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 3. Configure Database

Edit `config.php` and enter your database credentials:

```php
$pdo = new PDO("mysql:host=localhost;dbname=YOUR_DB", "YOUR_USER", "YOUR_PASS", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
```

## Usage Guide

### Scorekeeper

* Open `score_input.php` in a browser
* Select a tatami (1 to 8), enter player names and the password (`keepitsecret`)
* Use the buttons to update scores
* Matches end automatically when scoring conditions are met
* To reset a match, use the "Match Zurücksetzen" button

### Display Operator

* Open `score_display.php` on a large screen or projector
* Scores update automatically every 2 seconds

### Admin

* Open `admin.php`
* Log in with the admin password (`adminsupersecret`)
* View and delete matches

## Security Notes

* Consider using `.htaccess` or similar methods to restrict access to `admin.php`
* Change the default passwords in `update_score.php` and `admin.php`
* Ensure `config.php` is not accessible publicly

## To Do / Ideas

* Add timer and golden score support
* CSV or PDF export of results
* Match history per tatami
