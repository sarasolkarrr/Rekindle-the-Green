<?php
include 'connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $fname    = mysqli_real_escape_string($con, $_POST['fname']);
    $lname    = mysqli_real_escape_string($con, $_POST['lname']);
    $email    = mysqli_real_escape_string($con, $_POST['email']);
    $phone    = mysqli_real_escape_string($con, $_POST['code'] . $_POST['phone']);
    $drive    = mysqli_real_escape_string($con, $_POST['drive']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    $query = "INSERT INTO users (first_name, last_name, email, phone, password, conservation_drive, registration_date)
              VALUES ('$fname', '$lname', '$email', '$phone', '$password', '$drive', NOW())";

    if (mysqli_query($con, $query)) {
        $_SESSION['user_id']    = mysqli_insert_id($con);
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name']  = $fname;
        $_SESSION['user_drive'] = $drive;
        echo json_encode(['success' => true, 'message' => 'Registration successful', 'drive' => $drive]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($con)]);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email    = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT id, first_name, last_name, password, conservation_drive FROM users WHERE email='$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name']  = $user['first_name'];
            $_SESSION['user_lname'] = $user['last_name'];
            $_SESSION['user_drive'] = $user['conservation_drive'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.html');
    exit;
}


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


header_remove('Content-Type');
header('Content-Type: text/html; charset=UTF-8');

$uid    = intval($_SESSION['user_id']);
$result = mysqli_query($con, "SELECT * FROM users WHERE id=$uid");
$user   = mysqli_fetch_assoc($result);

$driveLabels = [
    'corbett' => ['name' => 'Jim Corbett National Park', 'emoji' => '🐯', 'sub'   => 'Tiger Conservation — Uttarakhand', 'page'  => 'tiger.html',  'color' => '#c0392b'],
    'velas'   => ['name' => 'Velas Beach',               'emoji' => '🐢', 'sub'   => 'Turtle Conservation — Maharashtra', 'page'  => 'turtle.html', 'color' => '#27ae60'],
    'gir'     => ['name' => 'Gir National Park',         'emoji' => '🦁', 'sub'   => 'Lion Conservation — Gujarat',       'page'  => 'lion.html',   'color' => '#e67e22'],
];

$driveKey  = $user['conservation_drive'] ?? '';
$driveInfo = $driveLabels[$driveKey] ?? ['name' => 'No drive selected', 'emoji' => '🌿', 'sub' => '', 'page' => 'index.html', 'color' => '#3d6b2a'];

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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
  <title><?= htmlspecialchars($user['first_name']) ?>'s Profile — Rekindle the Green</title>

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
      background: rgba(8, 18, 8, 0.72);
      z-index: 0;
    }


    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 54px;
      background: rgba(15, 28, 15, 0.95);
      backdrop-filter: blur(12px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1.5rem;
      z-index: 1000;
      border-bottom: 1px solid rgba(201,168,76,0.2);
      box-shadow: 0 2px 16px rgba(0,0,0,0.4);
    }

    .nav-logo {
      display: flex; align-items: center; gap: 0.5rem; text-decoration: none;
    }
    .nav-logo svg { width: 28px; height: 28px; flex-shrink: 0; }
    .nav-logo-text { font-family: 'Inter', sans-serif; font-weight: 700; font-size: 1rem; color: #fff; white-space: nowrap; }
    .nav-logo-text em { font-family: 'Playfair Display', serif; font-style: italic; color: #c9a84c; }

    .nav-center {
      position: absolute; left: 50%; transform: translateX(-50%);
      font-size: 0.58rem; font-weight: 600; letter-spacing: 0.18em;
      text-transform: uppercase; color: rgba(255,255,255,0.4);
      pointer-events: none; white-space: nowrap;
    }

    .nav-actions { display: flex; gap: 0.5rem; align-items: center; }

    .nav-user {
      display: flex; align-items: center; gap: 0.55rem;
      font-size: 0.78rem; color: rgba(255,255,255,0.75);
    }

    .nav-avatar {
      width: 30px; height: 30px; border-radius: 50%;
      background: #3d6b2a; border: 2px solid rgba(201,168,76,0.4);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.72rem; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }

    .btn-nav {
      padding: 0.38rem 0.9rem; border-radius: 5px;
      font-family: 'Inter', sans-serif; font-size: 0.73rem; font-weight: 600;
      cursor: pointer; text-decoration: none; transition: all 0.18s; display: inline-block;
    }
    .btn-map {
      background: transparent; color: rgba(255,255,255,0.6);
      border: 1.5px solid rgba(255,255,255,0.2);
    }
    .btn-map:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .btn-logout {
      background: transparent; color: #e88; border: 1.5px solid rgba(220,100,100,0.35);
    }
    .btn-logout:hover { background: rgba(220,100,100,0.1); color: #f99; }


    .page-wrap {
      position: relative; z-index: 1; flex: 1;
      display: flex; align-items: flex-start; justify-content: center;
      padding: 80px 1rem 2.5rem;
      gap: 1.2rem;
    }


    .profile-card {
      background: rgba(255,255,255,0.96);
      border-radius: 16px;
      width: 100%; max-width: 360px;
      overflow: hidden;
      box-shadow: 0 16px 50px rgba(0,0,0,0.4);
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
      width: 72px; height: 72px; border-radius: 50%;
      background: #3d6b2a;
      border: 4px solid #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; font-weight: 700; color: #fff;
      position: absolute;
      bottom: -36px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }

    .profile-body { padding: 2.8rem 1.5rem 1.5rem; }

    .profile-name {
      font-size: 1.25rem; font-weight: 700; color: #1a2e1a;
      letter-spacing: -0.02em; margin-bottom: 0.15rem;
    }

    .profile-email { font-size: 0.8rem; color: #999; margin-bottom: 1.2rem; }


    .info-row {
      display: flex; align-items: center; gap: 0.7rem;
      padding: 0.65rem 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.83rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon { font-size: 1rem; flex-shrink: 0; width: 22px; text-align: center; }
    .info-label { font-size: 0.68rem; color: #bbb; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 1px; }
    .info-val   { color: #333; font-weight: 500; }

    .drive-badge {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.9rem 1rem;
      border-radius: 10px;
      background: #f5faf3;
      border: 1.5px solid #d5ead0;
      margin: 1.1rem 0 1.3rem;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s;
      text-decoration: none;
    }
    .drive-badge:hover { background: #ecf5e8; box-shadow: 0 3px 12px rgba(61,107,42,0.15); }
    .drive-badge .d-emoji { font-size: 1.5rem; flex-shrink: 0; }
    .drive-badge .d-text {}
    .drive-badge .d-name { font-size: 0.85rem; font-weight: 700; color: #1a2e1a; }
    .drive-badge .d-sub  { font-size: 0.72rem; color: #777; margin-top: 1px; }
    .drive-badge .d-arrow { margin-left: auto; font-size: 0.9rem; color: #ccc; }


    .profile-actions { display: flex; gap: 0.6rem; margin-top: 0.3rem; }

    .action-btn {
      flex: 1; padding: 0.65rem 0.5rem; border-radius: 8px;
      font-family: 'Inter', sans-serif; font-size: 0.78rem; font-weight: 600;
      cursor: pointer; text-align: center; text-decoration: none;
      transition: all 0.18s; display: flex; align-items: center;
      justify-content: center; gap: 0.4rem;
      border: none;
    }
    .action-primary {
      background: #3d6b2a; color: #fff;
    }
    .action-primary:hover { background: #2f5220; }
    .action-danger {
      background: #fef2f2; color: #c0392b;
      border: 1.5px solid #f5c0bb;
    }
    .action-danger:hover { background: #fee5e5; }

    .stats-card {
      background: rgba(255,255,255,0.96);
      border-radius: 16px;
      padding: 1.5rem;
      width: 100%; max-width: 320px;
      box-shadow: 0 16px 50px rgba(0,0,0,0.35);
    }

    .stats-title {
      font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
      text-transform: uppercase; color: #bbb;
      margin-bottom: 1rem; padding-bottom: 0.6rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .stat-item {
      display: flex; align-items: center; gap: 0.8rem;
      padding: 0.7rem 0; border-bottom: 1px solid #f6f6f6;
    }
    .stat-item:last-child { border-bottom: none; }

    .stat-icon {
      width: 38px; height: 38px; border-radius: 10px;
      background: #f0f7ec; display: flex; align-items: center;
      justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    }

    .stat-label { font-size: 0.72rem; color: #aaa; margin-bottom: 2px; }
    .stat-val   { font-size: 0.9rem; font-weight: 700; color: #1a2e1a; }

    .quick-links { margin-top: 1.2rem; }
    .quick-link {
      display: flex; align-items: center; gap: 0.65rem;
      padding: 0.65rem 0.5rem; border-radius: 8px;
      text-decoration: none; font-size: 0.82rem; font-weight: 500;
      color: #444; transition: background 0.15s;
      margin-bottom: 0.3rem;
    }
    .quick-link:hover { background: #f5f5f5; }
    .quick-link .ql-icon { font-size: 1rem; width: 22px; text-align: center; flex-shrink: 0; }
    .quick-link .ql-arrow { margin-left: auto; color: #ddd; font-size: 0.85rem; }

    @media (max-width: 740px) {
      .page-wrap { flex-direction: column; align-items: center; }
      .stats-card { max-width: 360px; }
    }
  </style>
</head>
<body>

  <nav>
    <a class="nav-logo" href="index.html">
      <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 28 C10 28 14 18 26 10 C26 10 28 22 18 28 C18 28 15 30 10 28Z" fill="#4a8a2a"/>
        <path d="M18 28 C16 22 14 16 10 28Z" fill="#2d5a1a"/>
        <path d="M26 10 C24 16 20 22 18 28" stroke="#2d5a1a" stroke-width="1.5" fill="none"/>
        <path d="M16 32 C16 28 17 25 18 28" stroke="#4a8a2a" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <span class="nav-logo-text">Rekindle the Green</span>
    </a>

    <span class="nav-center">Wildlife Conservation India</span>

    <div class="nav-actions">
      <div class="nav-user">
        <div class="nav-avatar"><?= htmlspecialchars($initials) ?></div>
        <span><?= htmlspecialchars($user['first_name']) ?></span>
      </div>
      <a href="index.html" class="btn-nav btn-map">← Map</a>
      <a href="profile.php?logout=1" class="btn-nav btn-logout">Sign Out</a>
    </div>
  </nav>


  <div class="page-wrap">


    <div class="profile-card">
      <div class="profile-banner">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>

      <div class="profile-body">
        <div class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>

  
        <a class="drive-badge" href="<?= htmlspecialchars($driveInfo['page']) ?>">
          <span class="d-emoji"><?= $driveInfo['emoji'] ?></span>
          <div class="d-text">
            <div class="d-name"><?= htmlspecialchars($driveInfo['name']) ?></div>
            <div class="d-sub"><?= htmlspecialchars($driveInfo['sub']) ?></div>
          </div>
          <span class="d-arrow">›</span>
        </a>

        <div class="info-row">
          <span class="info-icon">📞</span>
          <div>
            <span class="info-label">Phone</span>
            <span class="info-val"><?= htmlspecialchars($user['phone'] ?? '—') ?></span>
          </div>
        </div>
        <div class="info-row">
          <span class="info-icon">📅</span>
          <div>
            <span class="info-label">Member since</span>
            <span class="info-val"><?= $joinDate ?></span>
          </div>
        </div>


        <div class="profile-actions" style="margin-top:1.2rem;">
          <a href="index.html" class="action-btn action-primary">🗺️ View Map</a>
          <a href="profile.php?logout=1" class="action-btn action-danger">Sign Out</a>
        </div>
      </div>
    </div>


    <div class="stats-card">
      <div class="stats-title">Your Conservation Hub</div>

      <div class="stat-item">
        <div class="stat-icon">🌿</div>
        <div>
          <div class="stat-label">Conservation Drive</div>
          <div class="stat-val"><?= $driveInfo['emoji'] ?> <?= htmlspecialchars($driveInfo['name']) ?></div>
        </div>
      </div>

      <div class="stat-item">
        <div class="stat-icon">📅</div>
        <div>
          <div class="stat-label">Member Since</div>
          <div class="stat-val"><?= $joinDate ?></div>
        </div>
      </div>

      <div class="stat-item">
        <div class="stat-icon">🪪</div>
        <div>
          <div class="stat-label">Member ID</div>
          <div class="stat-val">#<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>
      </div>

      <div class="quick-links" style="margin-top:1.2rem;">
        <div class="stats-title" style="margin-top:0;">Quick Links</div>

        <a href="<?= htmlspecialchars($driveInfo['page']) ?>" class="quick-link">
          <span class="ql-icon"><?= $driveInfo['emoji'] ?></span>
          My Conservation Drive
          <span class="ql-arrow">›</span>
        </a>
        <a href="index.html" class="quick-link">
          <span class="ql-icon">🗺️</span>
          Explore the Map
          <span class="ql-arrow">›</span>
        </a>
        <a href="tiger.html" class="quick-link">
          <span class="ql-icon">🐯</span>
          Jim Corbett — Tigers
          <span class="ql-arrow">›</span>
        </a>
        <a href="lion.html" class="quick-link">
          <span class="ql-icon">🦁</span>
          Gir Forest — Lions
          <span class="ql-arrow">›</span>
        </a>
        <a href="turtle.html" class="quick-link">
          <span class="ql-icon">🐢</span>
          Velas Beach — Turtles
          <span class="ql-arrow">›</span>
        </a>
      </div>
    </div>

  </div>

</body>
</html>
