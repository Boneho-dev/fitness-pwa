<?php

/**
 * =============================================================================
 * FEED V2.0 - AGRE FITNESS
 * =============================================================================
 * Fil d'actualité social avec système multilingue.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

// Initialisation de la langue
initLanguage();

// Sécurité : vérification de la session
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$lang = getCurrentLang();

// Récupération des infos utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM fitness_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$error = '';
$success = '';

// --- LOGIQUE : CRÉATION D'UN NOUVEAU POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
  $caption = trim($_POST['caption'] ?? '');
  $image = $_FILES['post_image'] ?? null;

  if ($image && $image['error'] === UPLOAD_ERR_OK) {
    // Validation du type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($image['tmp_name']);

    if (in_array($fileType, $allowedTypes)) {
      // Génération d'un nom unique
      $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
      $filename = 'post_' . $_SESSION['user_id'] . '_' . time() . '_' . uniqid() . '.' . $extension;
      $uploadPath = '../assets/images/posts/' . $filename;
      $dbPath = 'posts/' . $filename;

      // Déplacement du fichier
      if (move_uploaded_file($image['tmp_name'], $uploadPath)) {
        // Insertion en base de données
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, image_url, caption) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $dbPath, $caption]);
        $success = "Votre post a été publié avec succès !";
      } else {
        $error = "Erreur lors de l'upload de l'image.";
      }
    } else {
      $error = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WEBP.";
    }
  } else {
    $error = "Veuillez sélectionner une image pour votre post.";
  }
}

// --- RÉCUPÉRATION DES POSTS V1.1 ---
$currentUserId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
  SELECT
    p.id,
    p.image_url,
    p.caption,
    p.created_at,
    p.user_id,
    u.username,
    u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
    (SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked,
    CASE WHEN p.user_id = ? THEN 1 ELSE 0 END as is_own_post
  FROM posts p
  INNER JOIN fitness_users u ON p.user_id = u.id
  ORDER BY p.created_at DESC
");
$stmt->execute([$currentUserId, $currentUserId]);
$posts = $stmt->fetchAll();

/**
 * V1.1 - Fonction get_time_ago améliorée
 * Calcule le temps relatif réel depuis le timestamp
 */
function get_time_ago($datetime)
{
  if (empty($datetime)) return 'Date inconnue';

  $time = strtotime($datetime);
  $now = time();
  $diff = $now - $time;

  if ($diff < 0) return "À l'instant";
  if ($diff < 60) return "À l'instant";
  if ($diff < 3600) {
    $mins = floor($diff / 60);
    return $mins . ' min' . ($mins > 1 ? 's' : '');
  }
  if ($diff < 86400) {
    $hours = floor($diff / 3600);
    return $hours . ' h';
  }
  if ($diff < 604800) {
    $days = floor($diff / 86400);
    return $days . ' j';
  }
  if ($diff < 2592000) {
    $weeks = floor($diff / 604800);
    return $weeks . ' sem';
  }
  return date('d M Y', $time);
}

$profilePic = !empty($currentUser['profile_pic']) ? $currentUser['profile_pic'] : 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fil d'actualité | AGRE Fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slow-glow {

      0%,
      100% {
        opacity: 0.4;
        transform: scale(1);
      }

      50% {
        opacity: 0.7;
        transform: scale(1.1);
      }
    }

    .animate-glow {
      animation: slow-glow 8s infinite ease-in-out;
    }

    .glass-card {
      background: rgba(17, 24, 39, 0.7);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .glass-input {
      background: rgba(31, 41, 55, 0.6);
      backdrop-filter: blur(4px);
    }

    .hide-scrollbar::-webkit-scrollbar {
      display: none;
    }

    .hide-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .image-preview {
      transition: all 0.3s ease;
    }

    /* V1.1 : Menu dropdown styles */
    [id^="menu-"] {
      animation: fadeInMenu 0.2s ease;
    }

    @keyframes fadeInMenu {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Modal d'édition */
    .edit-modal {
      background: rgba(0, 0, 0, 0.9);
      backdrop-filter: blur(10px);
    }
  </style>
</head>

<body class="bg-black text-white min-h-screen font-sans selection:bg-blue-500 selection:text-white">

  <?php require_once '../includes/navbar.php'; ?>

  <!-- Bouton Retour -->
  <div class="max-w-2xl mx-auto px-4 md:px-6 pt-4">
    <a href="dashboard.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-all group">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      <span class="text-sm font-bold">Retour au Dashboard</span>
    </a>
  </div>

  <main class="p-4 md:p-6 max-w-2xl mx-auto pt-4">

    <!-- Header -->
    <header class="mb-8 text-center">
      <h2 class="text-3xl font-black tracking-tight">
        <span class="text-purple-400">FIL</span> D'ACTUALITÉ
      </h2>
      <p class="text-gray-500 text-sm mt-2">Partagez vos exploits et inspirez la communauté</p>
    </header>

    <!-- Alertes -->
    <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-2xl mb-6 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire Nouveau Post -->
    <section class="glass-card rounded-3xl border border-gray-800 p-6 mb-8 hover:border-blue-500/30 transition-all">
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="create_post" value="1">

        <div class="flex gap-4">
          <div class="w-12 h-12 rounded-full border-2 border-blue-500 overflow-hidden flex-shrink-0">
            <img src="../assets/images/<?= htmlspecialchars($profilePic) ?>"
              class="w-full h-full object-cover"
              onerror="this.src='../assets/images/default-avatar.png';">
          </div>

          <div class="flex-1">
            <textarea
              name="caption"
              rows="2"
              placeholder="Partagez votre séance... #LegDay #Progress"
              class="w-full glass-input rounded-2xl p-4 text-white placeholder-gray-500 border border-gray-700 focus:border-blue-500 focus:outline-none resize-none transition-all"></textarea>
          </div>
        </div>

        <!-- Aperçu de l'image -->
        <div id="imagePreview" class="hidden rounded-2xl overflow-hidden border border-gray-700 image-preview">
          <img id="previewImg" src="" alt="Aperçu" class="w-full h-64 object-cover">
        </div>

        <div class="flex items-center justify-between pt-2">
          <label class="flex items-center gap-2 text-gray-400 hover:text-blue-400 cursor-pointer transition-colors group">
            <div class="p-2 rounded-xl bg-gray-800/50 group-hover:bg-blue-500/20 transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <span class="text-sm font-medium">Ajouter une photo</span>
            <input type="file" name="post_image" accept="image/*" class="hidden" id="postImage" required>
          </label>

          <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-3 rounded-xl transition-all transform hover:scale-105 flex items-center gap-2">
            <span>Publier</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </form>
    </section>

    <!-- Feed des Posts -->
    <div class="space-y-6">
      <?php if (empty($posts)): ?>
        <div class="text-center py-16 glass-card rounded-3xl border border-gray-800">
          <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-800/50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <p class="text-gray-500 text-lg font-medium">Aucun post encore</p>
          <p class="text-gray-600 text-sm mt-2">Soyez le premier à partager votre séance !</p>
        </div>
      <?php else: ?>
        <?php foreach ($posts as $post):
          $postUserPic = !empty($post['profile_pic']) ? $post['profile_pic'] : 'default-avatar.png';
        ?>
          <article class="glass-card rounded-3xl border border-gray-800 overflow-hidden hover:border-gray-700 transition-all">
            <!-- Header du post -->
            <div class="p-4 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <a href="profile.php?id=<?= $post['user_id'] ?>" class="w-10 h-10 rounded-full border border-gray-700 overflow-hidden hover:border-blue-500 transition-all">
                  <img src="../assets/images/<?= htmlspecialchars($postUserPic) ?>"
                    class="w-full h-full object-cover"
                    onerror="this.src='../assets/images/default-avatar.png';">
                </a>
                <div>
                  <a href="profile.php?id=<?= $post['user_id'] ?>" class="font-bold text-sm hover:text-blue-400 transition-colors">
                    <?= htmlspecialchars($post['username']) ?>
                  </a>
                  <!-- V1.1 : Horodatage réel -->
                  <p class="text-gray-500 text-xs" title="<?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>">
                    <?= get_time_ago($post['created_at']) ?>
                  </p>
                </div>
              </div>

              <!-- V1.1 : Menu contextuel conditionnel -->
              <div class="relative">
                <button onclick="toggleMenu(<?= $post['id'] ?>)" class="text-gray-500 hover:text-white p-2 rounded-full hover:bg-gray-800 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                  </svg>
                </button>

                <!-- Menu déroulant -->
                <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-2 w-48 glass-card rounded-xl border border-gray-700 shadow-xl z-20 py-2">
                  <?php if ($post['is_own_post']): ?>
                    <!-- Options pour l'auteur -->
                    <button onclick="editPost(<?= $post['id'] ?>, <?= json_encode($post['caption'], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>)"
                      class="w-full text-left px-4 py-3 hover:bg-gray-800 flex items-center gap-2 text-sm text-blue-400 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Modifier
                    </button>
                    <button onclick="deletePost(<?= $post['id'] ?>)"
                      class="w-full text-left px-4 py-3 hover:bg-gray-800 flex items-center gap-2 text-sm text-red-400 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Supprimer
                    </button>
                  <?php else: ?>
                    <!-- Option pour les autres -->
                    <button onclick="reportPost(<?= $post['id'] ?>)"
                      class="w-full text-left px-4 py-3 hover:bg-gray-800 flex items-center gap-2 text-sm text-yellow-400 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                      Signaler
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Image du post -->
            <div class="relative aspect-[4/3] bg-black flex items-center justify-center">
              <img src="../assets/images/<?= htmlspecialchars($post['image_url']) ?>?v=2"
                alt="Post de <?= htmlspecialchars($post['username']) ?>"
                class="w-full h-full object-contain"
                onerror="this.src='../assets/images/default-avatar.png';">
            </div>

            <!-- Actions -->
            <div class="p-4">
              <div class="flex items-center gap-4 mb-3">
                <!-- V1.1 : Bouton Like fonctionnel avec AJAX -->
                <div class="flex items-center gap-1">
                  <button onclick="toggleLike(<?= $post['id'] ?>, this)"
                    class="flex items-center group like-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                    data-post-id="<?= $post['id'] ?>">
                    <div class="p-2 rounded-full group-hover:bg-red-500/20 transition-all">
                      <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 transition-colors like-icon <?= $post['user_liked'] ? 'text-red-500 fill-current' : 'text-gray-400 group-hover:text-red-500' ?>"
                        fill="<?= $post['user_liked'] ? 'currentColor' : 'none' ?>"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </div>
                  </button>
                  <button onclick="showLikers(<?= $post['id'] ?>)"
                    class="text-sm font-medium text-gray-400 hover:text-white transition-colors likes-count"><?= $post['likes_count'] ?></button>
                </div>

                <!-- V1.1 : Bouton Commentaire fonctionnel -->
                <button onclick="toggleCommentForm(<?= $post['id'] ?>)"
                  class="flex items-center gap-2 group">
                  <div class="p-2 rounded-full group-hover:bg-blue-500/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-400 comments-count-<?= $post['id'] ?>"><?= $post['comments_count'] ?></span>
                </button>

                <!-- Bouton Partager -->
                <button class="flex items-center gap-2 group ml-auto">
                  <div class="p-2 rounded-full group-hover:bg-green-500/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-green-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                  </div>
                </button>
              </div>

              <!-- V1.1 : Section commentaires cachée par défaut -->
              <div id="comment-section-<?= $post['id'] ?>" class="hidden border-t border-gray-800 pt-3 mt-3">
                <form onsubmit="submitComment(event, <?= $post['id'] ?>)" class="flex gap-2 mb-3">
                  <input type="text" name="content" placeholder="Ajouter un commentaire..." required
                    class="flex-1 bg-gray-800 border border-gray-700 rounded-full px-4 py-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                  <button type="submit" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-full text-sm font-bold">
                    Envoyer
                  </button>
                </form>
                <div id="comments-list-<?= $post['id'] ?>" class="space-y-2 max-h-40 overflow-y-auto">
                  <!-- Les commentaires seront chargés ici -->
                </div>
              </div>

              <!-- Légende -->
              <?php if (!empty($post['caption'])): ?>
                <div class="space-y-1">
                  <p class="text-sm">
                    <a href="profile.php?id=<?= $post['user_id'] ?>" class="font-bold hover:text-blue-400 transition-colors">
                      <?= htmlspecialchars($post['username']) ?>
                    </a>
                    <span class="text-gray-300"><?= nl2br(htmlspecialchars($post['caption'])) ?></span>
                  </p>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <footer class="mt-16 pb-10 text-center">
      <p class="text-gray-600 text-xs uppercase tracking-[0.3em] font-bold">
        <span class="text-[#3b82f6]">FITNESS</span> TRACKER • Copyright Agre Agency 2026
      </p>
    </footer>
  </main>

  <script>
    // Aperçu de l'image avant upload
    document.getElementById('postImage').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      }
    });

    // V1.1 - Gestion du menu contextuel
    function toggleMenu(postId) {
      document.querySelectorAll('[id^="menu-"]').forEach(menu => {
        if (menu.id !== `menu-${postId}`) menu.classList.add('hidden');
      });
      const menu = document.getElementById(`menu-${postId}`);
      menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => menu.classList.add('hidden'));
      }
    });

    // V1.1 - Système de Like AJAX
    async function toggleLike(postId, btnElement) {
      try {
        const response = await fetch('like_process.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}`
        });

        const data = await response.json();
        if (data.success) {
          btnElement.closest('.flex.items-center.gap-1').querySelector('.likes-count').textContent = data.likes_count;
          const icon = btnElement.querySelector('.like-icon');

          if (data.liked) {
            icon.style.fill = 'currentColor';
            icon.classList.add('text-red-500', 'fill-current');
            icon.classList.remove('text-gray-400');
          } else {
            icon.style.fill = 'none';
            icon.classList.remove('text-red-500', 'fill-current');
            icon.classList.add('text-gray-400');
          }
        }
      } catch (err) {
        console.error('Erreur like:', err);
      }
    }

    // V1.1 - Système de Commentaire
    function toggleCommentForm(postId) {
      const form = document.getElementById(`comment-section-${postId}`);
      form.classList.toggle('hidden');
    }

    async function submitComment(event, postId) {
      event.preventDefault();
      const form = event.target;
      const content = form.content.value;

      try {
        const response = await fetch('comment_process.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}&content=${encodeURIComponent(content)}`
        });

        const data = await response.json();
        if (data.success) {
          document.querySelector(`.comments-count-${postId}`).textContent = data.comments_count;

          // Ajouter le commentaire au DOM
          const commentsList = document.getElementById(`comments-list-${postId}`);
          const newComment = document.createElement('div');
          newComment.className = 'flex gap-2 items-start';
          newComment.innerHTML = `
            <img src="../assets/images/${data.profile_pic}" class="w-8 h-8 rounded-full object-cover">
            <div class="bg-gray-800 rounded-lg px-3 py-2 flex-1">
              <p class="text-xs font-bold text-blue-400">${data.username}</p>
              <p class="text-sm text-gray-300">${data.content}</p>
            </div>
          `;
          commentsList.prepend(newComment);
          form.reset();
        }
      } catch (err) {
        console.error('Erreur commentaire:', err);
      }
    }

    // V1.1 - Suppression de post
    async function deletePost(postId) {
      if (!confirm('Supprimer ce post ? Cette action est irréversible.')) return;

      try {
        const response = await fetch('delete_post.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}`
        });

        const data = await response.json();
        if (data.success) {
          const article = document.querySelector(`button[onclick="toggleMenu(${postId})"]`).closest('article');
          article.style.opacity = '0';
          article.style.transform = 'translateY(-20px)';
          setTimeout(() => article.remove(), 300);
        } else {
          alert(data.error || 'Erreur lors de la suppression');
        }
      } catch (err) {
        console.error('Erreur:', err);
      }
    }

    // V1.1 - Modification de post
    function editPost(postId, currentCaption) {
      const modal = document.createElement('div');
      modal.className = 'edit-modal fixed inset-0 z-50 flex items-center justify-center p-4';
      modal.innerHTML = `
        <div class="glass-card rounded-2xl p-6 w-full max-w-md border border-gray-700">
          <h3 class="text-xl font-bold mb-4">Modifier le post</h3>
          <form onsubmit="submitEdit(event, ${postId}, this); return false;">
            <textarea name="caption" rows="4" 
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 text-white focus:border-blue-500 focus:outline-none resize-none mb-4">${currentCaption}</textarea>
            <div class="flex gap-3 justify-end">
              <button type="button" onclick="this.closest('.edit-modal').remove()" 
                      class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-white font-medium">Annuler</button>
              <button type="submit" class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-400 text-white font-bold">Enregistrer</button>
            </div>
          </form>
        </div>
      `;
      document.body.appendChild(modal);
      document.body.style.overflow = 'hidden';
    }

    async function submitEdit(event, postId, form) {
      event.preventDefault();
      const caption = form.caption.value;

      try {
        // Debug: afficher l'URL utilisée
        console.log('Envoi vers: update_post.php');
        console.log('Données:', {
          post_id: postId,
          caption: caption
        });

        const response = await fetch('update_post.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}&caption=${encodeURIComponent(caption)}`
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Réponse:', data);

        if (data.success) {
          window.location.reload(); // Rechargement pour simplifier
        } else {
          alert(data.error || 'Erreur lors de la modification');
        }
      } catch (err) {
        console.error('Erreur complète:', err);
        alert('Erreur réseau: ' + err.message);
      }
    }

    // V1.1 - Signalement
    function reportPost(postId) {
      const reason = prompt('Pourquoi signalez-vous ce post ?');
      if (!reason) return;
      alert('Signalement envoyé. Merci !');
    }

    // V1.1 - Qui a liké ?
    async function showLikers(postId) {
      const countEl = document.querySelector(`button[onclick="showLikers(${postId})"]`);
      const count = parseInt(countEl.textContent.trim());
      if (count === 0) return;

      try {
        const response = await fetch(`get_likes.php?post_id=${postId}`);
        const data = await response.json();
        if (!data.success || !data.likers.length) return;

        const modal = document.createElement('div');
        modal.className = 'edit-modal fixed inset-0 z-50 flex items-center justify-center p-4';
        modal.onclick = function(e) {
          if (e.target === modal) modal.remove();
        };

        const likerRows = data.likers.map(u => `
          <div class="flex items-center gap-3 py-2 border-b border-gray-800 last:border-0">
            <img src="../assets/images/${u.profile_pic || 'default-avatar.png'}"
                 class="w-9 h-9 rounded-full object-cover border border-gray-700"
                 onerror="this.src='../assets/images/default-avatar.png'">
            <a href="profile.php?id=${u.id}" class="text-sm font-semibold hover:text-blue-400 transition-colors">${u.username}</a>
          </div>
        `).join('');

        modal.innerHTML = `
          <div class="glass-card rounded-2xl p-5 w-full max-w-sm border border-gray-700 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 fill-current" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                ${data.count} j'aime${data.count > 1 ? 's' : ''}
              </h3>
              <button onclick="this.closest('.edit-modal').remove()" class="text-gray-500 hover:text-white p-1 rounded-full hover:bg-gray-800 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="max-h-64 overflow-y-auto">${likerRows}</div>
          </div>
        `;
        document.body.appendChild(modal);
      } catch (err) {
        console.error('Erreur likers:', err);
      }
    }
  </script>

</body>

</html>