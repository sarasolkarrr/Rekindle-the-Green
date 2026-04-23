<?php
include 'connection.php';
session_start();

date_default_timezone_set('Asia/Kolkata');

function normalizeDriveKey($value) {
  $value = strtolower(trim((string) $value));

  if ($value === '') {
    return '';
  }

  $map = [
    'corbett' => 'corbett',
    'jim corbett' => 'corbett',
    'jim corbett national park' => 'corbett',
    'velas' => 'velas',
    'velas beach' => 'velas',
    'gir' => 'gir',
    'gir forest' => 'gir',
    'gir national park' => 'gir',
    'keoladeo' => 'keoladeo',
    'keoladeo national park' => 'keoladeo',
    'bird' => 'keoladeo',
  ];

  foreach ($map as $needle => $key) {
    if (strpos($value, $needle) !== false) {
      return $key;
    }
  }

  return $value;
}

function ensureReminderColumns($con) {
  $createTableSql = "CREATE TABLE IF NOT EXISTS drive_event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    drive_key VARCHAR(50) NOT NULL,
    drive_date DATE NOT NULL,
    reminder_sent TINYINT(1) NOT NULL DEFAULT 0,
    reminder_sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_drive_date (user_id, drive_key, drive_date),
    INDEX idx_drive_date_reminder (drive_date, reminder_sent)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

  if (!mysqli_query($con, $createTableSql)) {
    return false;
  }

  $hasReminderSent = false;
  $hasReminderSentAt = false;

  $columnsResult = mysqli_query($con, "SHOW COLUMNS FROM drive_event_registrations");
  if ($columnsResult) {
    while ($column = mysqli_fetch_assoc($columnsResult)) {
      if (($column['Field'] ?? '') === 'reminder_sent') {
        $hasReminderSent = true;
      }
      if (($column['Field'] ?? '') === 'reminder_sent_at') {
        $hasReminderSentAt = true;
      }
    }
  }

  if (!$hasReminderSent && !mysqli_query($con, "ALTER TABLE drive_event_registrations ADD COLUMN reminder_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER drive_date")) {
    return false;
  }

  if (!$hasReminderSentAt && !mysqli_query($con, "ALTER TABLE drive_event_registrations ADD COLUMN reminder_sent_at DATETIME NULL AFTER reminder_sent")) {
    return false;
  }

  $indexExists = false;
  $indexResult = mysqli_query($con, "SHOW INDEX FROM drive_event_registrations WHERE Key_name = 'idx_drive_date_reminder'");
  if ($indexResult && mysqli_num_rows($indexResult) > 0) {
    $indexExists = true;
  }

  if (!$indexExists) {
    try {
      mysqli_query($con, "CREATE INDEX idx_drive_date_reminder ON drive_event_registrations (drive_date, reminder_sent)");
    } catch (\Throwable $e) {
      if ((int) mysqli_errno($con) !== 1061) {
        return false;
      }
    }
  }

  return true;
}

if (!isset($_SESSION['admin_id'])) {
  if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
  } else {
    header('Location: login.php');
  }
  exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminDriveId = (int) ($_SESSION['admin_drive_id'] ?? 0);
$adminDriveLabel = trim((string) ($_SESSION['admin_drive_name'] ?? ''));
$adminDriveKey = normalizeDriveKey($_SESSION['admin_drive_key'] ?? $adminDriveLabel);

$driveDate = '';
if ($adminDriveId > 0) {
  $driveResult = mysqli_query($con, "SELECT location, date FROM drives WHERE id = {$adminDriveId} LIMIT 1");
  if ($driveResult && mysqli_num_rows($driveResult) === 1) {
    $driveRow = mysqli_fetch_assoc($driveResult);
    $adminDriveLabel = trim((string) ($driveRow['location'] ?? $adminDriveLabel));
    $adminDriveKey = normalizeDriveKey($adminDriveLabel);
    $driveDate = trim((string) ($driveRow['date'] ?? ''));
  }
}

$today = (new DateTime())->format('Y-m-d');
$hourNow = (int) (new DateTime())->format('H');
$pendingRows = [];
$errorMessage = '';

if (!ensureReminderColumns($con)) {
  $errorMessage = 'Could not prepare reminder schema.';
} elseif ($adminDriveKey === '') {
  $errorMessage = 'No admin drive is assigned in this session.';
} else {
  $sql = "SELECT der.id, der.drive_key, der.drive_date, der.created_at, u.first_name, u.last_name, u.email
          FROM drive_event_registrations der
          INNER JOIN users u ON u.id = der.user_id
          WHERE der.drive_date = ?
            AND der.drive_key = ?
            AND der.reminder_sent = 0
            AND u.email IS NOT NULL
            AND u.email <> ''
          ORDER BY der.created_at ASC";

  $stmt = mysqli_prepare($con, $sql);
  if (!$stmt) {
    $errorMessage = 'Could not prepare dry-run query.';
  } else {
    mysqli_stmt_bind_param($stmt, 'ss', $today, $adminDriveKey);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
        $pendingRows[] = $row;
      }
    } else {
      $errorMessage = 'Could not fetch dry-run results.';
    }

    mysqli_stmt_close($stmt);
  }
}

$pendingCount = count($pendingRows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <title>Reminder Dry Run - Rekindle the Green</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      color: #f5f7f2;
      background:
        radial-gradient(circle at top left, rgba(61, 107, 42, 0.32), transparent 32%),
        radial-gradient(circle at top right, rgba(201, 168, 76, 0.18), transparent 28%),
        linear-gradient(160deg, #081008 0%, #142414 55%, #0f1a0f 100%);
    }

    .wrap {
      width: min(980px, calc(100% - 2rem));
      margin: 1.25rem auto 2rem;
      display: grid;
      gap: 1rem;
    }

    .panel {
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(7, 16, 7, 0.74);
      backdrop-filter: blur(14px);
      border-radius: 20px;
      box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
      padding: 1.2rem;
    }

    .top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .title {
      margin: 0;
      font-size: clamp(1.2rem, 3vw, 1.9rem);
      letter-spacing: -0.02em;
    }

    .meta {
      color: rgba(245, 247, 242, 0.74);
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.35rem 0.7rem;
      border-radius: 999px;
      background: rgba(201, 168, 76, 0.14);
      color: #f4da8d;
      font-size: 0.76rem;
      font-weight: 700;
      letter-spacing: 0.04em;
    }

    .actions {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
    }

    .btn {
      text-decoration: none;
      border-radius: 999px;
      padding: 0.7rem 1rem;
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.01em;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn:hover { transform: translateY(-1px); }

    .btn-dark {
      color: #f5f7f2;
      border: 1px solid rgba(255, 255, 255, 0.18);
      background: rgba(255, 255, 255, 0.05);
    }

    .btn-gold {
      color: #1f2d1f;
      background: #e4c67a;
      border: 1px solid rgba(228, 198, 122, 0.7);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 720px;
      overflow: hidden;
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(10, 18, 10, 0.9);
    }

    thead th {
      text-align: left;
      padding: 0.85rem 0.95rem;
      font-size: 0.72rem;
      letter-spacing: 0.11em;
      text-transform: uppercase;
      color: rgba(245, 247, 242, 0.6);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }

    tbody td {
      padding: 0.9rem 0.95rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      font-size: 0.92rem;
    }

    .table-wrap { overflow: auto; }

    .note {
      color: rgba(245, 247, 242, 0.76);
      line-height: 1.6;
      font-size: 0.92rem;
    }

    .warn {
      color: #ffe9b0;
      background: rgba(201, 168, 76, 0.14);
      border: 1px solid rgba(201, 168, 76, 0.24);
      border-radius: 12px;
      padding: 0.75rem 0.85rem;
    }

    .error {
      color: #ffd2c9;
      background: rgba(187, 76, 59, 0.2);
      border: 1px solid rgba(187, 76, 59, 0.3);
      border-radius: 12px;
      padding: 0.75rem 0.85rem;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <section class="panel">
      <div class="top">
        <div>
          <h1 class="title">Reminder Dry Run</h1>
          <div class="meta">
            Admin: <?= htmlspecialchars($adminName) ?><br>
            Drive: <?= htmlspecialchars($adminDriveLabel !== '' ? $adminDriveLabel : $adminDriveKey) ?>
            <?php if ($driveDate !== ''): ?>
              <br>Assigned drive date: <?= htmlspecialchars($driveDate) ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="actions">
          <a class="btn btn-dark" href="admin-dashboard.php">Back to Dashboard</a>
          <a class="btn btn-gold" href="send-drive-reminders.php" target="_blank" rel="noopener">Run Actual Sender</a>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="top" style="margin-bottom:0.8rem;">
        <span class="badge">Today: <?= htmlspecialchars($today) ?></span>
        <span class="badge">Pending for this drive: <?= (int) $pendingCount ?></span>
      </div>

      <?php if ($hourNow < 9): ?>
        <div class="warn" style="margin-bottom:0.8rem;">
          Current time is before 9 AM. Scheduled reminders should not send yet.
        </div>
      <?php endif; ?>

      <?php if ($errorMessage !== ''): ?>
        <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
      <?php elseif ($pendingCount === 0): ?>
        <div class="note">No pending reminder recipients for your drive today.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Drive Key</th>
                <th>Drive Date</th>
                <th>Registered At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingRows as $index => $row): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['drive_key'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['drive_date'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['created_at'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>
