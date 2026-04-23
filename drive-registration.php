<?php
include 'connection.php';
session_start();

$driveOptions = [
  'corbett' => 'Jim Corbett National Park - Tiger Conservation',
  'gir' => 'Gir National Park - Lion Conservation',
  'velas' => 'Velas Beach - Turtle Conservation',
  'keoladeo' => 'Keoladeo National Park - Bird Conservation',
];

$driveDates = [
  'corbett' => ['2026-05-03', '2026-05-14', '2026-05-27', '2026-06-08'],
  'gir' => ['2026-05-05', '2026-05-18', '2026-05-29', '2026-06-12'],
  'velas' => ['2026-05-07', '2026-05-19', '2026-06-01', '2026-06-10'],
  'keoladeo' => ['2026-05-09', '2026-05-21', '2026-06-04', '2026-06-14'],
];

$flashType = '';
$flashTitle = '';
$flashMessage = '';

$selectedDrive = isset($_GET['drive']) ? strtolower(trim($_GET['drive'])) : 'corbett';
if (!array_key_exists($selectedDrive, $driveOptions)) {
  $selectedDrive = 'corbett';
}

$selectedDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_drive') {
  $selectedDrive = isset($_POST['drive']) ? strtolower(trim($_POST['drive'])) : '';
  $selectedDate = isset($_POST['drive_date']) ? trim($_POST['drive_date']) : '';

  if (!isset($_SESSION['user_id'])) {
    $flashType = 'error';
    $flashTitle = 'Sign in required';
    $flashMessage = 'Please sign in before registering for a drive day.';
  } elseif (!array_key_exists($selectedDrive, $driveOptions)) {
    $flashType = 'error';
    $flashTitle = 'Invalid drive';
    $flashMessage = 'Please choose a valid conservation drive.';
  } elseif (!in_array($selectedDate, $driveDates[$selectedDrive], true)) {
    $flashType = 'error';
    $flashTitle = 'Invalid date';
    $flashMessage = 'Please choose one of the marked drive dates for the selected drive.';
  } else {
    $createTableSql = "CREATE TABLE IF NOT EXISTS drive_event_registrations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      drive_key VARCHAR(50) NOT NULL,
      drive_date DATE NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_user_drive_date (user_id, drive_key, drive_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!mysqli_query($con, $createTableSql)) {
      $flashType = 'error';
      $flashTitle = 'Database error';
      $flashMessage = 'Could not prepare registration table. Please try again.';
    } else {
      $insertSql = "INSERT INTO drive_event_registrations (user_id, drive_key, drive_date) VALUES (?, ?, ?)";
      $stmt = mysqli_prepare($con, $insertSql);

      if (!$stmt) {
        $flashType = 'error';
        $flashTitle = 'Database error';
        $flashMessage = 'Could not create registration request.';
      } else {
        $userId = (int) $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, 'iss', $userId, $selectedDrive, $selectedDate);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
          $flashType = 'success';
          $flashTitle = 'Drive registration confirmed';
          $flashMessage = 'You are registered for ' . $driveOptions[$selectedDrive] . ' on ' . $selectedDate . '.';
        } else {
          if (mysqli_errno($con) === 1062) {
            $flashType = 'error';
            $flashTitle = 'Already registered';
            $flashMessage = 'You have already registered for this drive on this date.';
          } else {
            $flashType = 'error';
            $flashTitle = 'Registration failed';
            $flashMessage = 'Something went wrong while saving your registration.';
          }
        }

        mysqli_stmt_close($stmt);
      }
    }
  }
}

$userName = $_SESSION['user_name'] ?? '';
$userId = $_SESSION['user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
  <script src="navbar-loader.js" defer></script>
  <title>Drive Registration - Rekindle the Green</title>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      background-image: url('jimc.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-color: #1a2e1a;
      display: flex;
      flex-direction: column;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: rgba(8, 18, 8, 0.68);
      z-index: 0;
    }

    .page-wrap {
      position: relative;
      z-index: 1;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 86px 1rem 2rem;
    }

    .card {
      background: rgba(255,255,255,0.96);
      border-radius: 14px;
      width: 100%;
      max-width: 540px;
      padding: 2rem 1.7rem 1.8rem;
      box-shadow: 0 16px 50px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.1);
    }

    .card-header { text-align: center; margin-bottom: 1.2rem; }

    .site-label {
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #4a7a2a;
      margin-bottom: 0.5rem;
    }

    .card-header h1 {
      font-size: 1.45rem;
      font-weight: 700;
      color: #1a2e1a;
      letter-spacing: -0.02em;
      margin-bottom: 0.25rem;
    }

    .card-header p {
      font-size: 0.8rem;
      color: #888;
    }

    .divider {
      height: 1px;
      background: #eee;
      margin: 0 0 1rem;
    }

    .field { margin-bottom: 0.95rem; }

    .field label {
      display: block;
      font-size: 0.72rem;
      font-weight: 600;
      color: #444;
      margin-bottom: 0.38rem;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }

    .field select,
    .field input {
      width: 100%;
      padding: 0.7rem 0.9rem;
      border: 1.5px solid #ddd;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 0.88rem;
      color: #1a1a1a;
      background: #fafafa;
      outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }

    .field select:focus,
    .field input:focus {
      border-color: #3d6b2a;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(61,107,42,0.1);
    }

    .calendar-wrap {
      border: 1.5px solid #e5e5e5;
      border-radius: 10px;
      background: #fafafa;
      padding: 0.75rem;
      margin-bottom: 0.95rem;
    }

    .cal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.7rem;
    }

    .cal-title {
      font-size: 0.82rem;
      font-weight: 700;
      color: #1a2e1a;
      letter-spacing: 0.02em;
    }

    .cal-btn {
      border: 1px solid #d8d8d8;
      background: #fff;
      color: #555;
      width: 28px;
      height: 28px;
      border-radius: 7px;
      cursor: pointer;
      font-weight: 700;
      line-height: 1;
    }

    .cal-grid {
      display: grid;
      grid-template-columns: repeat(7, minmax(0, 1fr));
      gap: 0.35rem;
    }

    .cal-weekday {
      text-align: center;
      font-size: 0.64rem;
      color: #999;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding-bottom: 0.25rem;
    }

    .cal-day {
      height: 34px;
      border-radius: 8px;
      border: 1px solid #e6e6e6;
      background: #fff;
      font-size: 0.75rem;
      color: #444;
      cursor: default;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cal-day.blank {
      background: transparent;
      border-color: transparent;
    }

    .cal-day.drive-day {
      border-color: rgba(61,107,42,0.45);
      background: #f0f7ec;
      cursor: pointer;
      font-weight: 700;
      color: #2f5220;
      box-shadow: inset 0 0 0 1px rgba(61,107,42,0.14);
    }

    .cal-day.selected {
      background: #3d6b2a;
      border-color: #3d6b2a;
      color: #fff;
      box-shadow: 0 4px 12px rgba(61,107,42,0.3);
    }

    .legend {
      margin-top: 0.65rem;
      font-size: 0.71rem;
      color: #6d6d6d;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    .legend-dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;
      background: #f0f7ec;
      border: 1px solid rgba(61,107,42,0.45);
      display: inline-block;
    }

    .date-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.42rem;
      margin-top: 0.6rem;
    }

    .date-chip {
      border: 1px solid #d8dfd4;
      background: #fff;
      color: #4f5a4a;
      font-size: 0.7rem;
      border-radius: 999px;
      padding: 0.3rem 0.55rem;
      cursor: pointer;
    }

    .date-chip.active {
      border-color: #3d6b2a;
      background: #3d6b2a;
      color: #fff;
    }

    .btn-submit {
      width: 100%;
      padding: 0.8rem;
      background: #3d6b2a;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 0.2rem;
      transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    }

    .btn-submit:hover {
      background: #2f5220;
      box-shadow: 0 4px 14px rgba(61,107,42,0.35);
    }

    .btn-submit:active { transform: scale(0.99); }

    .card-footer {
      text-align: center;
      font-size: 0.78rem;
      color: #aaa;
      margin-top: 1rem;
      line-height: 1.5;
    }

    .card-footer a {
      color: #3d6b2a;
      font-weight: 600;
      text-decoration: none;
    }

    .card-footer a:hover { text-decoration: underline; }

    .msg {
      border-radius: 9px;
      padding: 0.72rem 0.78rem;
      margin-bottom: 0.95rem;
      font-size: 0.78rem;
      line-height: 1.5;
      border: 1px solid transparent;
    }

    .msg strong { display: block; margin-bottom: 0.12rem; }

    .msg.success {
      background: #f0f7ec;
      color: #2f5220;
      border-color: #cce3bf;
    }

    .msg.error {
      background: #fef2f2;
      color: #9e2f2f;
      border-color: #f3cccc;
    }

    .auth-actions {
      display: flex;
      gap: 0.55rem;
      margin-top: 1rem;
    }

    .auth-btn {
      flex: 1;
      border-radius: 8px;
      padding: 0.72rem;
      font-size: 0.82rem;
      font-weight: 600;
      text-decoration: none;
      text-align: center;
      transition: all 0.18s;
    }

    .auth-btn.primary {
      background: #3d6b2a;
      color: #fff;
    }

    .auth-btn.primary:hover { background: #2f5220; }

    .auth-btn.secondary {
      background: #fff;
      color: #3d6b2a;
      border: 1.5px solid #3d6b2a;
    }

    .auth-btn.secondary:hover { background: #f4faf1; }
  </style>
</head>
<body>
  <?php if (!empty($userName) && !empty($userId)): ?>
    <script>
      localStorage.setItem('rtg_user_name', <?= json_encode($userName) ?>);
      localStorage.setItem('rtg_user_id', <?= json_encode((string) $userId) ?>);
      localStorage.removeItem('rtg_admin_name');
      localStorage.removeItem('rtg_admin_id');
    </script>
  <?php endif; ?>

  <div class="page-wrap">
    <div class="card">
      <div class="card-header">
        <div class="site-label">Rekindle the Green</div>
        <h1>Drive Day Registration</h1>
        <p>Choose your conservation drive and a marked drive date.</p>
      </div>

      <div class="divider"></div>

      <?php if ($flashType !== ''): ?>
        <div class="msg <?= htmlspecialchars($flashType) ?>">
          <strong><?= htmlspecialchars($flashTitle) ?></strong>
          <?= htmlspecialchars($flashMessage) ?>
        </div>
      <?php endif; ?>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="msg error">
          <strong>Sign in required</strong>
          Please sign in before registering for a drive day event.
        </div>

        <div class="auth-actions">
          <a class="auth-btn primary" href="login.php">Log In</a>
          <a class="auth-btn secondary" href="signup.php">Sign Up</a>
        </div>

        <p class="card-footer">
          You can come back to this page after signing in.
        </p>
      <?php else: ?>
        <form method="POST" action="drive-registration.php?drive=<?= urlencode($selectedDrive) ?>">
          <input type="hidden" name="action" value="register_drive">

          <div class="field">
            <label for="drive">Conservation Drive</label>
            <select id="drive" name="drive" required>
              <?php foreach ($driveOptions as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $selectedDrive === $key ? 'selected' : '' ?>>
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="drive_date">Drive Day Date</label>
            <input id="drive_date" name="drive_date" type="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
          </div>

          <div class="calendar-wrap">
            <div class="cal-head">
              <button class="cal-btn" type="button" id="prevMonth" aria-label="Previous month">&lt;</button>
              <div class="cal-title" id="calendarTitle">Month Year</div>
              <button class="cal-btn" type="button" id="nextMonth" aria-label="Next month">&gt;</button>
            </div>
            <div class="cal-grid" id="calendarGrid"></div>
            <div class="legend"><span class="legend-dot"></span>Marked dates are drive days</div>
            <div class="date-list" id="dateList"></div>
          </div>

          <button class="btn-submit" type="submit">Register for Drive Day</button>
        </form>

        <p class="card-footer">
          Need to change your account drive preference? Visit <a href="profile.php">your profile</a>.
        </p>
      <?php endif; ?>
    </div>
  </div>

  <?php if (isset($_SESSION['user_id'])): ?>
  <script>
    (function () {
      var driveDates = <?= json_encode($driveDates) ?>;
      var driveSelect = document.getElementById('drive');
      var dateInput = document.getElementById('drive_date');
      var calendarGrid = document.getElementById('calendarGrid');
      var calendarTitle = document.getElementById('calendarTitle');
      var dateList = document.getElementById('dateList');
      var prevMonthBtn = document.getElementById('prevMonth');
      var nextMonthBtn = document.getElementById('nextMonth');

      var weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      var currentDate = new Date();

      function toIsoDate(dateObj) {
        var yyyy = dateObj.getFullYear();
        var mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        var dd = String(dateObj.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
      }

      function setInitialDateForDrive() {
        var dates = driveDates[driveSelect.value] || [];
        if (!dates.length) {
          dateInput.value = '';
          return;
        }

        if (dates.indexOf(dateInput.value) === -1) {
          dateInput.value = dates[0];
        }

        var preferred = new Date(dateInput.value + 'T00:00:00');
        if (!isNaN(preferred.getTime())) {
          currentDate = preferred;
        }
      }

      function renderDateChips(activeDrive, selectedIso) {
        var dates = driveDates[activeDrive] || [];
        dateList.innerHTML = '';

        dates.forEach(function (isoDate) {
          var chip = document.createElement('button');
          chip.type = 'button';
          chip.className = 'date-chip' + (isoDate === selectedIso ? ' active' : '');
          chip.textContent = isoDate;
          chip.addEventListener('click', function () {
            dateInput.value = isoDate;
            var picked = new Date(isoDate + 'T00:00:00');
            if (!isNaN(picked.getTime())) {
              currentDate = picked;
            }
            renderCalendar();
          });
          dateList.appendChild(chip);
        });
      }

      function renderCalendar() {
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth();
        var firstDay = new Date(year, month, 1);
        var firstWeekday = firstDay.getDay();
        var totalDays = new Date(year, month + 1, 0).getDate();
        var activeDrive = driveSelect.value;
        var validDates = driveDates[activeDrive] || [];
        var selectedIso = dateInput.value;

        calendarTitle.textContent = firstDay.toLocaleString('en-US', { month: 'long', year: 'numeric' });
        calendarGrid.innerHTML = '';

        weekDays.forEach(function (dayName) {
          var headCell = document.createElement('div');
          headCell.className = 'cal-weekday';
          headCell.textContent = dayName;
          calendarGrid.appendChild(headCell);
        });

        for (var i = 0; i < firstWeekday; i++) {
          var blank = document.createElement('div');
          blank.className = 'cal-day blank';
          calendarGrid.appendChild(blank);
        }

        for (var day = 1; day <= totalDays; day++) {
          var dayDate = new Date(year, month, day);
          var isoDate = toIsoDate(dayDate);
          var cell = document.createElement('button');
          cell.type = 'button';
          cell.className = 'cal-day';
          cell.textContent = day;

          if (validDates.indexOf(isoDate) !== -1) {
            cell.classList.add('drive-day');
            cell.addEventListener('click', function (pickedDate) {
              return function () {
                dateInput.value = pickedDate;
                renderCalendar();
              };
            }(isoDate));
          }

          if (isoDate === selectedIso) {
            cell.classList.add('selected');
          }

          calendarGrid.appendChild(cell);
        }

        renderDateChips(activeDrive, selectedIso);
      }

      driveSelect.addEventListener('change', function () {
        setInitialDateForDrive();
        renderCalendar();
      });

      dateInput.addEventListener('change', function () {
        var picked = new Date(dateInput.value + 'T00:00:00');
        if (!isNaN(picked.getTime())) {
          currentDate = picked;
          renderCalendar();
        }
      });

      prevMonthBtn.addEventListener('click', function () {
        currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
        renderCalendar();
      });

      nextMonthBtn.addEventListener('click', function () {
        currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
        renderCalendar();
      });

      setInitialDateForDrive();
      renderCalendar();
    }());
  </script>
  <?php endif; ?>
</body>
</html>
