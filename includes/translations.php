<?php
/**
 * =============================================================================
 * SYSTÈME MULTILINGUE V2.0 - AGRE FITNESS
 * =============================================================================
 * Architecture modulaire avec fichiers de traduction séparés dans /includes/lang/
 * 
 * Structure :
 * - /includes/lang/fr.php : Français
 * - /includes/lang/en.php : English  
 * - /includes/lang/es.php : Español
 * - /includes/lang/de.php : Deutsch
 * - /includes/lang/it.php : Italiano
 * - /includes/lang/pt.php : Português
 * - /includes/lang/zh.php : 中文
 * 
 * Usage :
 *   require_once 'includes/translations.php';
 *   initLanguage();
 *   echo __('login_title', 'fr');
 */

// Définition des langues supportées avec leurs métadonnées
define('SUPPORTED_LANGUAGES', [
    'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'locale' => 'fr_FR', 'file' => 'fr.php'],
    'en' => ['name' => 'English', 'flag' => '🇬🇧', 'locale' => 'en_US', 'file' => 'en.php'],
    'es' => ['name' => 'Español', 'flag' => '🇪🇸', 'locale' => 'es_ES', 'file' => 'es.php'],
    'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪', 'locale' => 'de_DE', 'file' => 'de.php'],
    'it' => ['name' => 'Italiano', 'flag' => '🇮🇹', 'locale' => 'it_IT', 'file' => 'it.php'],
    'pt' => ['name' => 'Português', 'flag' => '🇵🇹', 'locale' => 'pt_PT', 'file' => 'pt.php'],
    'zh' => ['name' => '中文', 'flag' => '🇨🇳', 'locale' => 'zh_CN', 'file' => 'zh.php']
]);

// Langue par défaut
define('DEFAULT_LANGUAGE', 'fr');

// Variable globale pour stocker les traductions chargées
$GLOBALS['loadedTranslations'] = [];
$GLOBALS['currentLang'] = DEFAULT_LANGUAGE;

/**
 * Charge les traductions pour une langue donnée
 * 
 * @param string $lang Code de la langue (fr, en, es, etc.)
 * @return array Tableau des traductions
 */
function loadTranslations(string $lang): array {
    // Vérifier si la langue est supportée
    if (!isset(SUPPORTED_LANGUAGES[$lang])) {
        $lang = DEFAULT_LANGUAGE;
    }
    
    // Déjà chargée ?
    if (isset($GLOBALS['loadedTranslations'][$lang])) {
        return $GLOBALS['loadedTranslations'][$lang];
    }
    
    // Construire le chemin du fichier
    $langFile = __DIR__ . '/lang/' . SUPPORTED_LANGUAGES[$lang]['file'];
    
    // Charger le fichier s'il existe
    if (file_exists($langFile)) {
        $GLOBALS['loadedTranslations'][$lang] = require $langFile;
    } else {
        // Fallback vers français
        $GLOBALS['loadedTranslations'][$lang] = require __DIR__ . '/lang/fr.php';
    }
    
    return $GLOBALS['loadedTranslations'][$lang];
}

/**
 * Initialise la langue de l'application
 * Priorité : Cookie > Session > Navigateur > Défaut
 */
function initLanguage(): void {
    // 1. Vérifier le cookie
    if (isset($_COOKIE['lang']) && isset(SUPPORTED_LANGUAGES[$_COOKIE['lang']])) {
        $GLOBALS['currentLang'] = $_COOKIE['lang'];
    }
    // 2. Vérifier la session
    elseif (isset($_SESSION['lang']) && isset(SUPPORTED_LANGUAGES[$_SESSION['lang']])) {
        $GLOBALS['currentLang'] = $_SESSION['lang'];
    }
    // 3. Détection navigateur
    elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($browserLangs as $browserLang) {
            $langCode = substr($browserLang, 0, 2);
            if (isset(SUPPORTED_LANGUAGES[$langCode])) {
                $GLOBALS['currentLang'] = $langCode;
                break;
            }
        }
    }
    
    // Charger les traductions
    loadTranslations($GLOBALS['currentLang']);
    $_SESSION['lang'] = $GLOBALS['currentLang'];
}

/**
 * Récupère une traduction
 * 
 * @param string $key Clé de traduction
 * @param string|null $lang Code langue (null = langue courante)
 * @return string Texte traduit ou clé si non trouvée
 */
function __(string $key, ?string $lang = null): string {
    $lang = $lang ?? $GLOBALS['currentLang'];
    
    // Charger si nécessaire
    if (!isset($GLOBALS['loadedTranslations'][$lang])) {
        loadTranslations($lang);
    }
    
    // Retourner la traduction ou la clé
    return $GLOBALS['loadedTranslations'][$lang][$key] ?? $key;
}

/**
 * Alias de __() pour compatibilité
 * @deprecated Utilisez __() à la place
 */
function __t(string $key, ?string $lang = null): string {
    return __($key, $lang);
}

/**
 * Change la langue active
 * 
 * @param string $lang Code de la langue
 */
function setLanguage(string $lang): void {
    if (isset(SUPPORTED_LANGUAGES[$lang])) {
        $GLOBALS['currentLang'] = $lang;
        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, [
            'expires' => time() + (365 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        loadTranslations($lang);
    }
}

/**
 * Récupère la langue active
 * 
 * @return string Code de la langue
 */
function getCurrentLang(): string {
    return $GLOBALS['currentLang'];
}

/**
 * Récupère les informations d'une langue
 * 
 * @param string $lang Code de la langue
 * @return array|null Infos de la langue
 */
function getLanguageInfo(string $lang): ?array {
    return SUPPORTED_LANGUAGES[$lang] ?? null;
}

/**
 * Récupère toutes les langues supportées
 * 
 * @return array Liste des langues
 */
function getSupportedLanguages(): array {
    return SUPPORTED_LANGUAGES;
}

/**
 * Vérifie si une langue est supportée
 * 
 * @param string $lang Code de la langue
 * @return bool True si supportée
 */
function isLanguageSupported(string $lang): bool {
    return isset(SUPPORTED_LANGUAGES[$lang]);
}

/**
 * Génère un sélecteur de langue HTML
 * 
 * @param string $currentLang Code langue active
 * @return string HTML du sélecteur
 */
function renderLanguageSelector(string $currentLang = 'fr'): string {
    $html = '<div class="language-selector flex items-center gap-2">';
    
    foreach (SUPPORTED_LANGUAGES as $code => $info) {
        $active = ($code === $currentLang) ? 'ring-2 ring-blue-500' : 'opacity-60 hover:opacity-100';
        $title = htmlspecialchars($info['name']);
        
        $html .= sprintf(
            '<a href="?lang=%s" class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-800 %s transition-all" title="%s">%s</a>',
            $code,
            $active,
            $title,
            $info['flag']
        );
    }
    
    $html .= '</div>';
    return $html;
}

// Auto-initialisation si session active
if (session_status() === PHP_SESSION_ACTIVE) {
    initLanguage();
}
