<?php

/**
 * =============================================================================
 * NAVBAR GLOBALE V2.0 - AGRE FITNESS
 * =============================================================================
 * Navigation responsive avec menu mobile et support multilingue.
 * Inclure ce fichier avec : require_once '../includes/navbar.php';
 */

// S'assurer que les traductions sont disponibles
if (!function_exists('__')) {
  require_once __DIR__ . '/translations.php';
}
if (session_status() === PHP_SESSION_ACTIVE && function_exists('initLanguage')) {
  initLanguage();
}

// Récupérer la langue courante
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

// Photo de profil pour la navbar (priorité : session user array > session directe > fichier statique > défaut)
$_navProfilePic = $_SESSION['user']['profile_pic']
  ?? $_SESSION['profile_pic']
  ?? null;
// Fallback statique : profile_{user_id}.jpeg commité dans le dépôt (contourne l'éphémère Railway)
if (empty($_navProfilePic) && !empty($_SESSION['user_id'])) {
  $_navStaticFile = __DIR__ . '/../assets/images/profiles/profile_' . (int)$_SESSION['user_id'] . '.jpeg';
  if (file_exists($_navStaticFile)) {
    $_navProfilePic = 'profiles/profile_' . (int)$_SESSION['user_id'] . '.jpeg';
  }
}
$_navAvatarUrl = !empty($_navProfilePic)
  ? '../assets/images/' . htmlspecialchars($_navProfilePic)
  : '../assets/images/default-avatar.png';
?>
<style>
  /* Navbar responsive styles */
  .navbar-mobile {
    display: none;
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(17, 17, 17, 0.98);
    backdrop-filter: blur(20px);
    z-index: 49;
    flex-direction: column;
    padding: 2rem 1rem;
    gap: 1rem;
    overflow-y: auto;
    transition: all 0.3s ease;
  }

  .navbar-mobile.active {
    display: flex;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .navbar-mobile a {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    text-align: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
  }

  .navbar-mobile a:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.5);
  }

  .burger-btn {
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    cursor: pointer;
    z-index: 51;
    background: transparent;
    border: none;
    padding: 8px;
  }

  .burger-btn span {
    display: block;
    width: 24px;
    height: 2px;
    background: white;
    margin: 3px 0;
    transition: all 0.3s ease;
    border-radius: 2px;
  }

  .burger-btn.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
  }

  .burger-btn.active span:nth-child(2) {
    opacity: 0;
  }

  .burger-btn.active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
  }

  /* Desktop navigation */
  .desktop-nav {
    display: flex;
  }

  /* Mobile adjustments */
  @media (max-width: 1024px) {
    .desktop-nav {
      display: none;
    }

    .burger-btn {
      display: flex;
    }

    .logo-center {
      position: static !important;
      transform: none !important;
      margin: 0 auto;
    }

    .right-nav {
      display: none;
    }
  }

  /* Safe area for iOS */
  .safe-area-top {
    padding-top: env(safe-area-inset-top);
  }
</style>

<nav class="sticky top-0 z-50 bg-[#111] border-b border-gray-800 safe-area-top">
  <div class="max-w-7xl mx-auto px-4 py-3">
    <div class="flex items-center justify-between">

      <!-- Gauche : Burger (mobile) + Navigation desktop -->
      <div class="flex items-center gap-2">
        <!-- Bouton Burger (visible sur mobile) -->
        <button class="burger-btn" id="burgerBtn" onclick="toggleMobileMenu()">
          <span></span>
          <span></span>
          <span></span>
        </button>

        <!-- Navigation Desktop (caché sur mobile) -->
        <div class="desktop-nav items-center gap-2">
          <a href="dashboard.php"
            class="px-3 py-2 rounded-lg text-xs lg:text-sm font-bold text-gray-400 hover:text-white hover:bg-gray-800/50 transition-all whitespace-nowrap">
            <?= strtoupper(__('nav_dashboard', $lang)) ?>
          </a>
          <a href="add_workout.php"
            class="px-3 py-2 rounded-lg text-xs lg:text-sm font-bold text-gray-400 hover:text-[#3b82f6] hover:bg-[#3b82f6]/10 transition-all whitespace-nowrap">
            💪
          </a>
          <a href="feed.php"
            class="px-3 py-2 rounded-lg text-xs lg:text-sm font-bold text-gray-400 hover:text-purple-400 hover:bg-purple-500/10 transition-all whitespace-nowrap">
            <?= strtoupper(__('nav_feed', $lang)) ?>
          </a>
          <a href="exercices.php"
            class="px-3 py-2 rounded-lg text-xs lg:text-sm font-bold text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all whitespace-nowrap">
            🎥
          </a>
          <a href="messages.php"
            class="px-3 py-2 rounded-lg text-xs lg:text-sm font-bold text-gray-400 hover:text-green-400 hover:bg-green-500/10 transition-all whitespace-nowrap">
            💬 <?= strtoupper(__('nav_messages', $lang)) ?>
          </a>
        </div>
      </div>

      <!-- Centre : Logo (toujours centré) -->
      <a href="dashboard.php" class="logo-center absolute left-1/2 transform -translate-x-1/2 text-lg md:text-xl font-black tracking-tighter">
        <span class="text-[#3b82f6]">FITNESS</span> <span class="text-white">TRACKER</span>
      </a>

      <!-- Droite : Profil + Quitter (desktop seulement) -->
      <div class="right-nav flex items-center gap-2">
        <a href="profile.php"
          class="flex items-center gap-2 px-3 py-1.5 rounded-xl hover:bg-gray-800/60 transition-all group">
          <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-gray-600 group-hover:border-[#3b82f6] transition-colors flex-shrink-0">
            <img src="<?= $_navAvatarUrl ?>"
              style="width:100%;height:100%;object-fit:cover;"
              alt="Mon profil"
              onerror="this.src='../assets/images/default-avatar.png'">
          </div>
          <span class="hidden xl:inline text-sm font-bold text-gray-400 group-hover:text-white transition-colors">
            <?= strtoupper(__('nav_profile', $lang)) ?>
          </span>
        </a>
        <a href="../logout.php"
          class="flex items-center gap-2 text-red-400 hover:text-white hover:bg-red-500 transition-all px-3 py-2 rounded-lg text-xs lg:text-sm font-bold border border-red-500/30 hover:border-red-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span class="hidden sm:inline"><?= strtoupper(__('nav_logout', $lang)) ?></span>
        </a>
      </div>
    </div>
  </div>

  <!-- Menu Mobile (caché par défaut) -->
  <div class="navbar-mobile" id="mobileMenu">
    <a href="dashboard.php" onclick="closeMobileMenu()">
      <span class="text-[#3b82f6]">🏠</span> <?= strtoupper(__('nav_dashboard', $lang)) ?>
    </a>
    <a href="add_workout.php" onclick="closeMobileMenu()">
      <span class="text-[#3b82f6]">💪</span> + <?= strtoupper(__('dashboard_add_workout', $lang)) ?>
    </a>
    <a href="feed.php" onclick="closeMobileMenu()">
      <span class="text-purple-400">📱</span> <?= strtoupper(__('nav_feed', $lang)) ?>
    </a>
    <a href="exercices.php" onclick="closeMobileMenu()">
      <span class="text-red-400">🎥</span> <?= strtoupper(__('nav_exercises', $lang)) ?>
    </a>
    <a href="messages.php" onclick="closeMobileMenu()">
      <span class="text-green-400">💬</span> <?= strtoupper(__('nav_messages', $lang)) ?>
    </a>
    <a href="profile.php" onclick="closeMobileMenu()">
      <span class="text-gray-400">👤</span> <?= strtoupper(__('nav_profile', $lang)) ?>
    </a>
    <a href="../logout.php" onclick="closeMobileMenu()" class="text-red-400 border-red-500/30">
      <span>🚪</span> <?= strtoupper(__('nav_logout', $lang)) ?>
    </a>
  </div>
</nav>

<script>
  // Toggle du menu mobile
  function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('burgerBtn');

    menu.classList.toggle('active');
    btn.classList.toggle('active');

    // Empêcher le scroll du body quand le menu est ouvert
    if (menu.classList.contains('active')) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }

  // Fermer le menu mobile
  function closeMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('burgerBtn');

    menu.classList.remove('active');
    btn.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Fermer le menu si on clique en dehors
  document.addEventListener('click', function(e) {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('burgerBtn');

    if (menu.classList.contains('active') &&
      !menu.contains(e.target) &&
      !btn.contains(e.target)) {
      closeMobileMenu();
    }
  });

  // Fermer le menu avec la touche Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeMobileMenu();
    }
  });
</script>