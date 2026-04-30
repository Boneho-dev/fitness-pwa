<?php

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

initLanguage();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

// --- TOUTES LES VIDÉOS (video_file stocké en DB) ---
try {
  $stmt = $pdo->query("
        SELECT id, name_fr, muscle_group, gender, video_file
        FROM exercises_reference
        WHERE video_file IS NOT NULL AND video_file != ''
        ORDER BY name_fr ASC
    ");
  $allVideos = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Erreur récupération exercices: " . $e->getMessage());
  $allVideos = [];
}

$videosMale   = array_filter($allVideos, fn($v) => $v['gender'] === 'male');
$videosFemale = array_filter($allVideos, fn($v) => $v['gender'] === 'female');

$stmt_user = $pdo->prepare("SELECT * FROM fitness_users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();
$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default-avatar.png';

/**
 * Construit l'URL relative d'une vidéo depuis son video_file en DB.
 * URL-encode le nom de fichier pour gérer espaces, accents et double extensions.
 *
 * @param string $videoFile  ex: assets/videos/male/1_Developpe_couche_barre.mp4.mp4
 * @return string            ex: ../assets/videos/male/1_Developpe_couche_barre.mp4.mp4
 */
function videoUrl(string $videoFile): string
{
  $dir  = dirname($videoFile);
  $file = basename($videoFile);
  return '../' . $dir . '/' . rawurlencode($file);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="AGRE Fitness">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="description" content="Maîtrisez chaque mouvement avec nos tutoriels vidéo">
  <link rel="manifest" href="../manifest.json">
  <link rel="apple-touch-icon" href="../assets/icons/icon-192x192.png">
  <title>Exercices | AGRE Fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('../service-worker.js')
        .then(reg => console.log('Service Worker registered'))
        .catch(err => console.log('Service Worker error:', err));
    }
  </script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }

    body {
      background: linear-gradient(180deg, #050505 0%, #0a0a0a 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      min-height: 100vh;
    }

    .glass-card {
      background: rgba(17, 17, 17, 0.8);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
      animation: slideUp 0.6s ease-out;
      animation-fill-mode: both;
    }

    .glass-card:hover {
      transform: translateY(-4px) scale(1.02);
      border-color: rgba(59, 130, 246, 0.3);
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
    }

    .glass-card:nth-child(1) { animation-delay: 0.05s; }
    .glass-card:nth-child(2) { animation-delay: 0.1s; }
    .glass-card:nth-child(3) { animation-delay: 0.15s; }
    .glass-card:nth-child(4) { animation-delay: 0.2s; }
    .glass-card:nth-child(5) { animation-delay: 0.25s; }
    .glass-card:nth-child(6) { animation-delay: 0.3s; }

    .tab-active {
      background: rgba(59, 130, 246, 0.2);
      border-color: rgba(59, 130, 246, 0.5);
    }

    .animate-fade-in {
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(40px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .tab-btn { transition: all 0.3s ease; }
    .tab-btn:hover { transform: translateY(-2px); background: rgba(255, 255, 255, 0.05); }
    .tab-btn:active { transform: scale(0.98); }

    .tab-content { display: none; }

    /* Desktop : 3 colonnes */
    .video-grid-desktop {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }

    @media (max-width: 1024px) {
      .video-grid-desktop {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }
    }

    /* Mobile : 2 colonnes miniatures */
    .video-grid-mobile {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
    }

    @media (min-width: 641px) {
      .video-grid-mobile { display: none; }
    }

    @media (max-width: 640px) {
      .video-grid-desktop { display: none; }

      .mobile-thumbnail {
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
      }

      .mobile-thumbnail video {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .mobile-play-icon {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.4);
      }

      .mobile-info { padding: 8px 4px 4px; }
      .mobile-info h4 {
        font-size: 0.75rem;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .mobile-info p { font-size: 0.625rem; }

      h1 { font-size: 1.5rem !important; }
      .text-3xl { font-size: 1.25rem !important; }
      .p-6 { padding: 0.75rem !important; }
      .px-8 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }

      .tab-btn span:last-child { display: none; }
      .tab-btn { padding: 0.5rem 1rem; font-size: 0.875rem; }
    }

    button, a, .glass-card { min-height: 44px; }

    .video-thumb {
      position: relative;
      cursor: pointer;
      overflow: hidden;
      border-radius: 12px;
      aspect-ratio: 16/9;
      background: #111;
    }

    .video-thumb video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .video-thumb:hover video { transform: scale(1.05); }

    .video-thumb .play-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.4);
      transition: opacity 0.3s;
    }

    .video-thumb:hover .play-overlay { opacity: 0.8; }

    .video-thumb .play-icon {
      width: 60px;
      height: 60px;
      background: rgba(59, 130, 246, 0.9);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s;
    }

    .video-thumb:hover .play-icon { transform: scale(1.1); }

    .video-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.98);
      backdrop-filter: blur(20px);
      z-index: 100;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .video-modal.active {
      display: flex;
      animation: fadeIn 0.3s ease;
    }

    .video-modal video {
      max-width: 90vw;
      max-height: 80vh;
      border-radius: 12px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }

    .video-modal .close-btn {
      position: absolute;
      top: 30px;
      left: 30px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 12px 20px;
      border-radius: 30px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .video-modal .close-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateX(-5px);
    }

    .video-modal .video-info {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(10px);
      padding: 15px 30px;
      border-radius: 15px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tab-content.active { display: block; }
  </style>
</head>

<body class="text-white min-h-screen font-sans selection:bg-[#3b82f6] selection:text-white">

  <!-- Bouton Retour -->
  <div class="max-w-7xl mx-auto px-6 pt-4">
    <a href="dashboard.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-all group">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      <span class="text-sm font-bold">Retour au Dashboard</span>
    </a>
  </div>

  <main class="max-w-7xl mx-auto p-6">

    <!-- Header -->
    <header class="text-center mb-8 animate-fade-in">
      <h1 class="text-3xl font-black tracking-tight mb-2">
        <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
          Bibliothèque d'exercices
        </span>
      </h1>
      <p class="text-gray-500 text-sm">Maîtrisez chaque mouvement avec nos tutoriels vidéo</p>
    </header>

    <!-- Onglets de filtrage -->
    <div class="flex justify-center gap-4 mb-8 animate-fade-in">
      <button onclick="switchTab('hommes')" id="tab-hommes" class="tab-btn px-8 py-3 rounded-xl border border-gray-700 font-bold flex items-center gap-2 tab-active">
        <span class="text-xl">♂️</span>
        <span>Espace Hommes</span>
      </button>
      <button onclick="switchTab('femmes')" id="tab-femmes" class="tab-btn px-8 py-3 rounded-xl border border-gray-700 font-bold flex items-center gap-2">
        <span class="text-xl">♀️</span>
        <span>Espace Femmes</span>
      </button>
    </div>

    <!-- Section Hommes -->
    <div id="content-hommes" class="tab-content active animate-fade-in">
      <?php if (!empty($videosMale)): ?>
        <!-- Desktop Grid -->
        <div class="video-grid-desktop">
          <?php foreach ($videosMale as $video):
            $videoUrl = videoUrl($video['video_file']);
          ?>
            <div class="glass-card rounded-xl p-3 hover:border-blue-500/30 transition-all group"
              onclick="openVideoModal('<?= htmlspecialchars($videoUrl) ?>', '<?= htmlspecialchars(addslashes($video['name_fr'])) ?>', 'hommes')">
              <div class="video-thumb mb-3 relative">
                <video src="<?= htmlspecialchars($videoUrl) ?>"
                  muted loop preload="metadata"
                  onmouseenter="this.play()"
                  onmouseleave="this.pause(); this.currentTime = 0;">
                </video>
                <div class="play-overlay">
                  <div class="play-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z" />
                    </svg>
                  </div>
                </div>
              </div>
              <h4 class="font-bold text-sm mb-1 text-blue-400 truncate"><?= htmlspecialchars($video['name_fr']) ?></h4>
              <p class="text-gray-500 text-xs truncate"><?= htmlspecialchars($video['muscle_group'] ?? 'général') ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Mobile Grid -->
        <div class="video-grid-mobile">
          <?php foreach ($videosMale as $video):
            $videoUrl = videoUrl($video['video_file']);
          ?>
            <div class="mobile-thumbnail group"
              onclick="openVideoModal('<?= htmlspecialchars($videoUrl) ?>', '<?= htmlspecialchars(addslashes($video['name_fr'])) ?>', 'hommes')">
              <video src="<?= htmlspecialchars($videoUrl) ?>" muted preload="metadata"></video>
              <div class="mobile-play-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8 5v14l11-7z" />
                </svg>
              </div>
              <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                <h4 class="text-white text-xs font-bold truncate"><?= htmlspecialchars($video['name_fr']) ?></h4>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-12 glass-card rounded-xl">
          <p class="text-gray-500">Aucune vidéo disponible pour le moment</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Section Femmes -->
    <div id="content-femmes" class="tab-content animate-fade-in">
      <?php if (!empty($videosFemale)): ?>
        <!-- Desktop Grid -->
        <div class="video-grid-desktop">
          <?php foreach ($videosFemale as $video):
            $videoUrl = videoUrl($video['video_file']);
          ?>
            <div class="glass-card rounded-xl p-3 hover:border-pink-500/30 transition-all group"
              onclick="openVideoModal('<?= htmlspecialchars($videoUrl) ?>', '<?= htmlspecialchars(addslashes($video['name_fr'])) ?>', 'femmes')">
              <div class="video-thumb mb-3 relative">
                <video src="<?= htmlspecialchars($videoUrl) ?>"
                  muted loop preload="metadata"
                  onmouseenter="this.play()"
                  onmouseleave="this.pause(); this.currentTime = 0;">
                </video>
                <div class="play-overlay">
                  <div class="play-icon" style="background: rgba(236, 72, 153, 0.9);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z" />
                    </svg>
                  </div>
                </div>
              </div>
              <h4 class="font-bold text-sm mb-1 text-pink-400 truncate"><?= htmlspecialchars($video['name_fr']) ?></h4>
              <p class="text-gray-500 text-xs truncate"><?= htmlspecialchars($video['muscle_group'] ?? 'général') ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Mobile Grid -->
        <div class="video-grid-mobile">
          <?php foreach ($videosFemale as $video):
            $videoUrl = videoUrl($video['video_file']);
          ?>
            <div class="mobile-thumbnail group"
              onclick="openVideoModal('<?= htmlspecialchars($videoUrl) ?>', '<?= htmlspecialchars(addslashes($video['name_fr'])) ?>', 'femmes')">
              <video src="<?= htmlspecialchars($videoUrl) ?>" muted preload="metadata"></video>
              <div class="mobile-play-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8 5v14l11-7z" />
                </svg>
              </div>
              <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                <h4 class="text-white text-xs font-bold truncate"><?= htmlspecialchars($video['name_fr']) ?></h4>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-12 glass-card rounded-xl">
          <p class="text-gray-500">Aucune vidéo disponible pour le moment</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="mt-16 pb-6 text-center">
      <p class="text-gray-600 text-xs uppercase tracking-[0.3em] font-bold">
        <span class="text-[#3b82f6]">AGRE</span> FITNESS
      </p>
    </footer>

  </main>

  <!-- Modal vidéo plein écran -->
  <div id="videoModal" class="video-modal">
    <button onclick="closeVideoModal()" class="close-btn">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Retour
    </button>

    <video id="modalVideo" controls autoplay></video>

    <div class="video-info">
      <h3 id="modalTitle" class="text-xl font-bold text-white"></h3>
      <p id="modalCategory" class="text-sm text-gray-400 mt-1"></p>
    </div>
  </div>

  <script>
    function switchTab(tab) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('tab-active'));
      document.getElementById('content-' + tab).classList.add('active');
      document.getElementById('tab-' + tab).classList.add('tab-active');
      window.currentTab = tab;
    }

    function openVideoModal(videoUrl, title, category) {
      const modal = document.getElementById('videoModal');
      const video = document.getElementById('modalVideo');
      const modalTitle = document.getElementById('modalTitle');
      const modalCategory = document.getElementById('modalCategory');

      video.src = videoUrl;
      modalTitle.textContent = title;
      modalCategory.textContent = category === 'hommes' ? 'Espace Hommes ♂️' : 'Espace Femmes ♀️';

      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeVideoModal() {
      const modal = document.getElementById('videoModal');
      const video = document.getElementById('modalVideo');

      video.pause();
      video.src = '';
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeVideoModal();
    });

    document.getElementById('videoModal').addEventListener('click', function(e) {
      if (e.target === this) closeVideoModal();
    });
  </script>

</body>

</html>
