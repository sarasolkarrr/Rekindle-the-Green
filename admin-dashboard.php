<?php
include 'connection.php';
session_start();

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
    'keoladeo' => 'bird',
    'keoladeo national park' => 'bird',
    'bird' => 'bird',
  ];

  foreach ($map as $needle => $key) {
    if (strpos($value, $needle) !== false) {
      return $key;
    }
  }

  return $value;
}

function driveMatchesAdmin($userDrive, $adminDriveKey, $adminDriveLabel) {
  $userDriveKey = normalizeDriveKey($userDrive);
  $labelKey = normalizeDriveKey($adminDriveLabel);

  return $userDriveKey !== '' && (
    $userDriveKey === $adminDriveKey ||
    $userDriveKey === $labelKey ||
    strtolower(trim((string) $userDrive)) === strtolower(trim((string) $adminDriveLabel))
  );
}

if (!isset($_SESSION['admin_id'])) {
  if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
  } else {
    header('Location: login.php');
  }
  exit;
}

$adminId = (int) $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminDriveId = (int) ($_SESSION['admin_drive_id'] ?? 0);
$adminDriveLabel = $_SESSION['admin_drive_name'] ?? '';
$adminDriveKey = $_SESSION['admin_drive_key'] ?? normalizeDriveKey($adminDriveLabel);

$driveRow = null;
if ($adminDriveId > 0) {
  $driveResult = mysqli_query($con, "SELECT * FROM drives WHERE id=$adminDriveId LIMIT 1");
  if ($driveResult && mysqli_num_rows($driveResult) === 1) {
    $driveRow = mysqli_fetch_assoc($driveResult);
  }
}

if ($driveRow) {
  $adminDriveLabel = $driveRow['location'] ?? $adminDriveLabel;
  $adminDriveKey = normalizeDriveKey($adminDriveLabel);
}

$usersResult = mysqli_query($con, "SELECT * FROM users ORDER BY registration_date DESC, id DESC");
$users = [];
if ($usersResult) {
  while ($row = mysqli_fetch_assoc($usersResult)) {
    if (driveMatchesAdmin($row['conservation_drive'] ?? '', $adminDriveKey, $adminDriveLabel)) {
      $users[] = $row;
    }
  }
}

$driveTitle = $adminDriveLabel !== '' ? $adminDriveLabel : 'Assigned Drive';
$driveDate = $driveRow['date'] ?? ($driveRow['created_at'] ?? '');
$totalUsers = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet" />
  <title>Admin Dashboard — Rekindle the Green</title>
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

    .shell {
      position: relative;
      z-index: 1;
      width: min(1180px, calc(100% - 2rem));
      margin: 0 auto;
      padding: 1.2rem 0 2.5rem;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1rem 0 1.25rem;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      text-decoration: none;
      color: inherit;
    }

    .brand-mark {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      background: linear-gradient(145deg, #c9a84c, #8d6c1d);
      display: grid;
      place-items: center;
      color: #172517;
      font-weight: 800;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }

    .brand-text h1 {
      margin: 0;
      font-size: 1.05rem;
      letter-spacing: -0.02em;
    }

    .brand-text p {
      margin: 0.15rem 0 0;
      font-size: 0.76rem;
      color: rgba(245, 247, 242, 0.62);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .logout {
      text-decoration: none;
      color: #f5f7f2;
      border: 1px solid rgba(255, 255, 255, 0.18);
      background: rgba(255, 255, 255, 0.05);
      padding: 0.7rem 1rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .quick-actions {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      flex-wrap: wrap;
    }

    .action-btn {
      text-decoration: none;
      color: #1f2d1f;
      background: #e4c67a;
      border: 1px solid rgba(228, 198, 122, 0.7);
      padding: 0.72rem 1.05rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.01em;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .action-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
    }

    .hero {
      display: grid;
      grid-template-columns: 1.35fr 0.9fr;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .panel {
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(7, 16, 7, 0.74);
      backdrop-filter: blur(14px);
      border-radius: 22px;
      box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
    }

    .hero-main {
      padding: 1.4rem;
      position: relative;
      overflow: hidden;
    }

    .hero-main::after {
      content: '';
      position: absolute;
      inset: auto -12% -40% auto;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(201, 168, 76, 0.24), transparent 66%);
      pointer-events: none;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.16em;
      color: rgba(201, 168, 76, 0.9);
      margin-bottom: 0.8rem;
    }

    .hero-main h2 {
      margin: 0;
      font-size: clamp(1.65rem, 4vw, 2.75rem);
      line-height: 1.05;
      max-width: 14ch;
    }

    .hero-main p {
      margin: 0.9rem 0 0;
      max-width: 62ch;
      line-height: 1.65;
      color: rgba(245, 247, 242, 0.76);
    }

    .hero-side {
      display: grid;
      gap: 1rem;
    }

    .stat-card {
      padding: 1.15rem;
    }

    .stat-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: rgba(245, 247, 242, 0.55);
      margin-bottom: 0.55rem;
    }

    .stat-value {
      font-size: 1.7rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      color: #fff;
    }

    .stat-sub {
      margin-top: 0.45rem;
      color: rgba(245, 247, 242, 0.68);
      line-height: 1.5;
      font-size: 0.88rem;
    }

    .table-panel {
      padding: 1rem;
    }

    .section-head {
      display: flex;
      align-items: end;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.2rem 0.2rem 1rem;
    }

    .section-head h3 {
      margin: 0;
      font-size: 1.05rem;
    }

    .section-head span {
      font-size: 0.82rem;
      color: rgba(245, 247, 242, 0.62);
    }

    .table-wrap {
      overflow: auto;
      border-radius: 18px;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 860px;
      background: rgba(10, 18, 10, 0.9);
    }

    thead th {
      text-align: left;
      padding: 0.95rem 1rem;
      font-size: 0.72rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(245, 247, 242, 0.6);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }

    tbody td {
      padding: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      color: #eef4ea;
      vertical-align: top;
      font-size: 0.92rem;
    }

    tbody tr:hover {
      background: rgba(255, 255, 255, 0.03);
    }

    .muted {
      color: rgba(245, 247, 242, 0.58);
      font-size: 0.82rem;
    }

    .empty {
      padding: 2rem 1rem 1.8rem;
      color: rgba(245, 247, 242, 0.72);
      text-align: center;
    }

    .pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.34rem 0.65rem;
      border-radius: 999px;
      background: rgba(201, 168, 76, 0.14);
      color: #f4da8d;
      font-size: 0.76rem;
      font-weight: 700;
      letter-spacing: 0.04em;
    }

    @media (max-width: 900px) {
      .hero { grid-template-columns: 1fr; }
      .topbar { align-items: flex-start; flex-direction: column; }
    }
  </style>
</head>
<body>
  <div class="shell">
    <div class="topbar">
      <a class="brand" href="index.html">
        <div class="brand-mark">RG</div>
        <div class="brand-text">
          <h1>Rekindle the Green</h1>
          <p>Drive admin dashboard</p>
        </div>
      </a>
      <div class="quick-actions">
        <a class="action-btn" href="admin-reminder-test.php">Reminder Dry Run</a>
        <a class="logout" href="logout.php">Log Out</a>
      </div>
    </div>

    <div class="hero">
      <section class="panel hero-main">
        <div class="eyebrow">Admin access</div>
        <h2>Hello, <?= htmlspecialchars($adminName) ?></h2>
        <p>
          This dashboard shows everyone who has registered for <?= htmlspecialchars($driveTitle) ?>.
          Use it to review sign-ups for your assigned conservation drive.
        </p>
      </section>

      <div class="hero-side">
        <section class="panel stat-card">
          <div class="stat-label">Assigned drive</div>
          <div class="stat-value"><?= htmlspecialchars($driveTitle) ?></div>
          <div class="stat-sub">
            Drive ID: <span class="pill">#<?= (int) $adminDriveId ?></span>
            <?php if ($driveDate !== ''): ?>
              <div style="margin-top:0.6rem;">Drive date: <?= htmlspecialchars($driveDate) ?></div>
            <?php endif; ?>
          </div>
        </section>

        <section class="panel stat-card">
          <div class="stat-label">Registered users</div>
          <div class="stat-value"><?= (int) $totalUsers ?></div>
          <div class="stat-sub">Users matched by their conservation drive selection.</div>
        </section>
      </div>
    </div>

    <section class="panel table-panel">
      <div class="section-head">
        <div>
          <h3>User registrations</h3>
          <span>Only users registered for this drive are shown.</span>
        </div>
        <span class="pill"><?= htmlspecialchars($driveTitle) ?></span>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Drive</th>
              <th>Registered</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($totalUsers > 0): ?>
              <?php foreach ($users as $index => $user): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                  <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($user['conservation_drive'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($user['registration_date'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($user['updated_at'] ?? '—') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">
                  <div class="empty">No users have registered for this drive yet.</div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</body>
</html>