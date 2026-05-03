# Agre Fitness

**Auteur :** NIONDJUI BONEHO ANGE-KEVIN AGRE  
**Hébergeur :** Agre Agency  
**URL de production :** https://agre.page.gd/fitness/

---

## Présentation

Agre Fitness est une application web PWA de suivi d'entraînement et de partage communautaire, développée par Ange-Kevin AGRE sous l'entité Agre Agency.

Elle permet à chaque utilisateur de planifier ses séances, consulter une bibliothèque de 310+ exercices vidéo, suivre sa progression et interagir avec une communauté de sportifs via un fil social, un système de follow et une messagerie privée.

---

## Fonctionnalités

- Authentification sécurisée (PDO + `password_hash`)
- Tableau de bord avec statistiques de progression
- Bibliothèque de 310+ exercices vidéo (catégorisés par muscle et difficulté)
- Journal d'entraînement
- Fil social : posts, likes, commentaires
- Système Follow / Unfollow en AJAX
- Messagerie privée en temps réel
- Recherche prédictive d'utilisateurs
- Interface multilingue (FR, EN, ES, DE, IT, PT, ZH)
- PWA installable sur mobile avec mode hors ligne

---

## Stack Technique

| Couche   | Technologie                    |
|----------|--------------------------------|
| Backend  | PHP 8.1+ (PDO, Sessions)       |
| Base     | MySQL 5.7+ — InfinityFree      |
| Frontend | HTML5, Tailwind CSS (CDN)      |
| PWA      | Web App Manifest, Service Worker |

---

## Structure

```
fitness/
├── config/db.php          # Connexion MySQL PDO
├── includes/              # Composants réutilisables (navbar, traductions)
├── pages/                 # Pages protégées (dashboard, feed, profil…)
├── assets/                # CSS, JS, images, vidéos, icônes PWA
├── database/              # Scripts SQL d'initialisation
├── index.php              # Connexion
├── signup.php             # Inscription
├── landing.php            # Landing page
└── manifest.json          # Configuration PWA
```

---

## Sécurité

- Requêtes préparées PDO (protection SQL Injection)
- `htmlspecialchars()` sur toutes les sorties (protection XSS)
- Validation des entrées côté serveur
- Gestion sécurisée des sessions PHP

---

*Agre Agency — Tous droits réservés*
