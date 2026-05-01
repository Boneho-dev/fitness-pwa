<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

initLanguage();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$current_user_id  = (int)$_SESSION['user_id'];
$profile_user_id  = isset($_GET['id']) ? intval($_GET['id']) : $current_user_id;
$is_own_profile   = ($profile_user_id === $current_user_id);
$message          = "";

// =============================================================================
// MISE À JOUR DU PROFIL
// =============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
  $username       = trim($_POST['username'] ?? '');
  $email          = trim($_POST['email'] ?? '');
  $gender         = $_POST['gender'] ?? 'male';
  $age            = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT) ?: null;
  $current_weight = filter_input(INPUT_POST, 'current_weight', FILTER_VALIDATE_FLOAT) ?: null;
  $goal_weight    = filter_input(INPUT_POST, 'goal_weight', FILTER_VALIDATE_FLOAT) ?: null;
  $height         = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_INT) ?: null;
  $bio            = trim($_POST['bio'] ?? '');

  // Garde l'ancienne valeur DB par défaut — ne jamais écraser avec une chaîne vide
  $profile_pic = !empty($_POST['old_pic']) ? $_POST['old_pic'] : null;

  // --- Upload photo de profil ---
  if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type     = mime_content_type($_FILES['profile_pic']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
      $message = "Format non supporté. Utilisez JPG, PNG, GIF ou WEBP.";
    } else {
      // Nom fixe : écrase toujours le même fichier → pas d'accumulation
      $file_name  = "profile_" . $current_user_id . ".jpeg";
      $upload_dir = __DIR__ . "/../assets/images/";
      $dest       = $upload_dir . $file_name;

      // Tente chmod si le volume Railway n'est pas encore writable
      if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0777);
      }

      if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
        $profile_pic = $file_name;
      } else {
        $php_err = $_FILES['profile_pic']['error'];
        error_log("[upload] FAILED → dest={$dest} | writable=" . (is_writable($upload_dir) ? 'yes' : 'no') . " | php_error={$php_err} | tmp=" . $_FILES['profile_pic']['tmp_name']);
        $message = "Échec de l'enregistrement. Vérifiez les permissions du dossier assets/images/.";
      }
    }
  }

  // --- Mise à jour DB uniquement si pas d'erreur d'upload ---
  if (empty($message)) {
    try {
      $stmt = $pdo->prepare("
        UPDATE fitness_users
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
        WHERE id = :id
      ");
      $stmt->execute([
        ':username'       => $username,
        ':email'          => $email,
        ':gender'         => $gender,
        ':age'            => $age,
        ':height'         => $height,
        ':current_weight' => $current_weight,
        ':goal_weight'    => $goal_weight,
        ':bio'            => $bio,
        ':profile_pic'    => $profile_pic,
        ':id'             => $current_user_id
      ]);

      $_SESSION['username']            = $username;
      $_SESSION['profile_pic']         = $profile_pic;
      $_SESSION['user']['profile_pic'] = $profile_pic;

      // Redirect PRG : recharge la page en GET → déclenche le cache-bust time()
      header('Location: profile.php?saved=1');
      exit;
    } catch (PDOException $e) {
      $message = "Erreur base de données lors de la mise à jour.";
      error_log("Erreur profil update: " . $e->getMessage());
    }
  }
}

// Message de succès après redirect PRG
if (isset($_GET['saved'])) {
  $message = 'Profil mis à jour avec succès ! 🎉';
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

$_profImgDir = __DIR__ . '/../assets/images/';
if (!empty($user['profile_pic']) && file_exists($_profImgDir . $user['profile_pic'])) {
  $profilePic = $user['profile_pic'];
} elseif (file_exists($_profImgDir . 'profile_' . $profile_user_id . '.jpeg')) {
  $profilePic = 'profile_' . $profile_user_id . '.jpeg';
} elseif (file_exists($_profImgDir . 'profile_' . $profile_user_id . '.jpg')) {
  $profilePic = 'profile_' . $profile_user_id . '.jpg';
} else {
  $profilePic = 'default-avatar.png';
}

// =============================================================================
// STATISTIQUES FOLLOWERS/FOLLOWING
// =============================================================================

try {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE following_id = :id");
  $stmt->execute([':id' => $profile_user_id]);
  $followersCount = (int)$stmt->fetchColumn();

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = :id");
  $stmt->execute([':id' => $profile_user_id]);
  $followingCount = (int)$stmt->fetchColumn();

  $isFollowing = false;
  if (!$is_own_profile) {
    $stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id = :follower AND following_id = :following");
    $stmt->execute([':follower' => $current_user_id, ':following' => $profile_user_id]);
    $isFollowing = (bool)$stmt->fetch();
  }
} catch (PDOException $e) {
  error_log("Erreur statistiques follow: " . $e->getMessage());
  $followersCount = 0;
  $followingCount = 0;
  $isFollowing    = false;
}

// =============================================================================
// RÉCUPÉRATION DES POSTS
// =============================================================================

try {
  $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.image_url,
            p.caption,
            p.created_at,
            (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) as likes_count,
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

function formatRelativeTime(string $datetime): string
{
  $time = strtotime($datetime);
  $diff = time() - $time;

  if ($diff < 86400) return "Aujourd'hui";
  if ($diff < 604800) {
    $days = floor($diff / 86400);
    return $days . ' jour' . ($days > 1 ? 's' : '');
  }
  return date('d M Y', $time);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title><?= $is_own_profile ? 'Mon Profil | AGRE Fitness' : htmlspecialchars($user['username']) . ' | AGRE Fitness' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
    }

    body {
      background: linear-gradient(180deg, #050505 0%, #0a0a0a 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

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

    .stat-card {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      background: rgba(255, 255, 255, 0.05);
      transform: translateY(-2px);
    }

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

    <!-- Header -->
    <header class="text-center mb-8 animate-fade-in">
      <h1 class="text-3xl font-black tracking-tight mb-2">
        <?php if ($is_own_profile): ?>
          <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
            Mon Profil
          </span>
        <?php else: ?>
          <span class="text-gray-400">Profil de </span>
          <span class="text-white"><?= htmlspecialchars($user['username']) ?></span>
        <?php endif; ?>
      </h1>
      <p class="text-gray-500 text-sm">
        <?= $is_own_profile ? 'Gérez vos informations personnelles' : 'Membre AGRE Fitness' ?>
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

    <?php if ($is_own_profile): ?>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="old_pic" value="<?= htmlspecialchars($user['profile_pic'] ?? '') ?>">
      <?php endif; ?>

      <!-- Section Profil Header -->
      <div class="glass-card rounded-3xl p-6 mb-6 animate-fade-in animate-delay-1">
        <div class="flex flex-col md:flex-row items-center gap-6">

          <!-- Photo de profil -->
          <div class="relative group">
            <div class="avatar-ring">
              <div class="w-28 h-28 rounded-full overflow-hidden bg-gray-800 border-4 border-[#111]">
                <img id="preview"
                  src="../assets/images/<?= htmlspecialchars($profilePic) ?>?v=<?= time() ?>"
                  class="w-full h-full object-cover"
                  alt="Photo de profil"
                  onerror="this.src='../assets/images/default-avatar.png'">
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
                <?= ($user['gender'] ?? 'male') === 'female' ? '♀️ Femme' : '♂️ Homme' ?>
              </span>
            </div>

            <?php if (!empty($user['bio'])): ?>
              <p class="text-gray-400 mb-4 max-w-md"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="flex items-center justify-center md:justify-start gap-4 mb-4">
              <div class="stat-card rounded-xl px-4 py-2 text-center">
                <p class="text-xl font-bold text-white"><?= $followersCount ?></p>
                <p class="text-xs text-gray-500">Abonnés</p>
              </div>
              <div class="stat-card rounded-xl px-4 py-2 text-center">
                <p class="text-xl font-bold text-white"><?= $followingCount ?></p>
                <p class="text-xs text-gray-500">Abonnements</p>
              </div>
              <div class="stat-card rounded-xl px-4 py-2 text-center">
                <p class="text-xl font-bold text-white"><?= count($profilePosts) ?></p>
                <p class="text-xs text-gray-500">Publications</p>
              </div>
            </div>

            <!-- Actions (profil autre) -->
            <?php if (!$is_own_profile): ?>
              <div class="flex items-center justify-center md:justify-start gap-3">
                <button id="followBtn"
                  onclick="toggleFollow(<?= $profile_user_id ?>, <?= $isFollowing ? 'true' : 'false' ?>)"
                  class="px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all
                             <?= $isFollowing ? 'btn-following' : 'btn-follow text-white' ?>">
                  <?php if ($isFollowing): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span id="followBtnText">Ne plus suivre</span>
                  <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span id="followBtnText">Suivre</span>
                  <?php endif; ?>
                </button>

                <a href="conversation.php?user=<?= $profile_user_id ?>"
                  class="glass-card px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-white/10 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  Envoyer un message
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Champs d'édition (son propre profil) -->
      <?php if ($is_own_profile): ?>
        <div class="space-y-4 animate-fade-in animate-delay-2">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Pseudo -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Pseudo</label>
              <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>

            <!-- Email -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
                class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>

            <!-- Genre -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Genre</label>
              <select name="gender" required class="glass-input w-full rounded-lg p-3 text-white focus:outline-none bg-transparent">
                <option value="male" <?= ($user['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>♂️ Homme</option>
                <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>♀️ Femme</option>
              </select>
            </div>

            <!-- Âge -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Âge</label>
              <input type="number" name="age" value="<?= $user['age'] ?? '' ?>"
                placeholder="25" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>

            <!-- Taille -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Taille (cm)</label>
              <input type="number" name="height" value="<?= $user['height'] ?? '' ?>"
                placeholder="180" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>

            <!-- Poids actuel -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-blue-400 mb-2">Poids actuel (kg)</label>
              <input type="number" step="0.1" name="current_weight" value="<?= $user['current_weight'] ?? '' ?>"
                placeholder="75.5" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>

            <!-- Objectif -->
            <div class="glass-card rounded-xl p-5">
              <label class="block text-xs font-bold uppercase tracking-wider text-purple-400 mb-2">Objectif poids (kg)</label>
              <input type="number" step="0.1" name="goal_weight" value="<?= $user['goal_weight'] ?? '' ?>"
                placeholder="70.0" class="glass-input w-full rounded-lg p-3 text-white focus:outline-none">
            </div>
          </div>

          <!-- Bio -->
          <div class="glass-card rounded-xl p-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Bio</label>
            <textarea name="bio" rows="3" placeholder="Décrivez votre parcours..."
              class="glass-input w-full rounded-lg p-3 text-white focus:outline-none resize-none"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <!-- Bouton -->
          <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400
                       text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02]
                       shadow-lg shadow-blue-500/25">
            Enregistrer les modifications
          </button>
        </div>
      </form>

    <?php else: ?>
      <!-- Vue publique -->
      <div class="glass-card rounded-xl p-6 mb-6 animate-fade-in animate-delay-2">
        <p class="text-gray-400 text-sm mb-4">
          📅 Membre depuis <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?>
        </p>

        <div class="grid grid-cols-2 gap-4">
          <?php if ($user['age']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-white"><?= $user['age'] ?></p>
              <p class="text-xs text-gray-500">Âge</p>
            </div>
          <?php endif; ?>

          <?php if ($user['height']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-white"><?= $user['height'] ?> <span class="text-sm">cm</span></p>
              <p class="text-xs text-gray-500">Taille</p>
            </div>
          <?php endif; ?>

          <?php if ($user['current_weight']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-blue-400"><?= $user['current_weight'] ?> <span class="text-sm">kg</span></p>
              <p class="text-xs text-gray-500">Poids actuel</p>
            </div>
          <?php endif; ?>

          <?php if ($user['goal_weight']): ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <p class="text-2xl font-bold text-purple-400"><?= $user['goal_weight'] ?> <span class="text-sm">kg</span></p>
              <p class="text-xs text-gray-500">Objectif</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Posts -->
    <?php if (!empty($profilePosts)): ?>
      <div class="mt-8 animate-fade-in animate-delay-3">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold flex items-center gap-2">
            <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
              Publications
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
                onerror="this.src='../assets/images/default-avatar.png'">
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
        <p>Aucune publication</p>
      </div>
    <?php endif; ?>

    <footer class="mt-16 pb-6 text-center">
      <p class="text-gray-600 text-xs uppercase tracking-[0.3em] font-bold">
        <span class="text-[#3b82f6]">FITNESS</span> TRACKER • Copyright Agre Agency 2026
      </p>
    </footer>
  </main>

  <script>
    function previewImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    async function toggleFollow(userId, isCurrentlyFollowing) {
      const btn = document.getElementById('followBtn');
      const btnText = document.getElementById('followBtnText');

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
          const newIsFollowing = !isCurrentlyFollowing;

          if (newIsFollowing) {
            btn.className = 'px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all btn-following';
            btn.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span id="followBtnText">Ne plus suivre</span>
            `;
          } else {
            btn.className = 'px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all btn-follow text-white';
            btn.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span id="followBtnText">Suivre</span>
            `;
          }

          btn.setAttribute('onclick', `toggleFollow(${userId}, ${newIsFollowing})`);
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

    function openPostModal(postId) {
      window.location.href = 'feed.php?post=' + postId;
    }
  </script>
</body>

</html>