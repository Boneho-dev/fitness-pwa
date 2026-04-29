# AGRE Fitness

<p align="center">
  <strong> Application Fitness PWA avec Système Social</strong><br>
  <em>Plateforme fitness complète - Projet étudiant IT</em>
</p>

---

## 📁 Structure du Projet

```
AGRE-Fitness/
├── 📂 assets/                    # Ressources statiques
│   ├── css/                     # Styles compilés
│   ├── js/                      # Scripts JavaScript
│   ├── images/                  # Images et avatars
│   ├── videos/                  # Bibliothèque d'exercices
│   └── icons/                   # Icônes PWA
│
├── 📂 config/                    # Configuration
│   └── db.php                   # Connexion MySQL (PDO)
│
├── 📂 includes/                # Logique métier PHP
│   ├── navbar.php               # Navigation responsive
│   ├── translations.php         # Système multilingue
│   └── lang/                    # 📁 Fichiers de traduction
│       ├── fr.php               # Français
│       ├── en.php               # English
│       ├── es.php               # Español
│       ├── de.php               # Deutsch
│       ├── it.php               # Italiano
│       ├── pt.php               # Português
│       └── zh.php               # 中文
│
├── 📂 pages/                     # Pages protégées (authentifiées)
│   ├── dashboard.php            # Tableau de bord
│   ├── feed.php                 # Fil social
│   ├── profile.php              # Profil utilisateur
│   ├── exercices.php            # Bibliothèque vidéo
│   ├── messages.php             # Messagerie
│   ├── conversation.php         # Conversation privée
│   ├── add_workout.php          # Ajout de séance
│   ├── follow_process.php       # API Follow/Unfollow
│   └── search_users.php         # API Recherche utilisateurs
│
├── 📂 database/                  # Scripts SQL
│   ├── social_tables.sql        # Tables sociales
│   └── exercises_library.sql    # 310+ exercices
│
├── index.php                    # Page de connexion
├── signup.php                   # Page d'inscription
├── landing.php                  # Landing page marketing
├── logout.php                   # Déconnexion
├── manifest.json                # Configuration PWA
├── service-worker.js            # Service Worker PWA
└── README.md                    # Ce fichier
```

---

## Système Multilingue (7 Langues)

Architecture modulaire dans `/includes/lang/` :

| Langue    | Fichier  | Flag |
| --------- | -------- | ---- |
| Français  | `fr.php` | 🇫🇷   |
| English   | `en.php` | 🇬🇧   |
| Español   | `es.php` | 🇪🇸   |
| Deutsch   | `de.php` | 🇩🇪   |
| Italiano  | `it.php` | 🇮🇹   |
| Português | `pt.php` | 🇵🇹   |
| 中文      | `zh.php` | 🇨🇳   |

### Usage

```php
<?php
require_once 'includes/translations.php';
initLanguage();

// Afficher une traduction
echo __('login_title');        // Auto-détection langue
echo __('login_title', 'en');   // Forcer langue EN
?>
```

---

## Base de Données

### Tables Principales

| Table                 | Description                 |
| --------------------- | --------------------------- |
| `fitness_users`       | Utilisateurs (auth, profil) |
| `workouts`            | Séances d'entraînement      |
| `posts`               | Publications du fil social  |
| `comments`            | Commentaires sur posts      |
| `likes`               | J'aime sur posts            |
| `followers`           | Relations follow/unfollow   |
| `conversations`       | Conversations messages      |
| `messages`            | Messages privés             |
| `exercises_reference` | Bibliothèque 310+ exercices |

---

## Fonctionnalités Clés

### Authentification

- Connexion/Inscription sécurisées (PDO + password_hash)
- Sessions avec persistance

### Social

- Système Follow/Unfollow AJAX
- Posts avec likes et commentaires
- Messagerie privée en temps réel
- Recherche prédictive d'utilisateurs

### Fitness

- Tableau de bord avec statistiques
- Bibliothèque 310+ exercices vidéo
- Journal d'entraînement
- Catégorisation par muscle/difficulté

### PWA

- Installable sur mobile
- Mode hors ligne
- Push notifications (prêt)
- Service Worker caching

---

## Stack Technique

| Couche   | Technologie                 |
| -------- | --------------------------- |
| Backend  | PHP 8.1+ (PDO, Sessions)    |
| Base     | MySQL 5.7+                  |
| Frontend | HTML5, Tailwind CSS (CDN)   |
| Icons    | Lucide / Heroicons          |
| PWA      | Manifest v3, Service Worker |

---

## Responsive Design

- **Desktop** : Navigation complète, grilles 3-4 colonnes
- **Tablet** : Navigation adaptée, grilles 2 colonnes
- **Mobile** : Menu burger, miniatures 2 colonnes, touch-friendly

---

## Sécurité

- ✅ Requêtes préparées PDO (anti-SQL Injection)
- ✅ `htmlspecialchars()` pour XSS protection
- ✅ CSRF tokens prêts
- ✅ Validation des entrées côté serveur
- ✅ Gestion sécurisée des sessions

---

## Installation

1. **Cloner** dans `C:\xampp\htdocs\fitness`
2. **Importer** `database/exercises_library.sql` dans phpMyAdmin
3. **Configurer** `config/db.php` avec vos identifiants MySQL
4. **Accéder** à `http://localhost/fitness`

---

## Architecture Code

### Bonnes Pratiques

- Séparation logique/fonctionnelle
- Functions réutilisables
- Commentaires en français
- Design system cohérent (Glassmorphism)
- Composants modulaires

### Patterns

- **MVC simplifié** : Logique dans `/includes`, vues dans `/pages`
- **API AJAX** : Endpoints dédiés (`*_process.php`)
- **I18n** : Fichiers de langue séparés

---

<p align="center">
  <strong>Ange-Kevin AGRE - Full Stack Developer & IA Expert</strong> </p>
