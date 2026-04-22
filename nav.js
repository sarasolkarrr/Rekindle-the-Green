// Shared navigation bar injection script based on index.html navbar
(function () {
    const navTemplate = `
    <nav class="rtg-navbar" data-rtg-nav="true">
      <a class="nav-logo" href="index.html" aria-label="Rekindle the Green Home">
        <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <path d="M10 28 C10 28 14 18 26 10 C26 10 28 22 18 28 C18 28 15 30 10 28Z" fill="#4a8a2a"/>
          <path d="M18 28 C16 22 14 16 10 28Z" fill="#2d5a1a"/>
          <path d="M26 10 C24 16 20 22 18 28" stroke="#2d5a1a" stroke-width="1.5" fill="none"/>
          <path d="M16 32 C16 28 17 25 18 28" stroke="#4a8a2a" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span class="nav-logo-text">Rekindle the Green</span>
      </a>

      <span class="nav-center">Wildlife Conservation India</span>

      <div class="nav-actions">
        <a href="profile.php" class="btn-nav btn-login">Log In</a>
        <a href="profile.php" class="btn-nav btn-signup">Sign Up</a>
      </div>
    </nav>
    `;

    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('[data-rtg-nav="true"]')) {
            return;
        }

        document.body.insertAdjacentHTML('afterbegin', navTemplate);
    });
})();