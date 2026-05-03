<?php

/**
 * =============================================================================
 * DASHBOARD V2.0 - AGRE FITNESS
 * =============================================================================
 * Tableau de bord avec système multilingue et navigation moderne.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

// Initialisation de la langue
initLanguage();

// Sécurité : si on n'est pas connecté, retour à la case départ
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$lang = getCurrentLang();

// Récupération des infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM fitness_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// --- CONSEIL DU JOUR ---
$conseils = [
  "L'hydratation est la clé : bois au moins 2L d'eau aujourd'hui.",
  "Contrôle bien la phase excentrique (la descente) de tes mouvements.",
  "Le repos fait partie de l'entraînement. Dors 7h à 8h par nuit.",
  "La constance bat l'intensité. Ne lâche rien !",
  "N'oublie pas de t'échauffer 5 à 10 minutes avant de forcer.",
  "Mange des protéines après ta séance pour la récupération musculaire.",
  "La forme avant la charge : ne sacrifie pas ta technique.",
  "La surcharge progressive est essentielle pour gagner en force.",
  "Écoute ton corps : la douleur aiguë signifie arrêt.",
  "Fixe-toi des objectifs SMART : Spécifiques, Mesurables, Atteignables."
];
$conseilDuJour = $conseils[array_rand($conseils)];

// --- SUIVI DE L'ÉVOLUTION ---
$stmt_logs = $pdo->prepare("
  SELECT wl.*, er.name_fr 
  FROM workout_logs wl 
  JOIN exercises_reference er ON wl.exercise_id = er.id 
  WHERE wl.user_id = ? 
  ORDER BY wl.date_completed DESC 
  LIMIT 5
");
$stmt_logs->execute([$_SESSION['user_id']]);
$logs = $stmt_logs->fetchAll();

// --- TOUTES LES VIDÉOS ---
$stmt_videos = $pdo->query("
  SELECT * FROM exercises_reference 
  WHERE video_file IS NOT NULL AND video_file != ''
  ORDER BY gender ASC, name_fr ASC
");
$allVideos = $stmt_videos->fetchAll();

// Séparer par gender
$videosMale = array_filter($allVideos, fn($v) => $v['gender'] === 'male');
$videosFemale = array_filter($allVideos, fn($v) => $v['gender'] === 'female');

// Résolution photo de profil : storage/profiles/ → défaut statique
$_uid        = (int)$_SESSION['user_id'];
$_storageDir = __DIR__ . '/../storage/profiles/';

if (!empty($user['profile_pic']) && file_exists($_storageDir . $user['profile_pic'])) {
  $profilePic = 'storage/profiles/' . $user['profile_pic'];
} elseif (file_exists($_storageDir . 'profile_' . $_uid . '.jpeg')) {
  $profilePic = 'storage/profiles/profile_' . $_uid . '.jpeg';
} elseif (file_exists($_storageDir . 'profile_' . $_uid . '.jpg')) {
  $profilePic = 'storage/profiles/profile_' . $_uid . '.jpg';
} else {
  $profilePic = 'assets/images/default-avatar.png';
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
  <meta name="description" content="AGRE Fitness - Votre application de suivi fitness">
  <link rel="manifest" href="../manifest.json">
  <link rel="apple-touch-icon" href="../assets/icons/icon-192x192.png">
  <title>Dashboard | AGRE Fitness Tracker </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Enregistrement du Service Worker
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
      background-color: #050505;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .glass-card {
      background: rgba(17, 17, 17, 0.8);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-2px);
      border-color: rgba(59, 130, 246, 0.2);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .animate-fade-in {
      animation: fadeIn 0.8s ease-out;
    }

    .animate-slide-up {
      animation: slideUp 0.6s ease-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
      h1 {
        font-size: 1.5rem !important;
      }

      .text-3xl {
        font-size: 1.25rem !important;
      }

      .text-4xl {
        font-size: 1.5rem !important;
      }

      .p-6 {
        padding: 1rem !important;
      }

      .pt-8 {
        padding-top: 1rem !important;
      }

      .gap-6 {
        gap: 0.75rem !important;
      }

      .w-20 {
        width: 3.5rem !important;
        height: 3.5rem !important;
      }
    }

    /* Touch-friendly buttons */
    button,
    a {
      min-height: 44px;
      min-width: 44px;
    }

    /* Safe area for iOS notch */
    .safe-top {
      padding-top: env(safe-area-inset-top);
    }

    .safe-bottom {
      padding-bottom: env(safe-area-inset-bottom);
    }
  </style>
</head>

<body class="text-white min-h-screen font-sans selection:bg-[#3b82f6] selection:text-white">
  <script>console.log('[PHOTO] Chemin résolu :', '../<?= addslashes($profilePic) ?>');</script>

  <!-- Navbar globale -->
  <?php require_once '../includes/navbar.php'; ?>

  <main class="max-w-6xl mx-auto p-6 pt-8">

    <!-- Section Profil Utilisateur -->
    <section class="animate-fade-in mb-10">
      <div class="flex items-center gap-6">
        <div class="relative">
          <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#3b82f6] bg-gray-800">
            <img src="../<?= htmlspecialchars($profilePic) ?>?v=<?= time() ?>"
              class="w-full h-full object-cover"
              alt="Photo de profil"
              onerror="this.src='../assets/images/default-avatar.png';">
          </div>
          <a href="profile.php" class="absolute -bottom-1 -right-1 w-8 h-8 bg-[#3b82f6] rounded-full flex items-center justify-center hover:bg-blue-500 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-1.572L16.732 3.732z" />
            </svg>
          </a>
        </div>
        <div>
          <p class="text-gray-500 text-sm">Bienvenue,</p>
          <h1 class="text-3xl md:text-4xl font-black tracking-tight">
            Bonjour <span class="text-[#3b82f6]"><?= htmlspecialchars($user['username']) ?></span> 👋
          </h1>
        </div>
      </div>
    </section>

    <!-- Message du Créateur avec photo -->
    <section class="glass-card rounded-2xl p-6 mb-8 border-l-4 border-[#3b82f6] animate-fade-in">
      <div class="flex items-start gap-4">
        <!-- Photo du Créateur Ange-Kevin Agre -->
        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#3b82f6] flex-shrink-0 bg-gray-800">
          <img src="../assets/images/PHOTO_AGRE_IDENTITE.jpeg"
            class="w-full h-full object-cover"
            style="object-position: center 20%;"
            alt="Ange-Kevin Agre - Créateur"
            onerror="this.src='../assets/images/default-avatar.png';">
        </div>
        <div class="flex-1">
          <h2 class="text-lg font-bold mb-2 text-[#3b82f6]">Bienvenue sur AGRE FITNESS TRACKER </h2>
          <p class="text-gray-300 leading-relaxed">
            Je suis <span class="text-white font-bold">Ange-Kevin Agre</span>, j'ai créé cette application pour vous aider à suivre votre évolution et exploser vos objectifs ! 💪
          </p>
        </div>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Colonne gauche : Conseil + Stats -->
      <div class="space-y-6">
        <!-- Conseil du jour -->
        <section class="glass-card rounded-2xl p-6 animate-fade-in">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-400">Conseil du jour</h3>
          </div>
          <p class="text-gray-300 italic leading-relaxed">"<?= htmlspecialchars($conseilDuJour) ?>"</p>
        </section>

        <!-- Stats rapides -->
        <section class="glass-card rounded-2xl p-6 animate-fade-in">
          <h3 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-4">Ton profil</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Poids actuel</span>
              <span class="font-bold text-[#3b82f6]"><?= $user['current_weight'] ? $user['current_weight'] . ' kg' : 'Non renseigné' ?></span>
            </div>
            <div class="h-px bg-gray-800"></div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Objectif</span>
              <span class="font-bold text-purple-400"><?= $user['goal_weight'] ? $user['goal_weight'] . ' kg' : 'Non renseigné' ?></span>
            </div>
            <div class="h-px bg-gray-800"></div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Taille</span>
              <span class="font-bold text-gray-300"><?= $user['height'] ? $user['height'] . ' cm' : 'Non renseigné' ?></span>
            </div>
          </div>
          <a href="profile.php" class="mt-4 block w-full text-center py-3 rounded-xl bg-gray-800 hover:bg-gray-700 text-sm font-bold transition-all">
            Modifier mes infos
          </a>
        </section>
      </div>

      <!-- Colonne droite : Suivi des séances -->
      <section class="lg:col-span-2 glass-card rounded-2xl p-6 animate-fade-in">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/30 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="font-bold text-lg">Suivi de l'évolution</h3>
        </div>

        <?php if (!empty($logs)): ?>
          <div class="space-y-3">
            <?php foreach ($logs as $log): ?>
              <div class="flex items-center justify-between p-4 rounded-xl bg-[#111] border border-gray-800 hover:border-gray-700 transition-all">
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-lg bg-[#3b82f6]/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#3b82f6]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold"><?= htmlspecialchars($log['name_fr']) ?></p>
                    <p class="text-gray-500 text-sm"><?= date('d/m/Y à H:i', strtotime($log['date_completed'])) ?></p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-black text-[#3b82f6] text-lg"><?= $log['weight'] ?> kg</p>
                  <p class="text-gray-500 text-sm"><?= $log['sets'] ?> séries × <?= $log['reps'] ?> reps</p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="add_workout.php" class="mt-4 block w-full text-center py-4 rounded-xl bg-[#3b82f6] hover:bg-blue-500 text-white font-bold transition-all">
            + Ajouter une nouvelle séance
          </a>
        <?php else: ?>
          <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-800 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <p class="text-gray-500 mb-4">Aucune séance enregistrée</p>
            <a href="add_workout.php" class="inline-block px-6 py-3 rounded-xl bg-[#3b82f6] hover:bg-blue-500 text-white font-bold transition-all">
              Noter ma première séance
            </a>
          </div>
        <?php endif; ?>
      </section>

    </div>

    <!-- Bloc Vidéos CTA (lien vers bibliothèque) -->
    <section class="mt-8 animate-fade-in">
      <div class="glass-card rounded-2xl p-8 text-center border border-red-500/20 hover:border-red-500/40 transition-all">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-500/10 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-xl font-bold mb-2">Besoin d'aide pour vos mouvements ?</h3>
        <p class="text-gray-500 mb-6">Accédez à notre bibliothèque complète d'exercices vidéo</p>
        <a href="exercices.php"
          class="inline-flex items-center gap-2 px-8 py-4 bg-red-500 hover:bg-red-400 text-white font-bold rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/25">
          <span>Voir la bibliothèque d'exercices</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </a>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mt-16 pb-6 text-center">
      <p class="text-gray-600 text-xs uppercase tracking-[0.3em] font-bold">
        <span class="text-[#3b82f6]">AGRE</span> FITNESS • Engineered for Excellence
      </p>
    </footer>

  </main>

</body>

</html>