<?php

/**
 * CONVERSATION - V1.1 AGRE FITNESS
 * Conversation privée avec un utilisateur
 * AJAX pour envoi texte + images
 */

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

// Initialisation de la langue
initLanguage();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$currentUserId = $_SESSION['user_id'];
$chatUserId = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Vérifier que l'utilisateur existe
$stmt = $pdo->prepare("SELECT id, username, profile_pic FROM fitness_users WHERE id = ?");
$stmt->execute([$chatUserId]);
$chatUser = $stmt->fetch();

if (!$chatUser) {
  header('Location: messages.php');
  exit;
}

// Marquer les messages comme lus
$stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE destinataire_id = ? AND expediteur_id = ?");
$stmt->execute([$currentUserId, $chatUserId]);

// Récupérer tous les messages
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.profile_pic 
    FROM messages m
    JOIN fitness_users u ON m.expediteur_id = u.id
    WHERE (m.expediteur_id = ? AND m.destinataire_id = ?) 
       OR (m.expediteur_id = ? AND m.destinataire_id = ?)
    ORDER BY m.date_envoi ASC
");
$stmt->execute([$currentUserId, $chatUserId, $chatUserId, $currentUserId]);
$messages = $stmt->fetchAll();

$_convImgDir = __DIR__ . '/../assets/images/';
$_convUid    = (int)$_SESSION['user_id'];
if (!empty($_SESSION['profile_pic']) && file_exists($_convImgDir . $_SESSION['profile_pic'])) {
  $currentProfilePic = $_SESSION['profile_pic'];
} elseif (file_exists($_convImgDir . 'profile_' . $_convUid . '.jpeg')) {
  $currentProfilePic = 'profile_' . $_convUid . '.jpeg';
} elseif (file_exists($_convImgDir . 'profile_' . $_convUid . '.jpg')) {
  $currentProfilePic = 'profile_' . $_convUid . '.jpg';
} else {
  $currentProfilePic = 'default-avatar.png';
}

$_chatPic = $chatUser['profile_pic'] ?? null;
if (!empty($_chatPic) && file_exists($_convImgDir . $_chatPic)) {
  $chatProfilePic = $_chatPic;
} elseif (file_exists($_convImgDir . 'profile_' . $chatUserId . '.jpeg')) {
  $chatProfilePic = 'profile_' . $chatUserId . '.jpeg';
} elseif (file_exists($_convImgDir . 'profile_' . $chatUserId . '.jpg')) {
  $chatProfilePic = 'profile_' . $chatUserId . '.jpg';
} else {
  $chatProfilePic = 'default-avatar.png';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <title>Conversation avec <?= htmlspecialchars($chatUser['username']) ?> | AGRE Fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
    }

    body {
      background-color: #050505;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      height: 100vh;
      overflow: hidden;
    }

    .glass-card {
      background: rgba(17, 17, 17, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .message-sent {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      border-radius: 18px 18px 4px 18px;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .message-received {
      background: rgba(55, 65, 81, 0.9);
      border-radius: 18px 18px 18px 4px;
    }

    .chat-container {
      height: calc(100vh - 180px);
      overflow-y: auto;
      scroll-behavior: smooth;
    }

    .input-area {
      background: rgba(17, 17, 17, 0.98);
      backdrop-filter: blur(20px);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .typing-indicator span {
      animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
      animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
      animation-delay: 0.4s;
    }

    @keyframes typing {

      0%,
      100% {
        opacity: 0.3;
      }

      50% {
        opacity: 1;
      }
    }

    .image-preview {
      max-height: 150px;
      border-radius: 10px;
    }
  </style>
</head>

<body class="text-white">
  <!-- Header -->
  <header class="fixed top-0 left-0 right-0 z-50 glass-card border-b border-gray-800">
    <div class="flex items-center gap-3 p-4 max-w-4xl mx-auto">
      <a href="messages.php" class="p-2 -ml-2 rounded-full hover:bg-gray-800 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </a>

      <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-blue-500">
        <img src="../assets/images/<?= htmlspecialchars($chatProfilePic) ?>?v=<?= time() ?>"
          class="w-full h-full object-cover"
          onerror="this.src='../assets/images/default-avatar.png'">
      </div>

      <div class="flex-1">
        <h2 class="font-bold text-lg"><?= htmlspecialchars($chatUser['username']) ?></h2>
        <p class="text-xs text-green-400">En ligne</p>
      </div>

      <a href="profile.php?id=<?= $chatUser['id'] ?>" class="p-2 rounded-full hover:bg-gray-800 transition-colors" title="Voir le profil">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </a>
    </div>
  </header>

  <!-- Zone de chat -->
  <div id="chatContainer" class="chat-container pt-20 pb-4 px-4 max-w-4xl mx-auto">
    <?php if (empty($messages)): ?>
      <div class="flex items-center justify-center h-full text-gray-500">
        <div class="text-center">
          <p class="mb-2">💬</p>
          <p>Démarrez la conversation !</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($messages as $msg):
        $isSent = ($msg['expediteur_id'] == $currentUserId);
      ?>
        <div class="flex <?= $isSent ? 'justify-end' : 'justify-start' ?> mb-4" data-message-id="<?= $msg['id'] ?>">
          <?php if (!$isSent): ?>
            <img src="../assets/images/<?= htmlspecialchars($msg['profile_pic'] ?? 'default-avatar.png') ?>"
              class="w-8 h-8 rounded-full mr-2 self-end">
          <?php endif; ?>

          <div class="max-w-[75%] <?= $isSent ? 'message-sent' : 'message-received' ?> p-3 text-sm">
            <?php if ($msg['media_url']): ?>
              <img src="../assets/images/<?= htmlspecialchars($msg['media_url']) ?>"
                class="image-preview mb-2 cursor-pointer"
                onclick="window.open(this.src, '_blank')">
            <?php endif; ?>
            <?php if ($msg['contenu']): ?>
              <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
            <?php endif; ?>
            <div class="flex items-center justify-end gap-1 mt-1 opacity-70">
              <span class="text-xs"><?= date('H:i', strtotime($msg['date_envoi'])) ?></span>
              <?php if ($isSent): ?>
                <?php if ($msg['lu']): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6 10.586l5.293-5.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Zone de saisie -->
  <div class="input-area fixed bottom-0 left-0 right-0 p-4">
    <form id="messageForm" class="max-w-4xl mx-auto flex gap-2 items-end">
      <input type="hidden" id="destinataireId" value="<?= $chatUserId ?>">

      <!-- Bouton image -->
      <label class="p-3 rounded-full bg-gray-800 hover:bg-gray-700 cursor-pointer transition-colors flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <input type="file" id="imageInput" accept="image/*" class="hidden">
      </label>

      <!-- Input texte -->
      <div class="flex-1 bg-gray-800 rounded-2xl border border-gray-700 focus-within:border-blue-500 transition-colors overflow-hidden">
        <textarea id="messageInput" rows="1" placeholder="Écrivez un message..."
          class="w-full bg-transparent px-4 py-3 text-white resize-none focus:outline-none max-h-24"></textarea>
      </div>

      <!-- Bouton envoyer -->
      <button type="submit" id="sendBtn" class="p-3 rounded-full bg-blue-500 hover:bg-blue-400 transition-colors flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
      </button>
    </form>

    <!-- Preview image -->
    <div id="imagePreview" class="hidden max-w-4xl mx-auto mt-2">
      <div class="relative inline-block">
        <img id="previewImg" class="h-20 rounded-lg border border-gray-700">
        <button onclick="clearImagePreview()" class="absolute -top-2 -right-2 bg-red-500 rounded-full p-1 hover:bg-red-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <script>
    // Scroll to bottom
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Image preview
    let selectedImage = null;
    document.getElementById('imageInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        selectedImage = file;
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      }
    });

    function clearImagePreview() {
      selectedImage = null;
      document.getElementById('imageInput').value = '';
      document.getElementById('imagePreview').classList.add('hidden');
    }

    // Envoi message AJAX
    document.getElementById('messageForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const content = messageInput.value.trim();
      const destinataireId = document.getElementById('destinataireId').value;

      if (!content && !selectedImage) return;

      const formData = new FormData();
      formData.append('destinataire_id', destinataireId);
      formData.append('contenu', content);
      if (selectedImage) {
        formData.append('image', selectedImage);
      }

      // Reset input
      messageInput.value = '';
      messageInput.style.height = 'auto';
      clearImagePreview();

      // Désactiver bouton
      const sendBtn = document.getElementById('sendBtn');
      sendBtn.disabled = true;
      sendBtn.classList.add('opacity-50');

      try {
        const response = await fetch('send_message.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success) {
          // Ajouter message au DOM
          appendMessage(data);
          chatContainer.scrollTop = chatContainer.scrollHeight;
        } else {
          alert(data.error || 'Erreur lors de l\'envoi');
        }
      } catch (err) {
        console.error('Erreur:', err);
        alert('Erreur de connexion');
      } finally {
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-50');
      }
    });

    function appendMessage(data) {
      const div = document.createElement('div');
      div.className = 'flex justify-end mb-4';

      let mediaHtml = '';
      if (data.media_url) {
        mediaHtml = `<img src="../assets/images/${data.media_url}" class="image-preview mb-2 cursor-pointer" onclick="window.open(this.src, '_blank')">`;
      }

      let contentHtml = '';
      if (data.contenu) {
        contentHtml = `<p>${data.contenu.replace(/\n/g, '<br>')}</p>`;
      }

      div.innerHTML = `
        <div class="max-w-[75%] message-sent p-3 text-sm">
          ${mediaHtml}
          ${contentHtml}
          <div class="flex items-center justify-end gap-1 mt-1 opacity-70">
            <span class="text-xs">${data.time}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      `;

      chatContainer.appendChild(div);
    }

    // Polling pour nouveaux messages (toutes les 3 secondes)
    let lastMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

    setInterval(async () => {
      try {
        const response = await fetch(`get_messages.php?user=<?= $chatUserId ?>&last_id=${lastMessageId}`);
        const data = await response.json();

        if (data.messages && data.messages.length > 0) {
          data.messages.forEach(msg => {
            if (msg.expediteur_id != <?= $currentUserId ?>) {
              appendReceivedMessage(msg);
              lastMessageId = msg.id;
            }
          });
          chatContainer.scrollTop = chatContainer.scrollHeight;
        }
      } catch (err) {
        console.error('Polling error:', err);
      }
    }, 3000);

    function appendReceivedMessage(msg) {
      const div = document.createElement('div');
      div.className = 'flex justify-start mb-4';

      let mediaHtml = '';
      if (msg.media_url) {
        mediaHtml = `<img src="../assets/images/${msg.media_url}" class="image-preview mb-2 cursor-pointer" onclick="window.open(this.src, '_blank')">`;
      }

      let contentHtml = '';
      if (msg.contenu) {
        contentHtml = `<p>${msg.contenu.replace(/\n/g, '<br>')}</p>`;
      }

      div.innerHTML = `
        <img src="../assets/images/${msg.profile_pic || 'default-avatar.png'}" class="w-8 h-8 rounded-full mr-2 self-end">
        <div class="max-w-[75%] message-received p-3 text-sm">
          ${mediaHtml}
          ${contentHtml}
          <div class="flex items-center justify-end gap-1 mt-1 opacity-70">
            <span class="text-xs">${msg.time}</span>
          </div>
        </div>
      `;

      chatContainer.appendChild(div);
    }
  </script>
</body>

</html>