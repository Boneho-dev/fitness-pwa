<?php

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

initLanguage();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$currentUserId = (int)$_SESSION['user_id'];

// =============================================================================
// SUPPRESSION DE CONVERSATION
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contact_id'])) {
  $deleteContactId = (int)$_POST['delete_contact_id'];
  if ($deleteContactId > 0) {
    try {
      $stmt = $pdo->prepare("
        DELETE FROM messages
        WHERE (expediteur_id = ? AND destinataire_id = ?)
           OR (expediteur_id = ? AND destinataire_id = ?)
      ");
      $stmt->execute([$currentUserId, $deleteContactId, $deleteContactId, $currentUserId]);
    } catch (PDOException $e) {
      error_log("Erreur suppression conversation: " . $e->getMessage());
    }
  }
  header('Location: messages.php');
  exit;
}

// =============================================================================
// RÉCUPÉRATION DES CONVERSATIONS
// =============================================================================

try {
  $stmt = $pdo->prepare("
        SELECT
            u.id as contact_id,
            u.username,
            u.profile_pic,
            u.last_active,
            m.contenu as last_message,
            m.date_envoi as last_date,
            m.expediteur_id as last_sender,
            m.media_url as last_media,
            (SELECT COUNT(*) FROM messages
             WHERE destinataire_id = :user_id
             AND expediteur_id = u.id
             AND lu = 0) as unread_count
        FROM fitness_users u
        INNER JOIN messages m ON (m.expediteur_id = u.id AND m.destinataire_id = :user_id2)
                               OR (m.destinataire_id = u.id AND m.expediteur_id = :user_id3)
        WHERE u.id != :user_id4
        AND m.date_envoi = (
            SELECT MAX(date_envoi)
            FROM messages
            WHERE (expediteur_id = u.id AND destinataire_id = :user_id5)
               OR (destinataire_id = u.id AND expediteur_id = :user_id6)
        )
        ORDER BY m.date_envoi DESC
    ");

  $stmt->execute([
    ':user_id'  => $currentUserId,
    ':user_id2' => $currentUserId,
    ':user_id3' => $currentUserId,
    ':user_id4' => $currentUserId,
    ':user_id5' => $currentUserId,
    ':user_id6' => $currentUserId
  ]);
  $conversations = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Erreur récupération conversations: " . $e->getMessage());
  $conversations = [];
}

// =============================================================================
// RÉCUPÉRATION DE TOUS LES UTILISATEURS
// =============================================================================

try {
  $stmt = $pdo->prepare("
        SELECT id, username, profile_pic, last_active
        FROM fitness_users
        WHERE id != :user_id
        ORDER BY username ASC
    ");
  $stmt->execute([':user_id' => $currentUserId]);
  $allUsers = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Erreur récupération utilisateurs: " . $e->getMessage());
  $allUsers = [];
}

function resolveContactAvatar(int $uid, ?string $dbPic): string
{
  $dir = __DIR__ . '/../assets/images/';
  if (!empty($dbPic) && file_exists($dir . $dbPic)) return $dbPic;
  if (file_exists($dir . 'profile_' . $uid . '.jpeg'))  return 'profile_' . $uid . '.jpeg';
  if (file_exists($dir . 'profile_' . $uid . '.jpg'))   return 'profile_' . $uid . '.jpg';
  return 'default-avatar.png';
}

function isUserOnline(?string $lastActive): bool
{
  if (!$lastActive) return false;
  return (time() - strtotime($lastActive)) < 300;
}

function formatMessageTime(string $datetime): string
{
  $time = strtotime($datetime);
  $diff = time() - $time;

  if ($diff < 60)    return 'À l\'instant';
  if ($diff < 3600)  return floor($diff / 60) . ' min';
  if ($diff < 86400) return floor($diff / 3600) . ' h';
  if ($diff < 604800) return floor($diff / 86400) . ' j';
  return date('d/m', $time);
}

function truncateMessage(?string $message, int $maxLength = 50): string
{
  if (!$message) return '';
  if (strlen($message) <= $maxLength) return $message;
  return substr($message, 0, $maxLength) . '...';
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
  <title>Messages | AGRE Fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #050505 0%, #0a0a0a 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      min-height: 100vh;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(59, 130, 246, 0.3);
      transform: translateY(-2px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .message-preview {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(59, 130, 246, 0.02) 100%);
      border-left: 3px solid rgba(59, 130, 246, 0.5);
    }

    .unread-badge {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .online-indicator {
      position: absolute;
      bottom: 2px;
      right: 2px;
      width: 14px;
      height: 14px;
      background: #10b981;
      border: 3px solid #111;
      border-radius: 50%;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    }

    .online-indicator::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      background: rgba(16, 185, 129, 0.2);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.5); opacity: 0; }
    }

    .avatar-container {
      position: relative;
      display: inline-block;
    }

    .search-input {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .search-input:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(59, 130, 246, 0.5);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-5px); }
    }

    .animate-float { animation: float 3s ease-in-out infinite; }

    .empty-state-icon {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .member-card {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }

    .member-card:hover {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(59, 130, 246, 0.3);
      transform: scale(1.05);
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
  </style>
</head>

<body class="text-white min-h-screen">
  <?php require_once '../includes/navbar.php'; ?>

  <div class="max-w-4xl mx-auto p-4 pt-24 pb-8">
    <!-- Header -->
    <header class="mb-8 animate-float">
      <h1 class="text-3xl font-black tracking-tight mb-2">
        <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
          Messages
        </span>
      </h1>
      <p class="text-gray-500 text-sm">Vos conversations et contacts</p>
    </header>

    <!-- Barre de recherche -->
    <div class="mb-6">
      <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input id="searchInput"
          type="text"
          placeholder="Rechercher un contact..."
          oninput="filterContacts(this.value)"
          class="search-input w-full rounded-2xl pl-12 pr-4 py-4 text-white placeholder-gray-500 focus:outline-none">
      </div>
    </div>

    <!-- Liste des conversations -->
    <?php if (!empty($conversations)): ?>
      <div id="contactsList" class="space-y-3">
        <?php foreach ($conversations as $conv):
          $isLastFromMe = ($conv['last_sender'] == $currentUserId);
          $hasUnread    = $conv['unread_count'] > 0;
          $isOnline     = isUserOnline($conv['last_active']);
          $displayMessage = $conv['last_media']
            ? ($isLastFromMe ? '📷 Vous : une photo' : '📷 une photo')
            : ($isLastFromMe ? 'Vous : ' . truncateMessage($conv['last_message'])
              : truncateMessage($conv['last_message']));
        ?>
          <div data-username="<?= strtolower(htmlspecialchars($conv['username'])) ?>"
            class="contact-item glass-card rounded-2xl p-4 flex items-center gap-3
                    <?= $hasUnread ? 'message-preview' : '' ?>
                    <?= $isOnline ? 'border-green-500/20' : '' ?>">

            <a href="conversation.php?user=<?= (int)$conv['contact_id'] ?>"
              class="flex items-center gap-4 flex-1 min-w-0">

              <div class="avatar-container flex-shrink-0">
                <div class="w-14 h-14 rounded-full overflow-hidden border-2
                            <?= $hasUnread ? 'border-blue-500' : 'border-gray-700' ?>
                            <?= $isOnline ? 'shadow-lg shadow-green-500/20' : '' ?>">
                  <img src="../assets/images/<?= htmlspecialchars(resolveContactAvatar((int)$conv['contact_id'], $conv['profile_pic'])) ?>?v=<?= time() ?>"
                    class="w-full h-full object-cover"
                    alt="<?= htmlspecialchars($conv['username']) ?>"
                    onerror="this.src='../assets/images/default-avatar.png'">
                </div>
                <?php if ($isOnline): ?>
                  <div class="online-indicator" title="En ligne"></div>
                <?php endif; ?>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <h3 class="font-bold text-white truncate"><?= htmlspecialchars($conv['username']) ?></h3>
                  <span class="text-xs text-gray-500 flex-shrink-0 ml-2">
                    <?= formatMessageTime($conv['last_date']) ?>
                  </span>
                </div>
                <p class="text-sm <?= $hasUnread ? 'text-white font-medium' : 'text-gray-400' ?> truncate">
                  <?= htmlspecialchars($displayMessage) ?>
                </p>
              </div>
            </a>

            <div class="flex items-center gap-2 flex-shrink-0">
              <?php if ($hasUnread): ?>
                <span class="w-6 h-6 unread-badge rounded-full flex items-center justify-center text-xs font-bold">
                  <?= (int)$conv['unread_count'] > 99 ? '99+' : (int)$conv['unread_count'] ?>
                </span>
              <?php endif; ?>
              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 text-gray-700 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <form method="POST"
                onsubmit="return confirmDelete('<?= htmlspecialchars(addslashes($conv['username'])) ?>')">
                <input type="hidden" name="delete_contact_id" value="<?= (int)$conv['contact_id'] ?>">
                <button type="submit"
                  class="p-2 rounded-lg text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition-all"
                  title="Supprimer la conversation">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Section Tous les membres -->
    <div id="allMembersSection" class="mt-8 <?= empty($conversations) ? '' : 'hidden' ?>">
      <?php if (empty($conversations)): ?>
        <div class="text-center py-12 mb-6">
          <div class="empty-state-icon w-20 h-20 mx-auto mb-4 rounded-2xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <p class="text-gray-400 mb-2">Aucune conversation pour le moment</p>
          <p class="text-gray-500 text-sm">Commencez à discuter avec un membre !</p>
        </div>
      <?php else: ?>
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-4 font-semibold">
          Membres de la communauté
        </p>
      <?php endif; ?>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="membersGrid">
        <?php foreach ($allUsers as $u):
          $isOnline = isUserOnline($u['last_active']);
        ?>
          <a href="conversation.php?user=<?= (int)$u['id'] ?>"
            data-username="<?= strtolower(htmlspecialchars($u['username'])) ?>"
            class="member-item member-card rounded-xl p-4 text-center group">
            <div class="avatar-container mx-auto mb-3">
              <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-gray-700
                          group-hover:border-blue-500/50 transition-colors">
                <img src="../assets/images/<?= htmlspecialchars(resolveContactAvatar((int)$u['id'], $u['profile_pic'])) ?>?v=<?= time() ?>"
                  class="w-full h-full object-cover"
                  alt="<?= htmlspecialchars($u['username']) ?>"
                  onerror="this.src='../assets/images/default-avatar.png'">
              </div>
              <?php if ($isOnline): ?>
                <div class="online-indicator" title="En ligne"></div>
              <?php endif; ?>
            </div>
            <p class="text-sm font-medium truncate text-gray-300 group-hover:text-white transition-colors">
              <?= htmlspecialchars($u['username']) ?>
            </p>
            <p class="text-xs text-gray-600 mt-1">
              <?= $isOnline ? 'En ligne' : 'Hors ligne' ?>
            </p>
          </a>
        <?php endforeach; ?>
      </div>

      <p id="noMemberResults" class="hidden text-gray-500 text-sm text-center py-8">
        Aucun résultat trouvé
      </p>
    </div>
  </div>

  <script>
    function confirmDelete(username) {
      return confirm('Supprimer toute la conversation avec ' + username + ' ?\nCette action est irréversible.');
    }

    function filterContacts(query) {
      const q = query.toLowerCase().trim();
      const allMembersSection = document.getElementById('allMembersSection');
      const noMemberResults   = document.getElementById('noMemberResults');
      const hasConversations  = document.querySelector('.contact-item') !== null;

      const convItems = document.querySelectorAll('.contact-item');
      convItems.forEach(item => {
        const name  = item.dataset.username || '';
        item.style.display = (!q || name.includes(q)) ? '' : 'none';
      });

      if (q || !hasConversations) {
        if (allMembersSection) allMembersSection.classList.remove('hidden');

        const memberItems = document.querySelectorAll('.member-item');
        let memberVisible = 0;
        memberItems.forEach(item => {
          const name  = item.dataset.username || '';
          const match = !q || name.includes(q);
          item.style.display = match ? '' : 'none';
          if (match) memberVisible++;
        });

        if (noMemberResults) {
          if (memberVisible === 0 && q) {
            noMemberResults.textContent = 'Aucun résultat pour "' + query + '"';
            noMemberResults.classList.remove('hidden');
            document.getElementById('membersGrid').classList.add('hidden');
          } else {
            noMemberResults.classList.add('hidden');
            document.getElementById('membersGrid').classList.remove('hidden');
          }
        }
      } else {
        if (allMembersSection && hasConversations) allMembersSection.classList.add('hidden');
        if (noMemberResults) noMemberResults.classList.add('hidden');
        document.querySelectorAll('.member-item').forEach(item => item.style.display = '');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      if (searchInput && !searchInput.value) searchInput.focus();
    });

    setInterval(() => {
      const activeElement = document.activeElement;
      if (activeElement && activeElement.id !== 'searchInput') location.reload();
    }, 30000);
  </script>
</body>

</html>
