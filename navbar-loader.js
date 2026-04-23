function handleLogout() {
  localStorage.removeItem('rtg_user_name');
  localStorage.removeItem('rtg_user_id');
  window.location.href = 'logout.php';
}

function loadNavbar() {
  const userName = localStorage.getItem('rtg_user_name');
  const userInitials = userName ? userName.charAt(0).toUpperCase() : '';
  const isLoggedIn = !!userName;

  const isIndexPage = window.location.pathname.includes('index.html') || 
                      window.location.pathname.endsWith('/');

  let navActionsHTML = '';

  if (isLoggedIn) {
    navActionsHTML += `
      <a href="profile.php" class="nav-user">
        <div class="nav-avatar">${userInitials}</div>
      </a>
      <a href="javascript:void(0)" onclick="handleLogout()" class="btn-nav btn-logout">Log Out</a>
    `;
  } else {
    navActionsHTML += `
      <a href="login.php" class="btn-nav btn-login">Log In</a>
      <a href="signup.php" class="btn-nav btn-signup">Sign Up</a>
    `;
  }

  const navHTML = `
    <style>
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
      }
      .nav-logo svg { width: 28px; height: 28px; flex-shrink: 0; }
      .nav-logo-text {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        color: #fff;
        white-space: nowrap;
      }
      .nav-logo-text em {
        font-family: 'Playfair Display', serif;
        font-style: italic;
        color: #c9a84c;
      }

      .nav-center {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.58rem;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
        pointer-events: none;
        white-space: nowrap;
      }

      .nav-actions {
        display: flex;
        gap: 0.8rem;
        align-items: center;
      }

      .nav-user {
        display: flex;
        align-items: center;
        cursor: pointer;
        text-decoration: none;
        transition: opacity 0.18s;
      }

      .nav-user:hover {
        opacity: 0.85;
      }

      .nav-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #3d6b2a;
        border: 2px solid rgba(201, 168, 76, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
      }

      .btn-nav {
        padding: 0.38rem 1rem;
        border-radius: 5px;
        font-family: 'Inter', sans-serif;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.18s;
        display: inline-block;
      }

      .btn-login {
        background: transparent;
        color: #c9a84c;
        border: 1.5px solid #c9a84c;
      }
      .btn-login:hover { background: rgba(201,168,76,0.12); }

      .btn-signup {
        background: #c9a84c;
        color: #1a2e1a;
        border: 1.5px solid #c9a84c;
      }
      .btn-signup:hover { background: #e8c96a; border-color: #e8c96a; }

      .btn-home {
        background: transparent;
        color: rgba(255,255,255,0.6);
        border: 1.5px solid rgba(255,255,255,0.2);
      }
      .btn-home:hover {
        background: rgba(255,255,255,0.08);
        color: #fff;
      }

      .btn-logout {
        background: transparent;
        color: #e88;
        border: 1.5px solid rgba(220, 100, 100, 0.35);
      }
      .btn-logout:hover {
        background: rgba(220, 100, 100, 0.1);
        color: #f99;
      }
    </style>

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
        ${navActionsHTML}
      </div>
    </nav>
  `;

  const existingNav = document.querySelector('nav');
  if (existingNav) {
    existingNav.insertAdjacentHTML('beforebegin', navHTML);
    existingNav.remove();
  } else {
    document.body.insertAdjacentHTML('afterbegin', navHTML);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadNavbar);
} else {
  loadNavbar();
}