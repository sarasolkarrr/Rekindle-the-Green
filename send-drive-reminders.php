<?php
include 'connection.php';
include 'mailer-config.php';

date_default_timezone_set(MAILER_TIMEZONE);

function loadPhpMailerOrExit() {
  $autoload = __DIR__ . '/vendor/autoload.php';
  if (file_exists($autoload)) {
    require_once $autoload;
    return;
  }

  $phpMailerPath = __DIR__ . '/PHPMailer/src/';
  $required = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
  $allPresent = true;

  foreach ($required as $fileName) {
    if (!file_exists($phpMailerPath . $fileName)) {
      $allPresent = false;
      break;
    }
  }

  if ($allPresent) {
    require_once $phpMailerPath . 'Exception.php';
    require_once $phpMailerPath . 'PHPMailer.php';
    require_once $phpMailerPath . 'SMTP.php';
    return;
  }

  http_response_code(500);
  echo "PHPMailer not found. Install with Composer (composer require phpmailer/phpmailer) or place PHPMailer/src in project root." . PHP_EOL;
  exit;
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

function driveLabelFromKey($key) {
  $map = [
    'corbett' => 'Jim Corbett',
    'gir' => 'Gir Forest',
    'velas' => 'Velas Beach',
    'keoladeo' => 'Keoladeo National Park',
  ];

  return $map[$key] ?? ucfirst(str_replace('-', ' ', $key));
}

loadPhpMailerOrExit();

if (!ensureReminderColumns($con)) {
  http_response_code(500);
  echo "Failed to prepare drive_event_registrations schema." . PHP_EOL;
  exit;
}

$now = new DateTime();
$today = $now->format('Y-m-d');
$hour = (int) $now->format('H');

if ($hour < 9) {
  echo "It is before 9 AM. No reminders should be sent yet." . PHP_EOL;
  exit;
}

$query = "SELECT der.id, der.user_id, der.drive_key, der.drive_date, u.first_name, u.last_name, u.email
          FROM drive_event_registrations der
          INNER JOIN users u ON u.id = der.user_id
          WHERE der.drive_date = ?
            AND der.reminder_sent = 0
            AND u.email IS NOT NULL
            AND u.email <> ''";

$stmt = mysqli_prepare($con, $query);
if (!$stmt) {
  http_response_code(500);
  echo "Failed to prepare reminder query." . PHP_EOL;
  exit;
}

mysqli_stmt_bind_param($stmt, 's', $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
  http_response_code(500);
  echo "Failed to run reminder query." . PHP_EOL;
  exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
  $rows[] = $row;
}

if (empty($rows)) {
  echo "No pending reminders for today." . PHP_EOL;
  exit;
}

$sentCount = 0;
$failedCount = 0;
$mailerClass = 'PHPMailer\\PHPMailer\\PHPMailer';

foreach ($rows as $row) {
  $registrationId = (int) $row['id'];
  $email = trim((string) ($row['email'] ?? ''));
  $firstName = trim((string) ($row['first_name'] ?? 'Volunteer'));
  $lastName = trim((string) ($row['last_name'] ?? ''));
  $fullName = trim($firstName . ' ' . $lastName);
  $driveKey = trim((string) ($row['drive_key'] ?? ''));
  $driveDate = trim((string) ($row['drive_date'] ?? $today));
  $driveLabel = driveLabelFromKey($driveKey);

  $mail = new $mailerClass(true);

  try {
    $mail->isSMTP();
    $mail->Host = MAILER_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAILER_USERNAME;
    $mail->Password = MAILER_PASSWORD;

    if (MAILER_ENCRYPTION === 'ssl') {
      $mail->SMTPSecure = constant($mailerClass . '::ENCRYPTION_SMTPS');
    } else {
      $mail->SMTPSecure = constant($mailerClass . '::ENCRYPTION_STARTTLS');
    }

    $mail->Port = MAILER_PORT;
    $mail->setFrom(MAILER_FROM_EMAIL, MAILER_FROM_NAME);
    $mail->addAddress($email, $fullName !== '' ? $fullName : $firstName);

    $mail->isHTML(true);
    $mail->Subject = 'Drive Reminder: ' . $driveLabel . ' Today at 9 AM';
    $mail->Body = '<p>Hi ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ',</p>'
      . '<p>This is your reminder for today\'s conservation drive:</p>'
      . '<p><strong>Drive:</strong> ' . htmlspecialchars($driveLabel, ENT_QUOTES, 'UTF-8') . '<br>'
      . '<strong>Date:</strong> ' . htmlspecialchars($driveDate, ENT_QUOTES, 'UTF-8') . '</p>'
      . '<p>Thank you for contributing to Rekindle the Green.</p>'
      . '<p>See you at the drive!</p>';
    $mail->AltBody = 'Hi ' . $firstName . ', This is your reminder for today\'s conservation drive: ' . $driveLabel . ' on ' . $driveDate . '. Thank you for contributing to Rekindle the Green.';

    $mail->send();

    $update = mysqli_prepare($con, "UPDATE drive_event_registrations SET reminder_sent = 1, reminder_sent_at = NOW() WHERE id = ?");
    if ($update) {
      mysqli_stmt_bind_param($update, 'i', $registrationId);
      mysqli_stmt_execute($update);
      mysqli_stmt_close($update);
    }

    $sentCount++;
    echo 'Sent reminder to ' . $email . PHP_EOL;
  } catch (\Throwable $e) {
    $failedCount++;
    echo 'Failed for ' . $email . ': ' . $mail->ErrorInfo . PHP_EOL;
  }
}

echo 'Done. Sent: ' . $sentCount . ', Failed: ' . $failedCount . PHP_EOL;
