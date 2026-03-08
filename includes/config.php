<?php
// ============================================
// CONFIGURATION BASE DE DONNÉES
// ============================================
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Log PHP errors into project file to inspect server-side errors from this workspace
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_error.log');

define('DB_HOST', 'localhost');
define('DB_NAME', 'agence_immo');
define('DB_USER', 'root');       // Modifier selon votre config
define('DB_PASS', '');           // Modifier selon votre config
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'ImmoAgence');
define('SITE_URL', 'http://192.168.56.1/immo');
define('UPLOAD_DIR', __DIR__ . '/../uploads/biens/');
define('UPLOAD_URL', SITE_URL . '/uploads/biens/');
define('MAX_PHOTOS', 10);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connexion base de données impossible: ' . $e->getMessage()]));
}

// Démarrage session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Fonctions utilitaires globales
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function requireClient(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/client/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function isGestionnaire(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'gestionnaire';
}

/**
 * Autorise l'accès aux pages admin ou gestionnaire.
 * Les gestionnaires peuvent gérer biens et demandes, mais n'ont pas accès
 * à la section utilisateurs.
 */
function requireAdminOrManager(): void {
    if (!isAdmin() && !isGestionnaire()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function formatPrix(float $prix): string {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}

function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getPhotos(array $bien): array {
    $photos = [];
    for ($i = 1; $i <= 10; $i++) {
        $key = 'photo' . $i;
        if (!empty($bien[$key])) {
            $photos[] = $bien[$key];
        }
    }
    return $photos;
}

function getPhotoUrl(string $photo): string {
    return UPLOAD_URL . $photo;
}

function getDefaultPhoto(): string {
    return SITE_URL . '/assets/images/no-photo.jpg';
}
?>
