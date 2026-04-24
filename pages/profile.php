<?php

/**
 * =============================================================================
 * PROFIL V2.0 - AGRE FITNESS
 * =============================================================================
 * Gestion du profil avec système de follow, affichage des posts,
 * et design responsive moderne avec style Glassmorphism.
 * 
 * Fonctionnalités :
 * - Affichage profil propre (soi ou autre utilisateur)
 * - Système Suivre/Ne plus suivre avec AJAX
 * - Affichage des publications de l'utilisateur
 * - Statistiques followers/following
 * - Protection XSS via htmlspecialchars
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

// Initialisation de la langue
initLanguage();

// Vérification de session
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$current_user_id = (int)$_SESSION['user_id'];
$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : $current_user_id;
$is_own_profile = ($profile_user_id === $current_user_id);
$message = "";
$lang = $_SESSION['lang'] ?? 'fr';

// =============================================================================
// MISE À JOUR DU PROFIL (uniquement pour son propre profil)
// =============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $gender = $_POST['gender'] ?? 'male';
  $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT) ?: null;
  $current_weight = filter_input(INPUT_POST, 'current_weight', FILTER_VALIDATE_FLOAT) ?: null;
  $goal_weight = filter_input(INPUT_POST, 'goal_weight', FILTER_VALIDATE_FLOAT) ?: null;
  $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_INT) ?: null;
  $bio = trim($_POST['bio'] ?? '');

  // Récupération de l'ancienne photo
  $profile_pic = $_POST['old_pic'] ?? '';

  // Upload de la nouvelle photo
  if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../assets/images/";

    // Validation du type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($_FILES['profile_pic']['tmp_name']);

    if (in_array($file_type, $allowed_types)) {
      $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
      $file_name = "profile_" . $current_user_id . "_" . time() . "." . $file_ext;

      if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $file_name)) {
        $profile_pic = $file_name;
        $_SESSION['profile_pic'] = $profile_pic;
      }
    }
  }

  // Mise à jour en base de données
  try {
    $sql = "UPDATE fitness_users 
                SET username = :username, 
                    email = :email, 
                    gender = :gender, 
                    age = :age, 
                    height = :height, 
                    current_weight = :current_weight, 
                    goal_weight = :goal_weight, 
                    bio = :bio, 
                    profile_pic = :profile_pic,
                    last_active = NOW()
                WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':username' => $username,
      ':email' => $email,
      ':gender' => $gender,
      ':age' => $age,
      ':height' => $height,
      ':current_weight' => $current_weight,
      ':goal_weight' => $goal_weight,
      ':bio' => $bio,
      ':profile_pic' => $profile_pic,
      ':id' => $current_user_id
    ]);

    $message = __('profile_success_update', $lang);
    $_SESSION['username'] = $username;
  } catch (PDOException $e) {
    $message = "Erreur lors de la mise à jour.";
    error_log("Erreur mise à jour profil: " . $e->getMessage());
  }
}

// =============================================================================
// RÉCUPÉRATION DES DONNÉES DU PROFIL
// =============================================================================

try {
  $stmt = $pdo->prepare("SELECT * FROM fitness_users WHERE id = :id");
  $stmt->execute([':id' => $profile_user_id]);
  $user = $stmt->fetch();

  if (!$user) {
    header('Location: profile.php');
    exit;
  }
} catch (PDOException $e) {
  error_log("Erreur récupération profil: " . $e->getMessage());
  header('Location: dashboard.php');
  exit;
}

$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default_avatar.png';

// =============================================================================
// STATISTIQUES FOLLOWERS/FOLLOWING
// =============================================================================

try {
  // Nombre d'abonnés (followers)
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE following_id = :id");
  $stmt->execute([':id' => $profile_user_id]);
  $followersCount = (int)$stmt->fetchColumn();

  // Nombre d'abonnements (following)
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = :id");
  $stmt->execute([':id' => $profile_user_id]);
  $followingCount = (int)$stmt->fetchColumn();

  // Vérifier si l'utilisateur connecté suit ce profil
  $isFollowing = false;
  if (!$is_own_profile) {
    $stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id = :follower AND following_id = :following");
    $stmt->execute([
      ':follower' => $current_user_id,
      ':following' => $profile_user_id
    ]);
    $isFollowing = (bool)$stmt->fetch();
  }
} catch (PDOException $e) {
  error_log("Erreur statistiques follow: " . $e->getMessage());
  $followersCount = 0;
  $followingCount = 0;
  $isFollowing = false;
}

// =============================================================================
// RÉCUPÉRATION DES POSTS DE L'UTILISATEUR
// =============================================================================

try {
  $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.image_url, 
            p.caption, 
            p.created_at,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
        FROM posts p
        WHERE p.user_id = :user_id
        ORDER BY p.created_at DESC
    ");
  $stmt->execute([':user_id' => $profile_user_id]);
  $profilePosts = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Erreur récupération posts: " . $e->getMessage());
  $profilePosts = [];
}

/**
 * Formate la date relative
 * 
 * @param string $datetime Timestamp
 * @param string $lang Code langue
 * @return string Date formatée
 */
function formatRelativeTime(string $datetime, string $lang): string
{
  $time = strtotime($datetime);
  $diff = time() - $time;

  if ($diff < 86400) return __('time_just_now', $lang);
  if ($diff < 604800) {
    $days = floor($diff / 86400);
    return $days . ' ' . __('time_day', $lang);
  }
  return date('d M Y', $time);
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title><?= $is_own_profile ? __('profile_title', $lang) : htmlspecialchars($user['username']) . ' | AGRE Fitness' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
    }

    body {
      background: linear-gradient(180deg, #050505 0%, #0a0a0a 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Glassmorphism */
    .glass-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .glass-input {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .glass-input:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(59, 130, 246, 0.5);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in {
      animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-delay-1 {
      animation-delay: 0.1s;
    }

    .animate-delay-2 {
      animation-delay: 0.2s;
    }

    .animate-delay-3 {
      animation-delay: 0.3s;
    }

    /* Avatar Styles */
    .avatar-ring {
      position: relative;
    }

    .avatar-ring::before {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      z-index: -1;
    }

    /* Follow Button */
    .btn-follow {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      transition: all 0.3s ease;
    }

    .btn-follow:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    .btn-following {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-following:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: rgba(239, 68, 68, 0.5);
      color: #ef4444;
    }

    /* Stats Card */
    .stat-card {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      background: rgba(255, 255, 255, 0.05);
      transform: translateY(-2px);
    }

    /* Post Grid */
    .post-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 4px;
    }

    @media (min-width: 768px) {
      .post-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
      }
    }

    .post-item {
      aspect-ratio: 1;
      position: relative;
      overflow: hidden;
      border-radius: 8px;
      cursor: pointer;
    }

    .post-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .post-item:hover img {
      transform: scale(1.1);
    }

    .post-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      opacity: 0;
      transition: opacity 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
    }

    .post-item:hover .post-overlay {
      opacity: 1;
    }

    /* Gender Badge */
    .gender-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .gender-male {
      background: rgba(59, 130, 246, 0.2);
      color: #60a5fa;
    }

    .gender-female {
      background: rgba(236, 72, 153, 0.2);
      color: #f472b6;
    }
  </style>
</head>

<body class="text-white min-h-screen selection:bg-blue-500 selection:text-white">
  <?php require_once '../includes/navbar.php'; ?>

  <main class="max-w-4xl mx-auto p-4 pt-24 pb-8">
    <!-- Header du profil -->
    <header class="text-center mb-8 animate-fade-in">
      <h1 class="text-3xl font-black tracking-tight mb-2">
        <?php if ($is_own_profile): ?>
          <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
            <?= __('profile_header_own', $lang) ?>
          </span>
        <?php else: ?>
          <span class="text-gray-400"><?= __('profile_header_other', $lang) ?></span>
          <span class="text-white"><?= htmlspecialchars($user['username']) ?></span>
        <?php endif; ?>
      </h1>
      <p class="text-gray-500 text-sm">
        <?= $is_own_profile ? __('profile_subtitle_own', $lang) : __('profile_subtitle_other', $lang) ?>
      </p>
    </header>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
      <div class="glass-card rounded-2xl p-4 mb-6 text-center animate-fade-in border-l-4 border-green-500">
        <p class="text-green-400 font-semibold flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <?= htmlspecialchars($message) ?>
        </p>
      </div>
    <?php endif; ?>

    <!-- Section Profil Header -->
    <div class="glass-card rounded-3xl p-6 mb-6 animate-fade-in animate-delay-1">
      <div class="flex flex-col md:flex-row items-center gap-6">
        <!-- Photo de profil -->
        <div class="relative group">
          <div class="avatar-ring">
            <div class="w-28 h-28 rounded-full overflow-hidden bg-gray-800 border-4 border-[#111]">
              <img id="preview"
                src="../assets/images/<?= htmlspecialchars($profilePic) ?>?v=2"
                class="w-full h-full object-cover"
                alt="<?= htmlspecialchars($user['username']) ?>">
            </div>
          </div>

          <?php if ($is_own_profile): ?>
            <label class="absolute bottom-0 right-0 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-400 transition-all shadow-lg group-hover:scale-110">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <input type="file" name="profile_pic" class="hidden" accept="image/*" onchange="previewImage(this)">
            </label>
          <?php endif; ?>
        </div>

        <!-- Infos utilisateur -->
        <div class="flex-1 text-center md:text-left">
          <div class="flex flex-col md:flex-row items-center gap-3 mb-3">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['username']) ?></h2>
            <span class="gender-badge <?= ($user['gender'] ?? 'male') === 'female' ? 'gender-female' : 'gender-male' ?>">
              <?= ($user['gender'] ?? 'male') === 'female' ? '♀️ ' . __('signup_gender_female', $lang) : '♂️ ' . __('signup_gender_male', $lang) ?>
            </span>
          </div>

          <?php if (!empty($user['bio'])): ?>
            <p class="text-gray-400 mb-4 max-w-md"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
          <?php endif; ?>

          <!-- Statistiques -->
          <div class="flex items-center justify-center md:justify-start gap-4 mb-4">
            <div class="stat-card rounded-xl px-4 py-2 text-center">
              <p class="text-xl font-bold text-white"><?= $followersCount ?></p>
              <p class="text-xs text-gray-500"><?= __('profile_followers', $lang) ?></p>
            </div>
            <div class="stat-card rounded-xl px-4 py-2 text-center">
              <p class="text-xl font-bold text-white"><?= $followingCount ?></p>
              <p class="text-xs text-gray-500"><?= __('profile_following', $lang) ?></p>
            </div>
            <div class="stat-card rounded-xl px-4 py-2 text-center">
              <p class="text-xl font-bold text-white"><?= count($profilePosts) ?></p>
              <p class="text-xs text-gray-500"><?= __('profile_posts', $lang) ?></p>
            </div>
          </div>

          <!-- Actions -->
          <?php if (!$is_own_profile): ?>
            <div class="flex items-center justify-center md:justify-start gap-3">
              <button id="followBtn"
                onclick="toggleFollow(<?= $profile_user_id ?>, <?= $isFollowing ? 'true' : 'false' ?>)" )
                class="px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all
                             <?= $isFollowing ? 'btn-following' : 'btn-follow text-white' ?>">
                <?php if ($isFollowing): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span id="followBtnText"><?= __('profile_unfollow', $lang) ?></span>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span id="followBtnText"><?= __('profile_follow', $lang) ?></span>
                <?php endif; ?>
              </button>

              <a href="conversation.php?user=<?= $profile_user_id ?>"
                class="glass-card px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-white/10 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <?= __('profile_send_message', $lang) ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Formulaire d'édition (uniquement pour son propre profil) -->
    <?php if ($is_own_profile): ?>
      <form method="POST" enctype="multipart/form-data" class="space-y-4 animate-fade-in animate-delay-2">
        <input type="hidden" name="old_pic" value="<?= htmlspecialchars($user['profile_pic'] ?? '') ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Pseudo -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              <?= __('profile_pseudo', $lang) ?>
            </label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
              class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>

          <!-- Email -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              <?= __('profile_email', $lang) ?>
            </label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
              class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>

          <!-- Genre -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              <?= __('profile_gender', $lang) ?>
            </label>
            <select name="gender" required class="glass-input w-full rounded-lg p-3 text-white focus:outline-none bg-transparent">
              <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>
                ♂️ <?= __('signup_gender_male', $lang) ?>
              </option>
              <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>
                ♀️ <?= __('signup_gender_female', $lang) ?>
              </option>
            </select>
          </div>

          <!-- Âge -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              <?= __('profile_age', $lang) ?>
            </label>
            <input type="number" name="age" value="<?= $user['age'] ?? '' ?>"
              placeholder="25" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>

          <!-- Taille -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              <?= __('profile_height', $lang) ?>
            </label>
            <input type="number" name="height" value="<?= $user['height'] ?? '' ?>"
              placeholder="180" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>

          <!-- Poids actuel -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-blue-400 mb-2">
              <?= __('profile_current_weight', $lang) ?>
            </label>
            <input type="number" step="0.1" name="current_weight" value="<?= $user['current_weight'] ?? '' ?>"
              placeholder="75.5" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>

          <!-- Objectif -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-purple-400 mb-2">
              <?= __('profile_goal_weight', $lang) ?>
            </label>
            <input type="number" step="0.1" name="goal_weight" value="<?= $user['goal_weight'] ?? '' ?>"
              placeholder="70.0" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
          </div>
        </div>

        <!-- Bio -->
        <div class="glass-card rounded-xl p-5">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
            <?= __('profile_bio', $lang) ?>
          </label>
          <textarea name="bio" rows="3" placeholder="<?= __('profile_bio_placeholder', $lang) ?>"
            class="glass-input w-full rounded-lg p-3 text-white focus:outline-none resize-none"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <!-- Bouton -->
        <button type="submit"
          class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 
                       text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] 
                       shadow-lg shadow-blue-500/25">
          <?= __('profile_save', $lang) ?>
        </button>
      </form>
    <?php else: ?>
      <!-- Vue publique du profil -->
      <div class="glass-card rounded-xl p-6 mb-6 animate-fade-in animate-delay-2">
        <p class="text-gray-400 text-sm mb-4">
          📅 <?= __('profile_member_since', $lang) ?> <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?>
        </p>

        <div class="grid grid-cols-2 gap-4">
          <?php if ($user['age']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-white"><?= $user['age'] ?></p>
              <p class="text-xs text-gray-500"><?= __('profile_age', $lang) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($user['height']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-white"><?= $user['height'] ?> <span class="text-sm">cm</span></p>
              <p class="text-xs text-gray-500"><?= __('profile_height', $lang) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($user['current_weight']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-blue-400"><?= $user['current_weight'] ?> <span class="text-sm">kg</span></p>
              <p class="text-xs text-gray-500"><?= __('profile_current_weight', $lang) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($user['goal_weight']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-purple-400"><?= $user['goal_weight'] ?> <span class="text-sm">kg</span></p>
              <p class="text-xs text-gray-500"><?= __('profile_goal_weight', $lang) ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Posts de l'utilisateur -->
    <?php if (!empty($profilePosts)): ?>
      <div class="mt-8 animate-fade-in animate-delay-3">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold flex items-center gap-2">
            <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
              <?= __('profile_posts', $lang) ?>
            </span>
            <span class="text-gray-500 text-sm">(<?= count($profilePosts) ?>)</span>
          </h2>
        </div>

        <div class="post-grid">
          <?php foreach ($profilePosts as $post): ?>
            <div class="post-item" onclick="openPostModal(<?= $post['id'] ?>)">
              <img src="../assets/images/<?= htmlspecialchars($post['image_url']) ?>?v=2"
                alt="Post"
                loading="lazy"
                onerror="this.src='../assets/images/default_avatar.png'">
              <div class="post-overlay">
                <span class="flex items-center gap-1 text-white font-bold">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                  <?= $post['likes_count'] ?>
                </span>
                <span class="flex items-center gap-1 text-white font-bold">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  <?= $post['comments_count'] ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php elseif (!$is_own_profile): ?>
      <div class="glass-card rounded-xl p-8 text-center text-gray-500 animate-fade-in animate-delay-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p><?= __('profile_no_posts', $lang) ?></p>
      </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="mt-16 pb-6 text-center">
      <p class="text-gray-600 text-xs uppercase tracking-[0.3em] font-bold">
        <span class="text-[#3b82f6]">AGRE</span> FITNESS
      </p>
    </footer>
  </main>

  <script>
    // Prévisualisation de l'image de profil
    function previewImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Système de Follow/Unfollow via AJAX
    async function toggleFollow(userId, isCurrentlyFollowing) {
      const btn = document.getElementById('followBtn');
      const btnText = document.getElementById('followBtnText');

      // Désactiver le bouton pendant la requête
      btn.disabled = true;
      btn.style.opacity = '0.7';

      try {
        const response = await fetch('follow_process.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `following_id=${userId}&action=${isCurrentlyFollowing ? 'unfollow' : 'follow'}`
        });

        const data = await response.json();

        if (data.success) {
          // Mettre à jour l'interface
          const newIsFollowing = !isCurrentlyFollowing;

          if (newIsFollowing) {
            btn.className = 'px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all btn-following';
            btnText.textContent = '<?= __('profile_unfollow', $lang) ?>';
            btn.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span id="followBtnText"><?= __('profile_unfollow', $lang) ?></span>
            `;
          } else {
            btn.className = 'px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all btn-follow text-white';
            btn.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span id="followBtnText"><?= __('profile_follow', $lang) ?></span>
            `;
          }

          // Mettre à jour l'attribut onclick
          btn.setAttribute('onclick', `toggleFollow(${userId}, ${newIsFollowing})`);

          // Mettre à jour le compteur de followers
          location.reload();
        } else {
          alert(data.error || 'Une erreur est survenue');
        }
      } catch (err) {
        console.error('Erreur follow:', err);
        alert('Erreur de connexion');
      } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
      }
    }

    // Ouvrir un post en modal (redirection vers le feed)
    function openPostModal(postId) {
      window.location.href = 'feed.php?post=' + postId;
    }
  </script>
</body>

</html>