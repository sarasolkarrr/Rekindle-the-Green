<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="nav.css">
  <script src="nav.js" defer></script>
  <title>Sign In-Rekindle the Green</title>

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
      padding: 80px 1rem 2rem;
    }

    .card {
      background: rgba(255,255,255,0.96);
      border-radius: 14px;
      width: 100%;
      max-width: 400px;
      padding: 2.2rem 2rem 2rem;
      box-shadow: 0 16px 50px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.1);
    }

    .card-header { text-align: center; margin-bottom: 1.8rem; }

    .site-label {
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #4a7a2a;
      margin-bottom: 0.5rem;
    }

    .card-header h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1a2e1a;
      letter-spacing: -0.02em;
      margin-bottom: 0.3rem;
    }

    .card-header p {
      font-size: 0.8rem;
      color: #888;
    }


    .divider {
      height: 1px;
      background: #eee;
      margin: 0 0 1.5rem;
    }

    .field { margin-bottom: 1rem; }

    .field label {
      display: block;
      font-size: 0.72rem;
      font-weight: 600;
      color: #444;
      margin-bottom: 0.38rem;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }

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

    .field input:focus {
      border-color: #3d6b2a;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(61,107,42,0.1);
    }

    .field.has-error input { border-color: #c0392b; }
    .field.has-error input:focus { box-shadow: 0 0 0 3px rgba(192,57,43,0.1); }

    .field .err {
      font-size: 0.7rem;
      color: #c0392b;
      margin-top: 0.28rem;
      display: none;
    }
    .field.has-error .err { display: block; }


    .pwd-wrap { position: relative; }
    .pwd-wrap input { padding-right: 3.2rem; }
    .show-pwd {
      position: absolute;
      right: 0.8rem; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      font-size: 0.68rem; font-weight: 700;
      font-family: 'Inter', sans-serif;
      text-transform: uppercase; letter-spacing: 0.05em;
      color: #aaa; cursor: pointer; padding: 0;
      transition: color 0.18s;
    }
    .show-pwd:hover { color: #3d6b2a; }

    .forgot-row { text-align: right; margin: -0.4rem 0 1rem; }
    .forgot-row a { font-size: 0.73rem; color: #aaa; text-decoration: none; transition: color 0.18s; }
    .forgot-row a:hover { color: #3d6b2a; }

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
      margin-top: 0.3rem;
      position: relative;
      transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
      letter-spacing: 0.01em;
    }
    .btn-submit:hover {
      background: #2f5220;
      box-shadow: 0 4px 14px rgba(61,107,42,0.35);
    }
    .btn-submit:active { transform: scale(0.99); }
    .btn-submit.loading { pointer-events: none; opacity: 0.75; }
    .btn-submit.loading .btn-text { opacity: 0; }

    .spinner {
      display: none;
      position: absolute; top: 50%; left: 50%;
      transform: translate(-50%,-50%);
      width: 17px; height: 17px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    .btn-submit.loading .spinner { display: block; }
    @keyframes spin { to { transform: translate(-50%,-50%) rotate(360deg); } }


    .card-footer {
      text-align: center;
      font-size: 0.78rem;
      color: #aaa;
      margin-top: 1.2rem;
    }
    .card-footer a {
      color: #3d6b2a;
      font-weight: 600;
      text-decoration: none;
    }
    .card-footer a:hover { text-decoration: underline; }

    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.65);
      z-index: 2000;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.show { display: flex; }

    .modal-box {
      background: #fff;
      border-radius: 14px;
      padding: 2rem;
      max-width: 360px;
      width: 90%;
      text-align: center;
      box-shadow: 0 16px 50px rgba(0,0,0,0.4);
      animation: modalIn 0.25s ease;
    }
    @keyframes modalIn {
      from { transform: scale(0.94) translateY(-16px); opacity: 0; }
      to   { transform: scale(1) translateY(0); opacity: 1; }
    }

    .modal-icon { font-size: 2.8rem; margin-bottom: 0.8rem; }
    .modal-box h3 { font-size: 1.1rem; font-weight: 700; color: #1a2e1a; margin-bottom: 0.45rem; }
    .modal-box p  { font-size: 0.83rem; color: #666; line-height: 1.55; margin-bottom: 1.4rem; }

    .modal-actions { display: flex; flex-direction: column; gap: 0.5rem; }
    .modal-btn {
      padding: 0.7rem;
      border: none;
      border-radius: 7px;
      font-family: 'Inter', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.18s;
    }
    .modal-btn-primary { background: #3d6b2a; color: #fff; }
    .modal-btn-primary:hover { background: #2f5220; }
    .modal-btn-secondary { background: #f0f0f0; color: #444; }
    .modal-btn-secondary:hover { background: #e5e5e5; }


    .success-box { display: none; text-align: center; padding: 0.5rem 0 0.8rem; }
    .success-box.show { display: block; }
    .success-icon { font-size: 2.6rem; margin-bottom: 0.7rem; }
    .success-box h3 { font-size: 1.1rem; font-weight: 700; color: #1a2e1a; margin-bottom: 0.4rem; }
    .success-box p  { font-size: 0.8rem; color: #777; line-height: 1.55; margin-bottom: 1.1rem; }
    .progress-bar { height: 2px; background: #e8e8e8; border-radius: 2px; overflow: hidden; margin-bottom: 0.4rem; }
    .progress-fill { height: 100%; background: #3d6b2a; width: 0%; transition: width 2.5s linear; }
    .redirect-note { font-size: 0.7rem; color: #bbb; }

    .form-wrap {}
  </style>
</head>
<body>


  <?php include 'navbar.php'; ?>


  <div class="modal-overlay" id="errorModal">
    <div class="modal-box">
      <h3 id="modalTitle">Login Error</h3>
      <p id="modalMessage">An error occurred. Please try again.</p>
      <div class="modal-actions">
        <button class="modal-btn modal-btn-primary" onclick="closeModal()">Try Again</button>
        <button class="modal-btn modal-btn-secondary" onclick="window.location.href='signup.php'">Create Account</button>
      </div>
    </div>
  </div>


  <div class="page-wrap">
    <div class="card">

      <div class="card-header">
        <div class="site-label">Rekindle the Green</div>
        <h1>Welcome back</h1>
        <p>Sign in to continue your conservation journey.</p>
      </div>

      <div class="divider"></div>


      <div class="success-box" id="successBox">
        <h3>Signed in!</h3>
        <p>Welcome back. Taking you to the map...</p>
        <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
        <span class="redirect-note">Redirecting...</span>
      </div>

      <div class="form-wrap" id="formWrap">
        <form onsubmit="return false;">

          <div class="field" id="fl-email">
            <label>Email address</label>
            <input type="email" id="l-email" placeholder="you@example.com" autocomplete="email">
            <span class="err">Please enter a valid email.</span>
          </div>

          <div class="field" id="fl-pwd">
            <label>Password</label>
            <div class="pwd-wrap">
              <input type="password" id="l-pwd" placeholder="Your password" autocomplete="current-password">
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

        <p class="card-footer">
          No account? <a href="signup.php">Create one here</a>
        </p>
      </div>

    </div>
  </div>

<script>
  function togglePwd(id, btn) {
    var inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
  }

  function isValidEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }

  function markError(id) {
    document.getElementById(id).classList.add('has-error');
    return true;
  }

  function clearErrors() {
    document.querySelectorAll('.field.has-error').forEach(function(f){ f.classList.remove('has-error'); });
  }

  function closeModal() {
    document.getElementById('errorModal').classList.remove('show');
  }

  function showModal(title, msg) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = msg;
    document.getElementById('errorModal').classList.add('show');
  }

  function doLogin() {
    clearErrors();
    var hasErr = false;
    var email    = document.getElementById('l-email').value.trim();
    var password = document.getElementById('l-pwd').value;

    if (!isValidEmail(email)) hasErr = markError('fl-email');
    if (!password)            hasErr = markError('fl-pwd');
    if (hasErr) return;

    var btn = document.getElementById('loginBtn');
    btn.classList.add('loading');

    var fd = new FormData();
    fd.append('action', 'login');
    fd.append('email', email);
    fd.append('password', password);

    fetch('profile.php', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(data) {
        btn.classList.remove('loading');
        if (data.success) {
          document.getElementById('formWrap').style.display = 'none';
          var sb = document.getElementById('successBox');
          sb.classList.add('show');
          setTimeout(function(){ document.getElementById('progressFill').style.width = '100%'; }, 50);
          setTimeout(function(){ window.location.href = 'profile.php'; }, 2600);
        } else {
          var title = 'Sign In Failed';
          var msg   = data.message || 'Login failed. Please try again.';
          if (data.message === 'User not found')   { title = 'Account Not Found'; msg = 'No account exists with this email address.'; }
          if (data.message === 'Invalid password') { title = 'Wrong Password'; msg = 'The password you entered is incorrect. Please try again.'; }
          showModal(title, msg);
        }
      })
      .catch(function() {
        btn.classList.remove('loading');
        showModal('Connection Error', 'Something went wrong. Please check your connection and try again.');
      });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doLogin();
  });
</script>
</body>
</html>
