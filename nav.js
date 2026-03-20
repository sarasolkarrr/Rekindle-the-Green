// Shared navigation bar injection script
(function () {
    const navTemplate = `
    <nav class="site-navbar">
      <div class="nav-brand">Rekindle the Wild</div>
      <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="profile.html">Profile</a></li>
        <li class="dropdown">
          <button class="dropbtn" type="button">Drives ▾</button>
          <div class="dropdown-content">
            <a href="turtle/turtle.html">Velas Beach</a>
            <a href="tiger.html">Jim Corbett National Park</a>
          </div>
        </li>
      </ul>
    </nav>
    `;

    document.addEventListener('DOMContentLoaded', function () {
        document.body.insertAdjacentHTML('afterbegin', navTemplate);
    });
})();