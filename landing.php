<?php
/**
 * =============================================================================
 * LANDING PAGE - AGRE FITNESS v2.0
 * =============================================================================
 * Page d'accueil professionnelle et épurée présentant l'application.
 * Message de bienvenue signé Ange-Kevin Agre, fondateur.
 */

session_start();
require_once 'includes/translations.php';
initLanguage();

// Si déjà connecté, redirection vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$lang = $_SESSION['lang'] ?? 'fr';

// Détection mobile pour animations optimisées
$isMobile = isset($_SERVER['HTTP_USER_AGENT']) && 
    preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#050505">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="AGRE Fitness">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="description" content="AGRE Fitness - Suivez votre évolution et rejoignez une communauté motivée de passionnés fitness">
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
  <title><?= __t('landing_title', $lang) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      -webkit-tap-highlight-color: transparent;
      box-sizing: border-box;
    }
    
    html {
      scroll-behavior: smooth;
    }
    
    body {
      background: #050505;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      overflow-x: hidden;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }
    
    @keyframes gradient {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    .animate-fade-up {
      opacity: 0;
      animation: fadeInUp 0.8s ease forwards;
    }
    
    .animate-delay-1 { animation-delay: 0.1s; }
    .animate-delay-2 { animation-delay: 0.2s; }
    .animate-delay-3 { animation-delay: 0.3s; }
    .animate-delay-4 { animation-delay: 0.4s; }
    .animate-delay-5 { animation-delay: 0.5s; }
    
    .animate-float {
      animation: float 6s ease-in-out infinite;
    }
    
    .animate-pulse-slow {
      animation: pulse 3s ease-in-out infinite;
    }

    /* Gradient background */
    .gradient-bg {
      background: linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111827 50%, #0a0a0a 75%, #050505 100%);
      background-size: 400% 400%;
      animation: gradient 15s ease infinite;
    }

    /* Glassmorphism */
    .glass {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .glass-card {
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .glass-card:hover {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(59, 130, 246, 0.3);
      transform: translateY(-8px);
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
    }

    /* Hero gradient text */
    .gradient-text {
      background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #f472b6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .gradient-text-blue {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Buttons */
    .btn-primary {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-primary:hover::before {
      left: 100%;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 40px rgba(59, 130, 246, 0.4);
    }
    
    .btn-secondary {
      border: 2px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.4);
      transform: translateY(-2px);
    }

    /* Feature icon glow */
    .icon-glow {
      position: relative;
    }
    
    .icon-glow::before {
      content: '';
      position: absolute;
      inset: -10px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
      border-radius: 50%;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .glass-card:hover .icon-glow::before {
      opacity: 1;
    }

    /* Decorative elements */
    .decoration-circle {
      position: absolute;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
      filter: blur(40px);
    }
    
    .decoration-grid {
      background-image: 
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
      background-size: 60px 60px;
    }

    /* Profile image styling */
    .profile-img-container {
      position: relative;
    }
    
    .profile-img-container::before {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
      z-index: -1;
      animation: gradient 3s ease infinite;
      background-size: 200% 200%;
    }

    /* Stats counter animation */
    @keyframes countUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-number {
      animation: countUp 0.6s ease forwards;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
      .animate-float {
        animation: none;
      }
      
      .decoration-circle {
        opacity: 0.5;
      }
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #050505;
    }
    
    ::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="gradient-bg text-white min-h-screen">
  
  <!-- Decorative Elements -->
  <div class="decoration-circle w-96 h-96 -top-48 -left-48"></div>
  <div class="decoration-circle w-[500px] h-[500px] top-1/3 -right-48"></div>
  <div class="decoration-circle w-80 h-80 bottom-0 left-1/4"></div>
  
  <!-- Background Grid -->
  <div class="fixed inset-0 decoration-grid opacity-50 pointer-events-none"></div>

  <!-- Navigation -->
  <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <a href="landing.php" class="flex items-center gap-2">
        <span class="text-2xl font-black tracking-tighter">
          <span class="text-blue-500">AGRE</span>
          <span class="text-white">FITNESS</span>
        </span>
      </a>
      
      <div class="flex items-center gap-4">
        <a href="index.php" class="hidden sm:block text-gray-400 hover:text-white transition-colors text-sm">
          <?= __t('landing_cta_login', $lang) ?>
        </a>
        <a href="signup.php" class="btn-primary px-5 py-2.5 rounded-xl font-semibold text-sm">
          <?= __t('landing_cta_signup', $lang) ?>
        </a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="relative min-h-screen flex items-center justify-center px-6 pt-20">
    <div class="max-w-5xl mx-auto text-center">
      
      <!-- Welcome Badge -->
      <div class="animate-fade-up inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-8">
        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
        <span class="text-sm text-gray-300">v2.0 Global & Social Edition</span>
      </div>
      
      <!-- Main Title -->
      <h1 class="animate-fade-up animate-delay-1 text-5xl sm:text-6xl md:text-7xl font-black tracking-tight mb-6">
        <span class="text-white"><?= __t('landing_welcome', $lang) ?></span>
        <br>
        <span class="gradient-text">AGRE Fitness</span>
      </h1>
      
      <!-- Subtitle -->
      <p class="animate-fade-up animate-delay-2 text-xl text-gray-400 max-w-2xl mx-auto mb-12 leading-relaxed">
        <?= __t('landing_intro', $lang) ?>
      </p>
      
      <!-- CTA Buttons -->
      <div class="animate-fade-up animate-delay-3 flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
        <a href="signup.php" class="btn-primary px-8 py-4 rounded-2xl font-bold text-lg flex items-center gap-2">
          <span><?= __t('landing_cta_signup', $lang) ?></span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
        <a href="index.php" class="btn-secondary px-8 py-4 rounded-2xl font-bold text-lg">
          <?= __t('landing_cta_login', $lang) ?>
        </a>
      </div>
      
      <!-- Creator Message Card -->
      <div class="animate-fade-up animate-delay-4 glass rounded-3xl p-8 max-w-3xl mx-auto relative">
        <div class="absolute -top-6 left-1/2 -translate-x-1/2">
          <div class="profile-img-container">
            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-[#111] bg-gray-800">
              <!-- Placeholder pour PHOTO_AGRE_IDENTITE -->
              <img src="assets/images/PHOTO_AGRE_IDENTITE.jpg?v=2" 
                   alt="Ange-Kevin Agre" 
                   class="w-full h-full object-cover"
                   onerror="this.src='assets/images/default_avatar.png'; this.onerror=null;">
            </div>
          </div>
        </div>
        
        <div class="pt-12 text-center">
          <p class="text-gray-300 leading-relaxed mb-4 italic">
            "Je suis <span class="text-white font-semibold">Ange-Kevin Agre</span>, créateur de cette application. 
            AGRE Fitness est aujourd'hui bien plus qu'un simple tracker : c'est une communauté où vous pouvez 
            suivre votre évolution ET partager votre passion avec d'autres athlètes motivés. 
            Discutez, publiez vos progrès, et inspirez-vous mutuellement !"
          </p>
          <p class="text-blue-400 font-semibold"><?= __t('landing_creator_sign', $lang) ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="relative py-24 px-6">
    <div class="max-w-6xl mx-auto">
      
      <div class="text-center mb-16 animate-fade-up">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">
          <span class="gradient-text-blue"><?= __t('landing_features_title', $lang) ?></span>
        </h2>
        <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-purple-500 mx-auto rounded-full"></div>
      </div>
      
      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Feature 1: Progress Tracking -->
        <div class="glass-card rounded-3xl p-8 animate-fade-up animate-delay-1">
          <div class="icon-glow w-16 h-16 rounded-2xl bg-blue-500/10 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-3"><?= __t('landing_feature_1_title', $lang) ?></h3>
          <p class="text-gray-400 leading-relaxed"><?= __t('landing_feature_1_desc', $lang) ?></p>
        </div>
        
        <!-- Feature 2: Exercise Library -->
        <div class="glass-card rounded-3xl p-8 animate-fade-up animate-delay-2">
          <div class="icon-glow w-16 h-16 rounded-2xl bg-red-500/10 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-3"><?= __t('landing_feature_2_title', $lang) ?></h3>
          <p class="text-gray-400 leading-relaxed"><?= __t('landing_feature_2_desc', $lang) ?></p>
        </div>
        
        <!-- Feature 3: Messaging -->
        <div class="glass-card rounded-3xl p-8 animate-fade-up animate-delay-3">
          <div class="icon-glow w-16 h-16 rounded-2xl bg-green-500/10 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-3"><?= __t('landing_feature_3_title', $lang) ?></h3>
          <p class="text-gray-400 leading-relaxed"><?= __t('landing_feature_3_desc', $lang) ?></p>
        </div>
        
        <!-- Feature 4: Social Feed -->
        <div class="glass-card rounded-3xl p-8 animate-fade-up animate-delay-4">
          <div class="icon-glow w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-3"><?= __t('landing_feature_4_title', $lang) ?></h3>
          <p class="text-gray-400 leading-relaxed"><?= __t('landing_feature_4_desc', $lang) ?></p>
        </div>
        
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="relative py-16 px-6">
    <div class="max-w-4xl mx-auto">
      <div class="glass rounded-3xl p-8 md:p-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div class="stat-number" style="animation-delay: 0.1s">
            <p class="text-4xl md:text-5xl font-black gradient-text-blue mb-2">7</p>
            <p class="text-sm text-gray-500 uppercase tracking-wider">Langues</p>
          </div>
          <div class="stat-number" style="animation-delay: 0.2s">
            <p class="text-4xl md:text-5xl font-black gradient-text-blue mb-2">310+</p>
            <p class="text-sm text-gray-500 uppercase tracking-wider">Exercices</p>
          </div>
          <div class="stat-number" style="animation-delay: 0.3s">
            <p class="text-4xl md:text-5xl font-black gradient-text-blue mb-2">∞</p>
            <p class="text-sm text-gray-500 uppercase tracking-wider">Messages</p>
          </div>
          <div class="stat-number" style="animation-delay: 0.4s">
            <p class="text-4xl md:text-5xl font-black gradient-text-blue mb-2">100%</p>
            <p class="text-sm text-gray-500 uppercase tracking-wider">Gratuit</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Final CTA Section -->
  <section class="relative py-24 px-6">
    <div class="max-w-3xl mx-auto text-center">
      <h2 class="animate-fade-up text-3xl sm:text-4xl font-bold mb-6">
        <span class="text-white">Prêt à commencer votre</span>
        <br>
        <span class="gradient-text">transformation ?</span>
      </h2>
      
      <p class="animate-fade-up animate-delay-1 text-gray-400 mb-10 max-w-xl mx-auto">
        Rejoignez la communauté AGRE Fitness dès aujourd'hui et commencez à suivre vos progrès 
        tout en partageant votre passion avec d'autres athlètes.
      </p>
      
      <div class="animate-fade-up animate-delay-2 flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="signup.php" class="btn-primary px-10 py-5 rounded-2xl font-bold text-lg">
          <?= __t('landing_cta_signup', $lang) ?>
        </a>
        <a href="index.php" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
          <?= __t('landing_cta_login', $lang) ?>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="relative py-12 px-6 border-t border-white/5">
    <div class="max-w-6xl mx-auto">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-2">
          <span class="text-xl font-black tracking-tighter">
            <span class="text-blue-500">AGRE</span>
            <span class="text-white">FITNESS</span>
          </span>
        </div>
        
        <p class="text-gray-500 text-sm">
          © 2025 <?= __t('landing_footer', $lang) ?>
        </p>
        
        <div class="flex items-center gap-6 text-sm text-gray-400">
          <a href="index.php" class="hover:text-white transition-colors"><?= __t('landing_cta_login', $lang) ?></a>
          <a href="signup.php" class="hover:text-white transition-colors"><?= __t('landing_cta_signup', $lang) ?></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript for Interactions -->
  <script>
    // Intersection Observer for scroll animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);
    
    document.querySelectorAll('.animate-fade-up').forEach(el => {
      observer.observe(el);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
    
    // Parallax effect for decorative circles (desktop only)
    if (!window.matchMedia('(pointer: coarse)').matches) {
      document.addEventListener('mousemove', (e) => {
        const circles = document.querySelectorAll('.decoration-circle');
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        
        circles.forEach((circle, index) => {
          const speed = (index + 1) * 10;
          const xOffset = (window.innerWidth / 2 - e.clientX) / speed;
          const yOffset = (window.innerHeight / 2 - e.clientY) / speed;
          circle.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
        });
      });
    }
  </script>

</body>
</html>
