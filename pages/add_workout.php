<?php

/**
 * =============================================================================
 * ADD WORKOUT V2.0 - AGRE FITNESS
 * =============================================================================
 * Ajout de séance avec système multilingue.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/translations.php';

// Initialisation de la langue
initLanguage();

// Vérification session
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$lang = getCurrentLang();

// Récupération des exercices
$stmt = $pdo->query("SELECT id, name_fr FROM exercises_reference ORDER BY name_fr ASC");
$exercises = $stmt->fetchAll();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $exercise_id = $_POST['exercise_id'] ?? '';
  $weight = $_POST['weight'] ?? '';
  $reps = $_POST['reps'] ?? '';
  $sets = $_POST['sets'] ?? '';
  $user_id = $_SESSION['user_id'];

  if ($exercise_id && $weight && $reps && $sets) {
    $insert = $pdo->prepare("INSERT INTO workout_logs (user_id, exercise_id, weight, reps, sets) VALUES (?, ?, ?, ?, ?)");
    if ($insert->execute([$user_id, $exercise_id, $weight, $reps, $sets])) {
      $success = "Séance enregistrée avec succès !";
    }
  }
}

// Récupération de l'historique
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
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Noter une séance | AGRE Fitness</title>
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

    .glass-input {
      background: rgba(31, 31, 31, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
  </style>
</head>

<body class="text-white min-h-screen font-sans">

  <?php require_once '../includes/navbar.php'; ?>

  <!-- Bouton Retour -->
  <div class="max-w-2xl mx-auto px-6 pt-4">
    <a href="dashboard.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-all group">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      <span class="text-sm font-bold">Retour au Dashboard</span>
    </a>
  </div>

  <main class="p-6 max-w-2xl mx-auto">

    <!-- Header -->
    <header class="mb-8 text-center">
      <h1 class="text-3xl font-black tracking-tight mb-2">
        <span class="text-[#3b82f6]">NOTER</span> UNE SÉANCE
      </h1>
      <p class="text-gray-500 text-sm">Enregistrez vos performances</p>
    </header>

    <?php if ($success): ?>
      <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="glass-card rounded-2xl p-6 mb-8">
      <form method="POST" class="space-y-5">
        <!-- Exercice -->
        <div>
          <label class="block text-sm font-medium text-gray-400 mb-2">Exercice</label>
          <select name="exercise_id" required
            class="w-full p-3.5 rounded-xl glass-input text-white focus:border-[#3b82f6] focus:outline-none transition-all">
            <option value="" class="bg-[#111]">Sélectionnez un exercice</option>
            <?php foreach ($exercises as $ex): ?>
              <option value="<?= $ex['id'] ?>" class="bg-[#111]"><?= htmlspecialchars($ex['name_fr']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- Poids -->
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Poids (kg)</label>
            <input type="number" step="0.5" name="weight" placeholder="60" required
              class="w-full p-3.5 rounded-xl glass-input text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all text-center">
          </div>

          <!-- Séries -->
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Séries</label>
            <input type="number" name="sets" placeholder="4" required
              class="w-full p-3.5 rounded-xl glass-input text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all text-center">
          </div>

          <!-- Répétitions -->
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Répétitions</label>
            <input type="number" name="reps" placeholder="10" required
              class="w-full p-3.5 rounded-xl glass-input text-white placeholder-gray-600 focus:border-[#3b82f6] focus:outline-none transition-all text-center">
          </div>
        </div>

        <!-- Bouton -->
        <button type="submit"
          class="w-full bg-[#3b82f6] hover:bg-blue-500 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Enregistrer la performance
        </button>
      </form>
    </div>

    <!-- Historique -->
    <?php if (!empty($logs)): ?>
      <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-bold mb-4 text-gray-300">Mes dernières séances</h3>
        <div class="space-y-3">
          <?php foreach ($logs as $log): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-[#111]/50 border border-gray-800">
              <div>
                <p class="font-medium text-white"><?= htmlspecialchars($log['name_fr']) ?></p>
                <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($log['date_completed'])) ?></p>
              </div>
              <div class="text-right">
                <p class="text-[#3b82f6] font-bold"><?= $log['weight'] ?> kg</p>
                <p class="text-gray-400 text-sm"><?= $log['sets'] ?> × <?= $log['reps'] ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </main>

</body>

</html>