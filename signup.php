<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



session_start();
require_once 'config/db.php';

// Si déjà connecté, redirection vers le dashboard
if (isset($_SESSION['user_id'])) {
  header('Location: pages/dashboard.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $gender = $_POST['gender'] ?? '';

  // Validation
  if (empty($username) || empty($email) || empty($password) || empty($gender)) {
    $error = "Veuillez remplir tous les champs.";
  } elseif (strlen($username) < 3) {
    $error = "Le pseudo doit contenir au moins 3 caractères.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Veuillez entrer un email valide.";
  } elseif (strlen($password) < 6) {
    $error = "Le mot de passe doit contenir au moins 6 caractères.";
  } else {
    // Vérifier si le username existe déjà
    $stmt = $pdo->prepare("SELECT id FROM fitness_users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
      $error = "Ce nom d'utilisateur est déjà pris.";
    } else {
      // Hash du mot de passe
      $passwordHash = password_hash($password, PASSWORD_BCRYPT);

      // Insertion
      $stmt = $pdo->prepare("INSERT INTO fitness_users (username, email, password_hash, gender) VALUES (?, ?, ?, ?)");

      if ($stmt->execute([$username, $email, $passwordHash, $gender])) {
        $success = "Compte créé avec succès ! Redirection...";
        header("Refresh: 2; URL=index.php");
      } else {
        $error = "Erreur lors de la création du compte.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - AGRE Fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-color: #050505;
    }

    .glass-card {
      background: rgba(17, 17, 17, 0.8);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
  </style>
</head>

<body class="text-white flex items-center justify-center min-h-screen font-sans">

  <div class="glass-card p-8 rounded-2xl shadow-2xl w-full max-w-md mx-4">
    <!-- Logo -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-black tracking-tighter">
        <span class="text-[#3b82f6]">AGRE</span> <span class="text-white">FITNESS</span>
      </h1>
      <p class="text-gray-500 text-sm mt-2">Créez votre compte</p>
    </div>

    <!-- Alertes -->
    <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <!-- Pseudo -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Pseudo</label>
        <input type="text" name="username" required
          class="w-full p-3.5 rounded-xl bg-[#111] border border-gray-800 text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all"
          placeholder="Votre pseudo">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
        <input type="email" name="email" required
          class="w-full p-3.5 rounded-xl bg-[#111] border border-gray-800 text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all"
          placeholder="votre@email.com">
      </div>

      <!-- Mot de passe -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Mot de passe</label>
        <input type="password" name="password" required
          class="w-full p-3.5 rounded-xl bg-[#111] border border-gray-800 text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all"
          placeholder="••••••••">
        <p class="text-xs text-gray-600 mt-1.5">Minimum 6 caractères</p>
      </div>

      <!-- Genre -->
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-3">Genre</label>
        <div class="grid grid-cols-2 gap-3">
          <label class="cursor-pointer">
            <input type="radio" name="gender" value="Homme" class="peer hidden" required>
            <div class="p-4 rounded-xl bg-[#111] border border-gray-800 text-center peer-checked:border-[#3b82f6] peer-checked:bg-[#3b82f6]/10 peer-checked:text-[#3b82f6] transition-all">
              <span class="text-2xl">♂️</span>
              <p class="text-sm font-medium mt-1">Homme</p>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="gender" value="Femme" class="peer hidden" required>
            <div class="p-4 rounded-xl bg-[#111] border border-gray-800 text-center peer-checked:border-pink-500 peer-checked:bg-pink-500/10 peer-checked:text-pink-500 transition-all">
              <span class="text-2xl">♀️</span>
              <p class="text-sm font-medium mt-1">Femme</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Bouton -->
      <button type="submit"
        class="w-full bg-[#3b82f6] hover:bg-blue-500 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-blue-500/25">
        Créer mon compte
      </button>
    </form>

    <!-- Lien connexion -->
    <p class="text-center text-gray-500 text-sm mt-6">
      Déjà un compte ?
      <a href="index.php" class="text-[#3b82f6] hover:underline font-medium">Se connecter</a>
    </p>
  </div>

</body>

</html>