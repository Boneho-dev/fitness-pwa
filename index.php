<?php

/**
 * =============================================================================
 * PAGE DE CONNEXION - AGRE FITNESS v2.0
 * =============================================================================
 * Système multilingue (7 langues) avec persistance de la langue choisie.
 * Interface épurée avec sélecteur de langue et informations PWA.
 */

// DEBUG - Afficher toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
require_once 'config/db.php';
require_once 'includes/translations.php';

// Initialisation de la langue
initLanguage();

// Changement de langue si demandé
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], SUPPORTED_LANGUAGES)) {
  setLanguage($_GET['lang']);
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Si l'utilisateur est déjà connecté, on l'envoie vers le dashboard
if (isset($_SESSION['user_id'])) {
  header('Location: pages/dashboard.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!empty($username) && !empty($password)) {
    // Requête préparée sécurisée
    $stmt = $pdo->prepare("SELECT * FROM fitness_users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // Vérification avec password_verify pour les nouveaux comptes
    if ($user && (password_verify($password, $user['password_hash']) || $password === $user['password_hash'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['profile_pic'] = $user['profile_pic'] ?? null;
      header('Location: pages/dashboard.php');
      exit;
    } else {
      $error = __('login_error');
    }
  } else {
    $error = __('error_required');
  }
}

$lang = $_SESSION['lang'] ?? 'fr';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="AGRE Fitness">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="description" content="AGRE Fitness - Suivez votre évolution et partagez avec la communauté">
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/png" sizes="96x96" href="/assets/icons/favicon-96x96.png">
  <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
  <title><?= __('login_title') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
    }

    body {
      background: linear-gradient(135deg, #050505 0%, #0a0a0a 50%, #050505 100%);
      min-height: 100vh;
    }

    .glass-card {
      background: rgba(17, 17, 17, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* Sélecteur de langue élégant */
    .lang-selector {
      position: relative;
    }

    .lang-btn {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 8px 12px;
      color: white;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .lang-btn:hover {
      background: rgba(59, 130, 246, 0.2);
      border-color: rgba(59, 130, 246, 0.4);
    }

    .lang-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 8px;
      background: rgba(20, 20, 20, 0.98);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 8px;
      min-width: 180px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 100;
    }

    .lang-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .lang-option {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      color: white;
      text-decoration: none;
      font-size: 0.875rem;
      transition: all 0.2s ease;
    }

    .lang-option:hover {
      background: rgba(59, 130, 246, 0.15);
    }

    .lang-option.active {
      background: rgba(59, 130, 246, 0.25);
    }

    /* PWA Install Banner */
    .pwa-banner {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(147, 51, 234, 0.15) 100%);
      border: 1px solid rgba(59, 130, 246, 0.3);
      border-radius: 16px;
      padding: 16px;
      margin-bottom: 24px;
      display: none;
    }

    .pwa-banner.visible {
      display: block;
      animation: slideDown 0.5s ease;
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

    .pwa-install-btn {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .pwa-install-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    .input-focus-ring:focus {
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
  </style>

  <link rel="icon" type="image/png" href="/assets/icons/favicon-96x96.png?v=1.0" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg?v=1.0" />
  <link rel="shortcut icon" href="/assets/icons/favicon.ico?v=1.0" />
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png?v=1.0" />
  <meta name="apple-mobile-web-app-title" content="AGRE Fitness" />
  <link rel="manifest" href="/assets/icons/site.webmanifest?v=1.0" />

</head>

<body class="text-white flex items-center justify-center min-h-screen font-sans p-4">

  <div class="glass-card p-8 rounded-3xl w-full max-w-md mx-4">
    <!-- Header avec sélecteur de langue -->
    <div class="flex items-center justify-between mb-6">
      <div class="lang-selector">
        <button class="lang-btn" onclick="toggleLangDropdown()" type="button">
          <span><?= SUPPORTED_LANGUAGES[$lang]['flag'] ?? '🏳️' ?></span>
          <span><?= strtoupper($lang) ?></span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div class="lang-dropdown" id="langDropdown">
          <?php foreach (SUPPORTED_LANGUAGES as $code => $info): ?>
            <a href="?lang=<?= $code ?>" class="lang-option <?= $code === $lang ? 'active' : '' ?>">
              <span><?= $info['flag'] ?></span>
              <span><?= $info['name'] ?></span>
              <?php if ($code === $lang): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Bouton PWA Info (toujours visible) -->
      <button onclick="showPWAInfo()" class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-all" title="Installer l'application">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
      </button>
    </div>

    <!-- Banner PWA (affiché conditionnellement) -->
    <div class="pwa-banner" id="pwaBanner">
      <div class="flex items-start gap-3">
        <div class="p-2 rounded-xl bg-blue-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-semibold mb-1"><?= __('pwa_install_title') ?></p>
          <p class="text-xs text-gray-400 mb-3"><?= __('pwa_install_description') ?></p>
          <button class="pwa-install-btn" id="pwaInstallBtn">
            <?= __('pwa_install_button') ?>
          </button>
        </div>
        <button onclick="hidePWABanner()" class="text-gray-500 hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Logo -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-black tracking-tighter mb-2">
        <span class="text-[#3b82f6]">AGRE</span> <span class="text-white">FITNESS</span>
      </h1>
      <p class="text-gray-500 text-sm"><?= __('login_subtitle') ?></p>
    </div>

    <!-- Alertes -->
    <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm animate-pulse">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <!-- Pseudo -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2"><?= __('login_username') ?></label>
        <input type="text" name="username" required
          class="input-focus-ring w-full p-3.5 rounded-xl bg-[#111] border border-gray-800 text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all"
          placeholder="<?= __('login_username_placeholder') ?>">
      </div>

      <!-- Mot de passe -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2"><?= __('login_password') ?></label>
        <input type="password" name="password" required
          class="input-focus-ring w-full p-3.5 rounded-xl bg-[#111] border border-gray-800 text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all"
          placeholder="••••••••">
      </div>

      <!-- Bouton -->
      <button type="submit"
        class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-blue-500/25">
        <?= __('login_button') ?>
      </button>
    </form>

    <!-- Lien inscription -->
    <p class="text-center text-gray-500 text-sm mt-6">
      <?= __('login_no_account') ?>
      <a href="signup.php" class="text-[#3b82f6] hover:text-blue-400 font-medium hover:underline transition-colors"><?= __('login_signup') ?></a>
    </p>

    <!-- Footer info -->
    <p class="text-center text-gray-600 text-xs mt-8">
      AGRE Fitness v2.0 • <?= __('footer_rights') ?>
    </p>
  </div>

  <script>
    // Enregistrement du Service Worker PWA
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
          .catch((err) => console.warn('SW registration failed:', err))
      })
    }

    // Gestion du dropdown de langue
    function toggleLangDropdown() {
      const dropdown = document.getElementById('langDropdown');
      dropdown.classList.toggle('active');
    }

    // Fermer le dropdown en cliquant ailleurs
    document.addEventListener('click', function(e) {
      const selector = document.querySelector('.lang-selector');
      const dropdown = document.getElementById('langDropdown');
      if (!selector.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });

    // PWA Install Logic
    let deferredPrompt;
    const pwaBanner = document.getElementById('pwaBanner');
    const pwaInstallBtn = document.getElementById('pwaInstallBtn');

    // Vérifier si l'app est déjà installée
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
      window.navigator.standalone === true;

    if (!isStandalone) {
      // Afficher le bouton PWA dans le header
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
      });
    }

    function showPWAInfo() {
      pwaBanner.classList.add('vi sible');
    }

    function hidePWABanner() {
      pwaBanner.classList.remove('visible');
    }

    pwaInstallBtn.addEventListener('click', async () => {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const {
          outcome
        } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
          hidePWABanner();
        }
        deferredPrompt = null;
      } else {
        // Guide l'utilisateur pour ajouter manuellement
        alert('Pour installer AGRE Fitness:\n\niOS: Appuyez sur Partager → "Sur l\'écran d\'accueil"\n\nAndroid: Menu ⋮ → "Ajouter à l\'écran d\'accueil"');
      }
    });
  </script>

</body>

</html>