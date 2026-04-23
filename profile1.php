<?php
include 'connection.php';
session_start();

header('Content-Type: application');

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
        $_SESSION['user_id'] = mysqli_insert_id($con);
        $_SESSION['user_email'] = $email;
        echo json_encode(['success' => true, 'message' => 'Registration successful', 'drive' => $drive]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($con)]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT id, first_name, password FROM users WHERE email='$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $user['first_name'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="nav.css">
    <script src="nav.js" defer></script>
    <title>Register — Rekindle the Wild</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-image: url('jimc.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #1a2e1a;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(8, 18, 8, 0.65);
            z-index: 0;
        }

        .page-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 90px 1rem 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.94);
            border-radius: 12px;
            width: 100%;
            max-width: 430px;
            padding: 2rem 2rem 1.8rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        .card.hide-on-complete {
          animation: cardExit 0.35s ease forwards;
          pointer-events: none;
        }

        @keyframes cardExit {
          to {
            opacity: 0;
            transform: translateY(12px) scale(0.98);
          }
        }

        .card-header {
            text-align: center;
            margin-bottom: 1.6rem;
        }

        .card-header .site-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #4a7a2a;
            margin-bottom: 0.4rem;
        }

        .card-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a2e1a;
            letter-spacing: -0.02em;
        }

        .card-header p {
            font-size: 0.81rem;
            color: #777;
            margin-top: 0.3rem;
        }

        .screen { display: none; }
        .screen.active { display: block; }

        .choice-btns {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .choice-btn {
            width: 100%;
            padding: 0.85rem 1.1rem;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            transition: background 0.18s, transform 0.1s, box-shadow 0.18s;
        }

        .choice-btn:active { transform: scale(0.99); }

        .choice-btn.primary {
            background: #3d6b2a;
            color: #fff;
        }

        .choice-btn.primary:hover {
            background: #2f5220;
            box-shadow: 0 4px 14px rgba(61,107,42,0.3);
        }

        .choice-btn.secondary {
            background: #f0f0f0;
            color: #333;
            border: 1.5px solid #ddd;
        }

        .choice-btn.secondary:hover { background: #e8e8e8; }

        .choice-btn .btn-icon { font-size: 1.1rem; flex-shrink: 0; }
        .choice-btn .btn-label { flex: 1; }
        .choice-btn .btn-label span {
            display: block;
            font-size: 0.71rem;
            font-weight: 400;
            opacity: 0.65;
            margin-top: 2px;
        }
        .choice-btn .arrow { font-size: 0.9rem; opacity: 0.45; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.77rem;
            color: #888;
            cursor: pointer;
            margin-bottom: 1.3rem;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            padding: 0;
            transition: color 0.2s;
        }
        .back-link:hover { color: #3d6b2a; }

        .section-label {
            font-size: 0.67rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #aaa;
            margin: 1rem 0 0.65rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid #eee;
        }

        .field { margin-bottom: 0.9rem; }

        .field label {
            display: block;
            font-size: 0.73rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 0.35rem;
            letter-spacing: 0.03em;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #d4d4d4;
            border-radius: 7px;
            font-family: 'Inter', sans-serif;
            font-size: 0.87rem;
            color: #1a1a1a;
            background: #fafafa;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: #3d6b2a;
            background: #fff;
        }

        .field.has-error input,
        .field.has-error select { border-color: #c0392b; }

        .field .err {
            font-size: 0.71rem;
            color: #c0392b;
            margin-top: 0.25rem;
            display: none;
        }
        .field.has-error .err { display: block; }

        /* select arrow */
        .select-wrap { position: relative; }
        .select-wrap::after {
            content: '▾';
            position: absolute;
            right: 0.85rem; top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            color: #888;
            pointer-events: none;
        }
        .field select { padding-right: 2.2rem; cursor: pointer; }

        .field-row { display: flex; gap: 0.7rem; }
        .field-row .field { flex: 1; }

        .phone-row { display: flex; gap: 0.5rem; }
        .phone-row .code-wrap { width: 92px; flex-shrink: 0; }
        .phone-row .num-wrap { flex: 1; }

        .pwd-wrap { position: relative; }
        .pwd-wrap input { padding-right: 3rem; }
        .show-pwd {
            position: absolute;
            right: 0.75rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            font-size: 0.7rem; font-weight: 600;
            font-family: 'Inter', sans-serif;
            text-transform: uppercase; letter-spacing: 0.04em;
            color: #999; cursor: pointer; padding: 0;
        }
        .show-pwd:hover { color: #3d6b2a; }

        .strength { display: none; margin-top: 0.4rem; }
        .s-bars { display: flex; gap: 3px; margin-bottom: 3px; }
        .sbar { flex: 1; height: 3px; border-radius: 2px; background: #e0e0e0; transition: background 0.25s; }
        .s-label { font-size: 0.7rem; color: #999; }

        .forgot-row { text-align: right; margin-top: -0.35rem; margin-bottom: 0.9rem; }
        .forgot-row a { font-size: 0.74rem; color: #888; text-decoration: none; }
        .forgot-row a:hover { color: #3d6b2a; text-decoration: underline; }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: #3d6b2a;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            position: relative;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:hover { background: #2f5220; }
        .btn-submit:active { transform: scale(0.99); }
        .btn-submit.loading { pointer-events: none; opacity: 0.75; }
        .btn-submit.loading .btn-text { opacity: 0; }

        .spinner {
            display: none;
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-submit.loading .spinner { display: block; }
        @keyframes spin { to { transform: translate(-50%,-50%) rotate(360deg); } }

        .sub-text { text-align: center; font-size: 0.76rem; color: #999; margin-top: 0.9rem; }
        .sub-text a { color: #3d6b2a; font-weight: 600; text-decoration: none; }
        .sub-text a:hover { text-decoration: underline; }

        .terms-text { text-align: center; font-size: 0.7rem; color: #bbb; margin-top: 0.7rem; line-height: 1.5; }
        .terms-text a { color: #999; text-decoration: underline; }

        /* SUCCESS */
        .success-box { display: none; text-align: center; padding: 0.8rem 0; }
        .success-box.show { display: block; }
        .success-icon { font-size: 2.4rem; margin-bottom: 0.7rem; }
        .success-box h3 { font-size: 1.15rem; font-weight: 700; color: #1a2e1a; margin-bottom: 0.4rem; }
        .success-box p { font-size: 0.81rem; color: #666; line-height: 1.55; margin-bottom: 1.2rem; }
        .progress-bar { height: 2px; background: #e0e0e0; border-radius: 2px; overflow: hidden; margin-bottom: 0.45rem; }
        .progress-fill { height: 100%; background: #3d6b2a; width: 0%; transition: width 2.8s linear; }
        .redirect-note { font-size: 0.71rem; color: #bbb; }

        /* ERROR MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            animation: modalSlideIn 0.3s ease;
        }
        @keyframes modalSlideIn {
            from { transform: scale(0.95) translateY(-20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
        .modal-icon { font-size: 3rem; margin-bottom: 1rem; }
        .modal-content h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a2e1a;
            margin-bottom: 0.5rem;
        }
        .modal-content p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .modal-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 7px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .modal-btn-primary {
            background: #3d6b2a;
            color: #fff;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .modal-btn-primary:hover { background: #2f5220; }
        .modal-btn-secondary {
            background: #f0f0f0;
            color: #333;
            width: 100%;
        }
        .modal-btn-secondary:hover { background: #e0e0e0; }
    </style>
</head>

<body>
<!-- ERROR MODAL -->
<div class="modal-overlay" id="errorModal">
  <div class="modal-content">
    <div class="modal-icon">Alert</div>
    <h3 id="modalTitle">Login Error</h3>
    <p id="modalMessage">An error occurred. Please try again.</p>
    <button class="modal-btn modal-btn-primary" onclick="closeErrorModal()">Try Again</button>
    <button class="modal-btn modal-btn-secondary" onclick="redirectToSignup()">Create Account</button>
  </div>
</div>

<div class="page-wrap">
  <div class="card">

    <div class="card-header">
      <div class="site-label">Rekindle the Wild</div>
      <h2 id="cardTitle">Join the mission</h2>
      <p id="cardSub">Help us protect India\'s wild spaces.</p>
    </div>

    <!-- SUCCESS -->
    <div class="success-box" id="successBox">
      <div class="success-icon">OK</div>
      <h3 id="successTitle">You\'re registered!</h3>
      <p id="successMsg">Welcome. Taking you to the map...</p>
      <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
      <span class="redirect-note" id="redirectNote">Redirecting in 3s...</span>
    </div>

    <!-- SCREEN 1: CHOICE -->
    <div class="screen active" id="screenChoice">
      <div class="choice-btns">

        <button class="choice-btn secondary" onclick="goTo('screenLogin')">
          <span class="btn-icon">In</span>
          <span class="btn-label">
            I already have an account
            <span>Sign in to continue</span>
          </span>
          <span class="arrow">›</span>
        </button>

        <button class="choice-btn primary" onclick="goTo('screenRegister')">
          <span class="btn-icon">New</span>
          <span class="btn-label">
            Create a new account
            <span>Register and join a conservation drive</span>
          </span>
          <span class="arrow">›</span>
        </button>

      </div>
    </div>

    <!-- SCREEN 2: LOGIN -->
    <div class="screen" id="screenLogin">
      <button class="back-link" onclick="goTo('screenChoice')">← Back</button>

      <form id="loginForm" onsubmit="return false;">
        <div class="field" id="fl-email">
          <label>Email address</label>
          <input type="email" id="l-email" placeholder="you@example.com" autocomplete="email">
          <span class="err">Please enter a valid email.</span>
        </div>

        <div class="field" id="fl-pwd">
          <label>Password</label>
          <div class="pwd-wrap">
            <input type="password" id="l-pwd" placeholder="Enter your password" autocomplete="current-password">
            <button class="show-pwd" type="button" onclick="togglePwd('l-pwd', this)">Show</button>
          </div>
          <span class="err">Password is required.</span>
        </div>

        <div class="forgot-row"><a href="#">Forgot password?</a></div>

        <button class="btn-submit" id="loginBtn" type="button" onclick="doLogin()">
          <span class="btn-text">Sign In</span>
          <span class="spinner"></span>
        </button>
      </form>

      <p class="sub-text" style="margin-top:1rem">
        No account yet? <a href="#" onclick="goTo('screenRegister'); return false;">Register here</a>
      </p>
    </div>

    <!-- SCREEN 3: REGISTER -->
    <div class="screen" id="screenRegister">
      <button class="back-link" onclick="goTo('screenChoice')">← Back</button>

      <form id="registerForm" onsubmit="return false;">
        <div class="section-label">Personal details</div>

        <div class="field-row">
          <div class="field" id="fr-fname">
            <label>First name</label>
            <input type="text" id="r-fname" placeholder="Jane" autocomplete="given-name">
            <span class="err">Required.</span>
          </div>
          <div class="field" id="fr-lname">
            <label>Last name</label>
            <input type="text" id="r-lname" placeholder="Doe" autocomplete="family-name">
            <span class="err">Required.</span>
          </div>
        </div>

        <div class="field" id="fr-email">
          <label>Email address</label>
          <input type="email" id="r-email" placeholder="you@example.com" autocomplete="email">
          <span class="err">Please enter a valid email.</span>
        </div>

        <div class="field" id="fr-phone">
          <label>Phone number</label>
          <div class="phone-row">
            <div class="code-wrap select-wrap">
              <select id="r-code">
                <option value="+91">India +91</option>
                <option value="+1">USA +1</option>
                <option value="+44">UK +44</option>
                <option value="+61">Australia +61</option>
              </select>
            </div>
            <div class="num-wrap">
              <input type="tel" id="r-phone" placeholder="9876543210" autocomplete="tel-national">
            </div>
          </div>
          <span class="err">Please enter a valid phone number.</span>
        </div>

        <div class="section-label">Conservation drive</div>

        <div class="field" id="fr-drive">
          <label>Which drive are you joining?</label>
          <div class="select-wrap">
            <select id="r-drive">
              <option value="" disabled selected>Select a drive...</option>
              <option value="corbett">Jim Corbett National Park — Tiger Conservation</option>
              <option value="velas">Velas Beach — Turtle Conservation Drive</option>
              <option value="gir">Gir National Park — Lion Conservation</option>
            </select>
          </div>
          <span class="err">Please select a drive.</span>
        </div>

        <div class="section-label">Set your password</div>

        <div class="field" id="fr-pwd">
          <label>Password</label>
          <div class="pwd-wrap">
            <input type="password" id="r-pwd" placeholder="Min. 8 characters" autocomplete="new-password" oninput="checkStrength(this.value)">
            <button class="show-pwd" type="button" onclick="togglePwd('r-pwd', this)">Show</button>
          </div>
          <span class="err">Must be at least 8 characters.</span>
          <div class="strength" id="strengthBox">
            <div class="s-bars">
              <div class="sbar" id="sb1"></div>
              <div class="sbar" id="sb2"></div>
              <div class="sbar" id="sb3"></div>
              <div class="sbar" id="sb4"></div>
            </div>
            <span class="s-label" id="strengthText"></span>
          </div>
        </div>

        <button class="btn-submit" id="registerBtn" type="button" onclick="doRegister()">
          <span class="btn-text">Register &amp; Join Drive</span>
          <span class="spinner"></span>
        </button>

        <p class="terms-text">
          By registering you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
        </p>
      </form>
      <p class="sub-text">
        Already have an account? <a href="#" onclick="goTo('screenLogin'); return false;">Sign in</a>
      </p>
    </div>

  </div>
</div>

<script>
    function goTo(screenId) {
        document.querySelectorAll('.screen').forEach(function(s) {
            s.classList.remove('active');
        });
        var t = document.getElementById(screenId);
        if (t) t.classList.add('active');

        var titles = {
            screenChoice:   ['Join the mission',    "Help us protect India's wild spaces."],
            screenLogin:    ['Welcome back',         'Sign in to continue your journey.'],
            screenRegister: ['Create your account',  'Register and join a conservation drive.']
        };
        var info = titles[screenId];
        if (info) {
            document.getElementById('cardTitle').textContent = info[0];
            document.getElementById('cardSub').textContent   = info[1];
        }
        clearErrors();
    }

    function togglePwd(id, btn) {
        var input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? 'Show' : 'Hide';
    }

    function clearErrors() {
        document.querySelectorAll('.field.has-error').forEach(function(f) {
            f.classList.remove('has-error');
        });
    }

    function markError(id) {
        document.getElementById(id).classList.add('has-error');
        return true;
    }

    function isValidEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
    function isValidPhone(p) { return /^[0-9]{7,15}$/.test(p.replace(/\s/g, '')); }

    function checkStrength(val) {
        var box = document.getElementById('strengthBox');
        if (!val) { box.style.display = 'none'; return; }
        box.style.display = 'block';
        var score = 0;
        if (val.length >= 8)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        var colors = ['#c0392b','#e67e22','#27ae60','#1e8449'];
        var labels = ['Weak','Fair','Good','Strong'];
        for (var i = 1; i <= 4; i++) {
            document.getElementById('sb'+i).style.background = i <= score ? colors[score-1] : '#e0e0e0';
        }
        var lbl = document.getElementById('strengthText');
        lbl.textContent = labels[score-1] || '';
        lbl.style.color  = score ? colors[score-1] : '#999';
    }

    function doLogin() {
        clearErrors();
        var hasErr = false;
        var email = document.getElementById('l-email').value.trim();
        var password = document.getElementById('l-pwd').value;

        if (!isValidEmail(email)) hasErr = markError('fl-email');
        if (!password) hasErr = markError('fl-pwd');
        if (hasErr) return;

        var btn = document.getElementById('loginBtn');
        btn.classList.add('loading');

        var formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', email);
        formData.append('password', password);

        fetch('profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.classList.remove('loading');
            if (data.success) {
                showSuccess('login');
            } else {
                var title = 'Login Error';
                var msg = data.message || 'Login failed';

                if (data.message === 'User not found') {
                    title = 'Account Not Found';
                    msg = 'No account exists with this email. Create a new account to get started.';
                } else if (data.message === 'Invalid password') {
                    title = 'Invalid Password';
                    msg = 'The password you entered is incorrect. Please try again.';
                }

                showErrorModal(title, msg);
            }
        })
        .catch(error => {
            btn.classList.remove('loading');
            console.error('Error:', error);
            showErrorModal('Error', 'An error occurred. Please try again.');
        });
    }

    function showErrorModal(title, message) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('errorModal').classList.add('show');
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.remove('show');
    }

    function redirectToSignup() {
        document.getElementById('errorModal').classList.remove('show');
        goTo('screenRegister');
    }

    function doRegister() {
        clearErrors();
        var hasErr = false;
        var fname = document.getElementById('r-fname').value.trim();
        var lname = document.getElementById('r-lname').value.trim();
        var email = document.getElementById('r-email').value.trim();
        var phone = document.getElementById('r-phone').value;
        var code = document.getElementById('r-code').value;
        var drive = document.getElementById('r-drive').value;
        var password = document.getElementById('r-pwd').value;

        if (!fname) hasErr = markError('fr-fname');
        if (!lname) hasErr = markError('fr-lname');
        if (!isValidEmail(email)) hasErr = markError('fr-email');
        if (!isValidPhone(phone)) hasErr = markError('fr-phone');
        if (!drive) hasErr = markError('fr-drive');
        if (password.length < 8) hasErr = markError('fr-pwd');
        if (hasErr) return;

        var btn = document.getElementById('registerBtn');
        btn.classList.add('loading');

        var formData = new FormData();
        formData.append('action', 'register');
        formData.append('fname', fname);
        formData.append('lname', lname);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('code', code);
        formData.append('drive', drive);
        formData.append('password', password);

        fetch('profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.classList.remove('loading');
            if (data.success) {
                showSuccess('register');
            } else {
                alert(data.message || 'Registration failed');
            }
        })
        .catch(error => {
            btn.classList.remove('loading');
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Drive → page mapping for redirect after register
    var drivePages = {
        corbett: 'tiger.html',
        velas:   'turtle.html',
        gir:     'lion.html'
    };

    function showSuccess(type) {
        document.querySelectorAll('.screen').forEach(function(s) { s.style.display = 'none'; });
        document.getElementById('successBox').classList.add('show');

        var driveEl   = document.getElementById('r-drive');
        var driveVal  = driveEl ? driveEl.value : '';
        var driveName = (driveEl && driveVal)
            ? driveEl.options[driveEl.selectedIndex].text.split('—')[0].trim()
            : '';

        var destination = (type === 'register' && driveVal && drivePages[driveVal])
            ? drivePages[driveVal]
            : 'index.html';

        if (type === 'register') {
            document.getElementById('cardTitle').textContent    = 'Registration complete!';
            document.getElementById('successTitle').textContent = "You're in!";
            document.getElementById('successMsg').textContent   = driveName
                ? 'Welcome to ' + driveName + '. Taking you there...'
                : 'Account created. Taking you to the map...';
        } else {
            document.getElementById('cardTitle').textContent    = 'Signed in.';
            document.getElementById('successTitle').textContent = 'Welcome back.';
            document.getElementById('successMsg').textContent   = 'Taking you to the map...';
        }
        document.getElementById('cardSub').textContent = '';

        setTimeout(function() {
          var card = document.querySelector('.card');
          if (card) card.classList.add('hide-on-complete');
        }, 50);

        setTimeout(function() {
          window.location.href = destination;
        }, 2800);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return;
        var active = document.querySelector('.screen.active');
        if (!active) return;
        if (active.id === 'screenLogin')    doLogin();
        if (active.id === 'screenRegister') doRegister();
    });
</script>
</body>
</html>
