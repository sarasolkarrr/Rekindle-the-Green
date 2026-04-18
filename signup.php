<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
  <title>Sign Up-for Rekindle the Green</title>

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

    .btn-nav {
      padding: 0.38rem 1rem; border-radius: 5px;
      font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 600;
      cursor: pointer; text-decoration: none; transition: all 0.18s; display: inline-block;
    }
    .btn-ghost {
      background: transparent; color: rgba(255,255,255,0.6);
      border: 1.5px solid rgba(255,255,255,0.2);
    }
    .btn-ghost:hover { background: rgba(255,255,255,0.08); color: #fff; border-color: rgba(255,255,255,0.4); }
    .btn-outline {
      background: transparent; color: #c9a84c; border: 1.5px solid #c9a84c;
    }
    .btn-outline:hover { background: rgba(201,168,76,0.1); }


    .page-wrap {
      position: relative;
      z-index: 1;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 76px 1rem 2rem;
    }


    .card {
      background: rgba(255,255,255,0.96);
      border-radius: 14px;
      width: 100%;
      max-width: 440px;
      padding: 2rem 2rem 1.8rem;
      box-shadow: 0 16px 50px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.08);
    }

    .card-header { text-align: center; margin-bottom: 1.5rem; }

    .site-label {
      font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em;
      text-transform: uppercase; color: #4a7a2a; margin-bottom: 0.5rem;
    }

    .card-header h1 {
      font-size: 1.45rem; font-weight: 700; color: #1a2e1a;
      letter-spacing: -0.02em; margin-bottom: 0.3rem;
    }
    .card-header p { font-size: 0.8rem; color: #888; }


    .section-label {
      font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
      text-transform: uppercase; color: #bbb;
      margin: 1.1rem 0 0.7rem;
      padding-bottom: 0.4rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .field { margin-bottom: 0.85rem; }

    .field label {
      display: block; font-size: 0.71rem; font-weight: 600;
      color: #444; margin-bottom: 0.35rem;
      letter-spacing: 0.03em; text-transform: uppercase;
    }

    .field input, .field select {
      width: 100%; padding: 0.68rem 0.88rem;
      border: 1.5px solid #ddd; border-radius: 8px;
      font-family: 'Inter', sans-serif; font-size: 0.87rem;
      color: #1a1a1a; background: #fafafa; outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
      -webkit-appearance: none; appearance: none;
    }
    .field input:focus, .field select:focus {
      border-color: #3d6b2a; background: #fff;
      box-shadow: 0 0 0 3px rgba(61,107,42,0.1);
    }
    .field.has-error input, .field.has-error select { border-color: #c0392b; }

    .field .err { font-size: 0.7rem; color: #c0392b; margin-top: 0.25rem; display: none; }
    .field.has-error .err { display: block; }


    .select-wrap { position: relative; }
    .select-wrap::after {
      content: '▾'; position: absolute;
      right: 0.88rem; top: 50%; transform: translateY(-50%);
      font-size: 0.8rem; color: #999; pointer-events: none;
    }
    .field select { padding-right: 2.2rem; cursor: pointer; }


    .field-row { display: flex; gap: 0.65rem; }
    .field-row .field { flex: 1; }


    .phone-row { display: flex; gap: 0.5rem; }
    .phone-row .code-wrap { width: 94px; flex-shrink: 0; }
    .phone-row .num-wrap { flex: 1; }

    .pwd-wrap { position: relative; }
    .pwd-wrap input { padding-right: 3.2rem; }
    .show-pwd {
      position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; font-size: 0.68rem; font-weight: 700;
      font-family: 'Inter', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;
      color: #aaa; cursor: pointer; padding: 0; transition: color 0.18s;
    }
    .show-pwd:hover { color: #3d6b2a; }


    .strength { display: none; margin-top: 0.42rem; }
    .s-bars { display: flex; gap: 3px; margin-bottom: 3px; }
    .sbar { flex: 1; height: 3px; border-radius: 2px; background: #e8e8e8; transition: background 0.25s; }
    .s-label { font-size: 0.69rem; color: #aaa; }


    .btn-submit {
      width: 100%; padding: 0.8rem;
      background: #3d6b2a; color: #fff; border: none;
      border-radius: 8px; font-family: 'Inter', sans-serif;
      font-size: 0.9rem; font-weight: 600; cursor: pointer;
      margin-top: 0.6rem; position: relative;
      transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    }
    .btn-submit:hover { background: #2f5220; box-shadow: 0 4px 14px rgba(61,107,42,0.35); }
    .btn-submit:active { transform: scale(0.99); }
    .btn-submit.loading { pointer-events: none; opacity: 0.75; }
    .btn-submit.loading .btn-text { opacity: 0; }

    .spinner {
      display: none; position: absolute; top: 50%; left: 50%;
      transform: translate(-50%,-50%);
      width: 17px; height: 17px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    .btn-submit.loading .spinner { display: block; }
    @keyframes spin { to { transform: translate(-50%,-50%) rotate(360deg); } }

    .terms-text {
      text-align: center; font-size: 0.69rem; color: #ccc;
      margin-top: 0.7rem; line-height: 1.5;
    }
    .terms-text a { color: #aaa; text-decoration: underline; }

    .card-footer {
      text-align: center; font-size: 0.78rem; color: #aaa; margin-top: 1rem;
    }
    .card-footer a { color: #3d6b2a; font-weight: 600; text-decoration: none; }
    .card-footer a:hover { text-decoration: underline; }


    .success-box { display: none; text-align: center; padding: 0.5rem 0 0.8rem; }
    .success-box.show { display: block; }
    .success-icon { font-size: 2.6rem; margin-bottom: 0.7rem; }
    .success-box h3 { font-size: 1.1rem; font-weight: 700; color: #1a2e1a; margin-bottom: 0.4rem; }
    .success-box p  { font-size: 0.8rem; color: #777; line-height: 1.55; margin-bottom: 1.1rem; }
    .progress-bar { height: 2px; background: #e8e8e8; border-radius: 2px; overflow: hidden; margin-bottom: 0.4rem; }
    .progress-fill { height: 100%; background: #3d6b2a; width: 0%; transition: width 2.5s linear; }
    .redirect-note { font-size: 0.7rem; color: #bbb; }

    .drive-cards { display: flex; flex-direction: column; gap: 0.5rem; }

    .drive-card {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.7rem 0.9rem;
      border: 1.5px solid #e0e0e0; border-radius: 8px;
      cursor: pointer; background: #fafafa;
      transition: border-color 0.18s, background 0.18s, box-shadow 0.18s;
      font-family: 'Inter', sans-serif;
    }
    .drive-card:hover { border-color: #3d6b2a; background: #f5faf3; }
    .drive-card.selected {
      border-color: #3d6b2a; background: #f0f7ec;
      box-shadow: 0 0 0 3px rgba(61,107,42,0.12);
    }

    .drive-card .d-emoji { font-size: 1.35rem; flex-shrink: 0; }
    .drive-card .d-info { flex: 1; }
    .drive-card .d-name { font-size: 0.82rem; font-weight: 600; color: #1a2e1a; }
    .drive-card .d-sub  { font-size: 0.71rem; color: #999; margin-top: 1px; }
    .drive-card .d-check {
      width: 18px; height: 18px; border-radius: 50%;
      border: 2px solid #ddd; display: flex; align-items: center;
      justify-content: center; font-size: 0.6rem; color: #fff;
      transition: all 0.18s; flex-shrink: 0; background: transparent;
    }
    .drive-card.selected .d-check { background: #3d6b2a; border-color: #3d6b2a; }

    input[name="drive"] { display: none; }

    .field.has-error .drive-card { border-color: #f0c0bb; }
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
      <a href="index.html" class="btn-nav btn-ghost">← Map</a>
      <a href="login.php" class="btn-nav btn-outline">Sign In</a>
    </div>
  </nav>


  <div class="page-wrap">
    <div class="card">

      <div class="card-header">
        <div class="site-label">Rekindle the Green</div>
        <h1>Join the mission</h1>
        <p>Register and help protect India's wild spaces.</p>
      </div>

      <div class="success-box" id="successBox">
        <div class="success-icon">🌿</div>
        <h3 id="successTitle">You're in!</h3>
        <p id="successMsg">Welcome. Taking you to the map...</p>
        <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
        <span class="redirect-note">Redirecting...</span>
      </div>


      <div id="formWrap">
        <form onsubmit="return false;">

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
            <input type="email" id="r-email" placeholder="janedoe@gmail.com" autocomplete="email">
            <span class="err">Please enter a valid email</span>
          </div>

          <div class="field" id="fr-phone">
            <label>Phone number</label>
            <div class="phone-row">
              <div class="code-wrap select-wrap">
                <select id="r-code">
                  <option value="+91">🇮🇳 +91</option>
                  <option value="+1">🇺🇸 +1</option>
                  <option value="+44">🇬🇧 +44</option>
                  <option value="+61">🇦🇺 +61</option>
                </select>
              </div>
              <div class="num-wrap">
                <input type="tel" id="r-phone" placeholder="9876543210" autocomplete="tel-national">
              </div>
            </div>
            <span class="err">Please enter a valid phone number.</span>
          </div>

          <div class="section-label">Choose your conservation drive</div>

          <div class="field" id="fr-drive">
            <div class="drive-cards">
              <div class="drive-card" onclick="selectDrive('corbett', this)">
                <span class="d-emoji">🐯</span>
                <div class="d-info">
                  <div class="d-name">Jim Corbett National Park</div>
                  <div class="d-sub">Tiger Conservation — Uttarakhand</div>
                </div>
                <div class="d-check">✓</div>
              </div>
              <div class="drive-card" onclick="selectDrive('velas', this)">
                <span class="d-emoji">🐢</span>
                <div class="d-info">
                  <div class="d-name">Velas Beach</div>
                  <div class="d-sub">Turtle Conservation Drive — Maharashtra</div>
                </div>
                <div class="d-check">✓</div>
              </div>
              <div class="drive-card" onclick="selectDrive('gir', this)">
                <span class="d-emoji">🦁</span>
                <div class="d-info">
                  <div class="d-name">Gir National Park</div>
                  <div class="d-sub">Lion Conservation — Gujarat</div>
                </div>
                <div class="d-check">✓</div>
              </div>
            </div>
            <input type="hidden" id="r-drive" value="">
            <span class="err">Please select a conservation drive.</span>
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
                <div class="sbar" id="sb1"></div><div class="sbar" id="sb2"></div>
                <div class="sbar" id="sb3"></div><div class="sbar" id="sb4"></div>
              </div>
              <span class="s-label" id="strengthText"></span>
            </div>
          </div>

          <button class="btn-submit" id="registerBtn" type="button" onclick="doRegister()">
            <span class="btn-text">Register &amp; Join Drive</span>
            <span class="spinner"></span>
          </button>

        </form>

        <p class="card-footer">
          Already have an account? <a href="login.php">Sign in here</a>
        </p>
      </div>

    </div>
  </div>

<script>
  var selectedDrive = '';

  function selectDrive(val, el) {
    selectedDrive = val;
    document.getElementById('r-drive').value = val;
    document.querySelectorAll('.drive-card').forEach(function(c){ c.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('fr-drive').classList.remove('has-error');
  }

  function togglePwd(id, btn) {
    var inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
  }

  function isValidEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
  function isValidPhone(p) { return /^[0-9]{7,15}$/.test(p.replace(/\s/g,'')); }

  function markError(id) { document.getElementById(id).classList.add('has-error'); return true; }
  function clearErrors() {
    document.querySelectorAll('.field.has-error').forEach(function(f){ f.classList.remove('has-error'); });
  }

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
      document.getElementById('sb'+i).style.background = i <= score ? colors[score-1] : '#e8e8e8';
    }
    var lbl = document.getElementById('strengthText');
    lbl.textContent = labels[score-1] || '';
    lbl.style.color  = score ? colors[score-1] : '#aaa';
  }

  var drivePages = { corbett: 'tiger.html', velas: 'turtle.html', gir: 'lion.html' };

  function doRegister() {
    clearErrors();
    var hasErr = false;
    var fname    = document.getElementById('r-fname').value.trim();
    var lname    = document.getElementById('r-lname').value.trim();
    var email    = document.getElementById('r-email').value.trim();
    var phone    = document.getElementById('r-phone').value;
    var code     = document.getElementById('r-code').value;
    var drive    = document.getElementById('r-drive').value;
    var password = document.getElementById('r-pwd').value;

    if (!fname)                hasErr = markError('fr-fname');
    if (!lname)                hasErr = markError('fr-lname');
    if (!isValidEmail(email))  hasErr = markError('fr-email');
    if (!isValidPhone(phone))  hasErr = markError('fr-phone');
    if (!drive)                hasErr = markError('fr-drive');
    if (password.length < 8)  hasErr = markError('fr-pwd');
    if (hasErr) return;

    var btn = document.getElementById('registerBtn');
    btn.classList.add('loading');

    var fd = new FormData();
    fd.append('action',   'register');
    fd.append('fname',    fname);
    fd.append('lname',    lname);
    fd.append('email',    email);
    fd.append('phone',    phone);
    fd.append('code',     code);
    fd.append('drive',    drive);
    fd.append('password', password);

    fetch('profile.php', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(data) {
        btn.classList.remove('loading');
        if (data.success) {
          document.getElementById('formWrap').style.display = 'none';
          var sb = document.getElementById('successBox');
          sb.classList.add('show');
          var driveName = drive === 'corbett' ? 'Jim Corbett' : drive === 'velas' ? 'Velas Beach' : 'Gir Forest';
          document.getElementById('successMsg').textContent = 'Welcome to ' + driveName + '. Taking you there...';
          setTimeout(function(){ document.getElementById('progressFill').style.width = '100%'; }, 50);
          var dest = drivePages[drive] || 'index.html';
          setTimeout(function(){ window.location.href = dest; }, 2600);
        } else {
          alert(data.message || 'Registration failed. Please try again.');
        }
      })
      .catch(function() {
        btn.classList.remove('loading');
        alert('An error occurred. Please try again.');
      });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doRegister();
  });
</script>
</body>
</html>
