<?php
session_start();
require_once '../config/db.php';

// Sécurité
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

// Récupération de tous les exercices
$stmt = $pdo->query("SELECT * FROM exercises_reference ORDER BY name_fr ASC");
$all_exercises = $stmt->fetchAll();

// Séparation PHP
$male_exercises = array_filter($all_exercises, function ($e) {
  return $e['gender'] === 'male';
});
$female_exercises = array_filter($all_exercises, function ($e) {
  return $e['gender'] === 'female';
});
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exercices - AGRE fitness</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white p-4 md:p-8">
  <div class="max-w-5xl mx-auto">

    <header class="flex justify-between items-center mb-12">
      <div>
        <h1 class="text-3xl font-bold text-blue-500">AGRE Fitness</h1>
        <p class="text-gray-400 text-sm">Choisissez votre programme</p>
      </div>
      <a href="dashboard.php" class="bg-gray-800 hover:bg-gray-700 px-5 py-2 rounded-xl text-sm border border-gray-700 transition flex items-center gap-2">
        <span>🏠</span> Dashboard
      </a>
    </header>

    <div id="menu-selection" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
      <button onclick="showCategory('male')" class="group relative h-64 rounded-3xl overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all">
        <div class="absolute inset-0 bg-blue-900/40 group-hover:bg-blue-900/20 transition"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-6xl mb-4">🧔</span>
          <h2 class="text-3xl font-black uppercase tracking-widest text-white">Hommes</h2>
          <p class="text-blue-300 mt-2"><?= count($male_exercises) ?> exercices</p>
        </div>
      </button>

      <button onclick="showCategory('female')" class="group relative h-64 rounded-3xl overflow-hidden border-2 border-transparent hover:border-pink-500 transition-all">
        <div class="absolute inset-0 bg-pink-900/40 group-hover:bg-pink-900/20 transition"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-6xl mb-4">👩</span>
          <h2 class="text-3xl font-black uppercase tracking-widest text-white">Femmes</h2>
          <p class="text-pink-300 mt-2"><?= count($female_exercises) ?> exercices</p>
        </div>
      </button>
    </div>

    <div id="video-display" class="hidden">
      <button onclick="goBackToMenu()" class="flex items-center text-blue-400 hover:text-blue-300 mb-8 font-bold transition">
        <span class="mr-2 text-xl">←</span> Changer de catégorie
      </button>

      <h2 id="category-title" class="text-3xl font-bold mb-8 border-l-4 pl-4 uppercase"></h2>

      <div id="grid-male" class="hidden grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach ($male_exercises as $exo): ?>
          <div class="bg-gray-900 rounded-3xl overflow-hidden border border-gray-800">
            <video controls class="w-full aspect-video bg-black" preload="metadata">
              <source src="../assets/videos/male/<?= htmlspecialchars($exo['video_file']) ?>" type="video/mp4">
            </video>
            <div class="p-6">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($exo['name_fr']) ?></h3>
              <p class="text-blue-400 text-sm font-bold uppercase mt-1"><?= htmlspecialchars($exo['muscle_group']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="grid-female" class="hidden grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach ($female_exercises as $exo): ?>
          <div class="bg-gray-900 rounded-3xl overflow-hidden border border-gray-800">
            <video controls class="w-full aspect-video bg-black" preload="metadata">
              <source src="../assets/videos/female/<?= htmlspecialchars($exo['video_file']) ?>" type="video/mp4">
            </video>
            <div class="p-6">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($exo['name_fr']) ?></h3>
              <p class="text-pink-400 text-sm font-bold uppercase mt-1"><?= htmlspecialchars($exo['muscle_group']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <script>
    function showCategory(gender) {
      document.getElementById('menu-selection').classList.add('hidden');
      document.getElementById('video-display').classList.remove('hidden');

      const title = document.getElementById('category-title');

      if (gender === 'male') {
        document.getElementById('grid-male').classList.remove('hidden');
        document.getElementById('grid-female').classList.add('hidden');
        title.innerText = "🧔 Musculation Hommes";
        title.style.borderColor = "#3b82f6"; // Bleu
      } else {
        document.getElementById('grid-female').classList.remove('hidden');
        document.getElementById('grid-male').classList.add('hidden');
        title.innerText = "👩 Musculation Femmes";
        title.style.borderColor = "#ec4899"; // Rose
      }
      window.scrollTo(0, 0);
    }

    function goBackToMenu() {
      document.getElementById('menu-selection').classList.remove('hidden');
      document.getElementById('video-display').classList.add('hidden');
      window.scrollTo(0, 0);
    }
  </script>
</body>

</html>