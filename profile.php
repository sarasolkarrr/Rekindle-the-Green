<?php
include 'connection.php';
session_start();

function tableExists($con, $tableName) {
  $safeName = mysqli_real_escape_string($con, $tableName);
  $result = mysqli_query($con, "SHOW TABLES LIKE '$safeName'");
  return $result && mysqli_num_rows($result) > 0;
}

function firstExistingTable($con, $candidates) {
  foreach ($candidates as $tableName) {
    if (tableExists($con, $tableName)) {
      return $tableName;
    }
  }
  return null;
}

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

function jsonResponse($payload) {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($payload);
  exit;
}

$adminTable = firstExistingTable($con, ['admin', 'admins']);
$driveTable = firstExistingTable($con, ['drives', 'drive']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
  $fname = mysqli_real_escape_string($con, $_POST['fname']);
  $lname = mysqli_real_escape_string($con, $_POST['lname']);
  $email = mysqli_real_escape_string($con, $_POST['email']);
  $phone = mysqli_real_escape_string($con, $_POST['code'] . $_POST['phone']);
  $drive = mysqli_real_escape_string($con, $_POST['drive']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
  if (mysqli_num_rows($checkEmail) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
  }

  $query = "INSERT INTO users (first_name, last_name, email, phone, password, conservation_drive, registration_date)
              VALUES ('$fname', '$lname', '$email', '$phone', '$password', '$drive', NOW())";

  if (mysqli_query($con, $query)) {
    $newId = mysqli_insert_id($con);
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $fname;
    $_SESSION['user_drive'] = $drive;
    $_SESSION['user_role'] = 'user';
    jsonResponse(['success' => true, 'message' => 'Registration successful', 'drive' => $drive, 'name' => $fname, 'id' => $newId, 'role' => 'user', 'redirect' => 'profile.php']);
  } else {
    jsonResponse(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($con)]);
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
  $email = mysqli_real_escape_string($con, $_POST['email']);
  $password = $_POST['password'];
  $role = isset($_POST['role']) ? strtolower(trim($_POST['role'])) : 'user';

  if ($role === 'admin') {
    if (!$adminTable) {
      jsonResponse(['success' => false, 'message' => 'Admin account table not found']);
    }

    $query = "SELECT * FROM `$adminTable` WHERE email='$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) === 1) {
      $admin = mysqli_fetch_assoc($result);
      if (password_verify($password, $admin['password'])) {
        $adminId = (int) ($admin['id'] ?? 0);
        $adminName = trim((string) ($admin['name'] ?? ''));

        if ($adminName === '') {
          $adminName = trim((string) (($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')));
        }

        if ($adminName === '') {
          $adminName = 'Admin';
        }

        $driveId = (int) ($admin['drive_id'] ?? ($admin['drive'] ?? 0));
        $driveRow = null;

        if ($driveTable && $driveId > 0) {
          $driveResult = mysqli_query($con, "SELECT * FROM `$driveTable` WHERE id=$driveId LIMIT 1");
          if ($driveResult && mysqli_num_rows($driveResult) === 1) {
            $driveRow = mysqli_fetch_assoc($driveResult);
          }
        }

        $driveLabel = $driveRow['location'] ?? ($driveRow['name'] ?? '');
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_name'] = $adminName;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_drive_id'] = $driveId;
        $_SESSION['admin_drive_name'] = $driveLabel;
        $_SESSION['admin_drive_key'] = normalizeDriveKey($driveLabel);
        $_SESSION['user_role'] = 'admin';

        jsonResponse([
          'success' => true,
          'message' => 'Admin login successful',
          'role' => 'admin',
          'name' => $adminName,
          'id' => $adminId,
          'drive_id' => $driveId,
          'redirect' => 'admin-dashboard.php'
        ]);
      }

      jsonResponse(['success' => false, 'message' => 'Invalid password']);
    }

    jsonResponse(['success' => false, 'message' => 'Admin not found']);
  }

  $query = "SELECT id, first_name, last_name, password, conservation_drive FROM users WHERE email='$email'";
  $result = mysqli_query($con, $query);

  if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_email'] = $email;
      $_SESSION['user_name'] = $user['first_name'];
      $_SESSION['user_lname'] = $user['last_name'];
      $_SESSION['user_drive'] = $user['conservation_drive'];
      $_SESSION['user_role'] = 'user';
      jsonResponse(['success' => true, 'message' => 'Login successful', 'role' => 'user', 'name' => $user['first_name'], 'id' => $user['id'], 'redirect' => 'profile.php']);
    } else {
      jsonResponse(['success' => false, 'message' => 'Invalid password']);
    }
  } else {
    jsonResponse(['success' => false, 'message' => 'User not found']);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logout'])) {
  session_destroy();
  header('Location: index.html');
  exit;
}

if (isset($_SESSION['admin_id'])) {
  header('Location: admin-dashboard.php');
  exit;
}


if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}


header_remove('Content-Type');
header('Content-Type: text/html; charset=UTF-8');

$uid = intval($_SESSION['user_id']);
$result = mysqli_query($con, "SELECT * FROM users WHERE id=$uid");
$user = mysqli_fetch_assoc($result);

$driveLabels = [
  'corbett' => ['name' => 'Jim Corbett National Park', 'sub' => 'Tiger Conservation — Uttarakhand', 'page' => 'tiger.html', 'color' => '#c0392b'],
  'velas' => ['name' => 'Velas Beach', 'sub' => 'Turtle Conservation — Maharashtra', 'page' => 'turtle.html', 'color' => '#27ae60'],
  'gir' => ['name' => 'Gir National Park', 'sub' => 'Lion Conservation — Gujarat', 'page' => 'lion.html', 'color' => '#e67e22'],
  'keoladeo' => ['name' => 'Keoladeo National Park', 'sub' => 'Bird Conservation — Rajasthan', 'page' => 'bird.html', 'color' => '#2a8ae0'],
];

$driveKey = $user['conservation_drive'] ?? '';
$driveInfo = $driveLabels[$driveKey] ?? ['name' => 'No drive selected', 'sub' => '', 'page' => 'index.html', 'color' => '#3d6b2a'];

$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$joinDate = date('F j, Y', strtotime($user['registration_date']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,700&display=swap"
    rel="stylesheet">
   <script src="navbar-loader.js" defer></script>
  <title><?= htmlspecialchars($user['first_name']) ?>'s Profile — Rekindle the Green</title>

  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

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
      background: rgba(8, 18, 8, 0.72);
      z-index: 0;
    }


    .page-wrap {
      position: relative;
      z-index: 1;
      flex: 1;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 80px 1rem 2.5rem;
      gap: 1.2rem;
    }


    .profile-card {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 16px;
      width: 100%;
      max-width: 360px;
      overflow: hidden;
      box-shadow: 0 16px 50px rgba(0, 0, 0, 0.4);
      flex-shrink: 0;
    }

    .profile-banner {
      height: 80px;
      background: linear-gradient(135deg, #1a2e1a 0%, #3d6b2a 60%, #2d5a1a 100%);
      position: relative;
      display: flex;
      align-items: flex-end;
      padding: 0 1.5rem 0;
    }

    .avatar {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: #3d6b2a;
      border: 4px solid #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff;
      position: absolute;
      bottom: -36px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .profile-body {
      padding: 2.8rem 1.5rem 1.5rem;
    }

    .profile-name {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1a2e1a;
      letter-spacing: -0.02em;
      margin-bottom: 0.15rem;
    }

    .profile-email {
      font-size: 0.8rem;
      color: #999;
      margin-bottom: 1.2rem;
    }


    .info-row {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      padding: 0.65rem 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.83rem;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      font-size: 0.68rem;
      color: #bbb;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      display: block;
      margin-bottom: 1px;
    }

    .info-val {
      color: #333;
      font-weight: 500;
    }

    .drive-badge {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1rem;
      border-radius: 10px;
      background: #f5faf3;
      border: 1.5px solid #d5ead0;
      margin: 1.1rem 0 1.3rem;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s;
      text-decoration: none;
    }

    .drive-badge:hover {
      background: #ecf5e8;
      box-shadow: 0 3px 12px rgba(61, 107, 42, 0.15);
    }

    .drive-badge .d-text {}

    .drive-badge .d-name {
      font-size: 0.85rem;
      font-weight: 700;
      color: #1a2e1a;
    }

    .drive-badge .d-sub {
      font-size: 0.72rem;
      color: #777;
      margin-top: 1px;
    }

    .drive-badge .d-arrow {
      margin-left: auto;
      font-size: 0.9rem;
      color: #ccc;
    }


    .profile-actions {
      display: flex;
      gap: 0.6rem;
      margin-top: 0.3rem;
    }

    .action-btn {
      flex: 1;
      padding: 0.65rem 0.5rem;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      transition: all 0.18s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      border: none;
    }

    .action-primary {
      background: #3d6b2a;
      color: #fff;
    }

    .action-primary:hover {
      background: #2f5220;
    }

    .action-danger {
      background: #fef2f2;
      color: #c0392b;
      border: 1.5px solid #f5c0bb;
    }

    .action-danger:hover {
      background: #fee5e5;
    }

    .stats-card {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 16px;
      padding: 1.5rem;
      width: 100%;
      max-width: 320px;
      box-shadow: 0 16px 50px rgba(0, 0, 0, 0.35);
    }

    .stats-title {
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #bbb;
      margin-bottom: 1rem;
      padding-bottom: 0.6rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .stat-item {
      display: flex;
      align-items: center;
      padding: 0.7rem 0;
      border-bottom: 1px solid #f6f6f6;
    }

    .stat-item:last-child {
      border-bottom: none;
    }

    .stat-label {
      font-size: 0.72rem;
      color: #aaa;
      margin-bottom: 2px;
    }

    .stat-val {
      font-size: 0.9rem;
      font-weight: 700;
      color: #1a2e1a;
    }

    .quick-links {
      margin-top: 1.2rem;
    }

    .quick-link {
      display: flex;
      align-items: center;
      padding: 0.65rem 0.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.82rem;
      font-weight: 500;
      color: #444;
      transition: background 0.15s;
      margin-bottom: 0.3rem;
    }

    .quick-link:hover {
      background: #f5f5f5;
    }

    .quick-link .ql-arrow {
      margin-left: auto;
      color: #ddd;
      font-size: 0.85rem;
    }

    @media (max-width: 740px) {
      .page-wrap {
        flex-direction: column;
        align-items: center;
      }

      .stats-card {
        max-width: 360px;
      }
    }
    nav {
      position: fixed; top: 0; left: 0; right: 0; height: 50px;
      background: #1a2e1a; display: flex; align-items: center;
      justify-content: space-between; padding: 0 1.2rem;
      z-index: 1000; border-bottom: 1px solid rgba(212,175,55,0.25);
      box-shadow: 0 2px 12px rgba(0,0,0,0.4);
    }
    .nav-logo { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
    .nav-logo-text { font-family: 'Inter', sans-serif; font-weight: 700; font-size: 1rem; color: #fff; white-space: nowrap; }
    .nav-center {
      position: absolute; left: 50%; transform: translateX(-50%);
      font-size: 0.6rem; font-weight: 600; letter-spacing: 0.18em;
      text-transform: uppercase; color: rgba(255,255,255,0.45);
      pointer-events: none; white-space: nowrap; font-family: 'Inter', sans-serif;
    }
    .nav-actions { display: flex; gap: 0.5rem; }
    .btn-nav {
      padding: 0.38rem 1rem; border-radius: 5px; font-family: 'Inter', sans-serif;
      font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: none;
      transition: all 0.18s; display: inline-block; border: none;
    }
    .btn-login { background: transparent; color: rgba(255,255,255,0.75); border: 1px solid rgba(255,255,255,0.2); }
    .btn-login:hover { color: #fff; border-color: rgba(255,255,255,0.5); }
    .btn-signup { background: var(--gold); color: #1a2e1a; }
    .btn-signup:hover { background: var(--gold-light); }

  </style>
</head>

<body>

  <nav>
    <a class="nav-logo" href="index.html"><span class="nav-logo-text">Rekindle the Green</span></a>
    <span class="nav-center">Wildlife Conservation India</span>
    <div class="nav-actions">
      <a href="login.php" class="btn-nav btn-login">Log In</a>
      <a href="signup.php" class="btn-nav btn-signup">Sign Up</a>
    </div>
  </nav>

  <script>
    // Sync localStorage from PHP session so navbar shows avatar on direct profile visits
    localStorage.setItem('rtg_user_name', <?= json_encode($user['first_name']) ?>);
    localStorage.setItem('rtg_user_id', <?= json_encode((string)$user['id']) ?>);
  </script>

  <div class="page-wrap">

    <div class="profile-card">
      <div class="profile-banner">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>

      <div class="profile-body">
        <div class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>

        <a class="drive-badge" href="<?= htmlspecialchars($driveInfo['page']) ?>">
          <div class="d-text">
            <div class="d-name"><?= htmlspecialchars($driveInfo['name']) ?></div>
            <div class="d-sub"><?= htmlspecialchars($driveInfo['sub']) ?></div>
          </div>
          <span class="d-arrow">›</span>
        </a>

        <div class="info-row">
          <div>
            <span class="info-label">Phone</span>
            <span class="info-val"><?= htmlspecialchars($user['phone'] ?? '—') ?></span>
          </div>
        </div>
        <div class="info-row">
          <div>
            <span class="info-label">Member since</span>
            <span class="info-val"><?= $joinDate ?></span>
          </div>
        </div>

        <div class="profile-actions" style="margin-top:1.2rem;">
          <a href="index.html" class="action-btn action-primary">View Map</a>
          <a href="logout.php" class="action-btn action-danger" onclick="localStorage.removeItem('rtg_user_name'); localStorage.removeItem('rtg_user_id'); localStorage.removeItem('rtg_admin_name'); localStorage.removeItem('rtg_admin_id');">Sign Out</a>
        </div>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-title">Your Conservation Hub</div>

      <div class="stat-item">
        <div>
          <div class="stat-label">Conservation Drive</div>
          <div class="stat-val"><?= htmlspecialchars($driveInfo['name']) ?></div>
        </div>
      </div>

      <div class="stat-item">
        <div>
          <div class="stat-label">Member Since</div>
          <div class="stat-val"><?= $joinDate ?></div>
        </div>
      </div>

      <div class="stat-item">
        <div>
          <div class="stat-label">Member ID</div>
          <div class="stat-val">#<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>
      </div>

      <div class="quick-links" style="margin-top:1.2rem;">
        <div class="stats-title" style="margin-top:0;">Quick Links</div>

        <a href="<?= htmlspecialchars($driveInfo['page']) ?>" class="quick-link">
          My Conservation Drive
          <span class="ql-arrow">›</span>
        </a>
        <a href="index.html" class="quick-link">
          Explore the Map
          <span class="ql-arrow">›</span>
        </a>
        <a href="tiger.html" class="quick-link">
          Jim Corbett - Tigers
          <span class="ql-arrow">›</span>
        </a>
        <a href="lion.html" class="quick-link">
          Gir Forest - Lions
          <span class="ql-arrow">›</span>
        </a>
        <a href="turtle.html" class="quick-link">
          Velas Beach - Turtles
          <span class="ql-arrow">›</span>
        </a>
        <a href="bird.html" class="quick-link">
          Keoladeo - Birds
          <span class="ql-arrow">›</span>
        </a>
      </div>
    </div>

  </div>

</body>

</html>