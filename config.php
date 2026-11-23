<?php
// ============================================================
//  NOVAGROUP — Configuration base de données
//  Modifiez ces valeurs selon votre hébergement
// ============================================================

// Configuration base de données automatique (Localhost & InfinityFree)
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'novagroup');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'sql105.infinityfree.com');
    define('DB_NAME', 'if0_43479942_novagroup');
    define('DB_USER', 'if0_43479942');
    define('DB_PASS', 'EmQi1jeNRGKE');
}
define('DB_CHARSET', 'utf8mb4');

// Session
define('SESSION_NAME', 'ng_session');
define('SESSION_LIFETIME', 7200); // 2 heures

// ─── Connexion PDO singleton ───────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            die(json_encode(['error' => 'Erreur de connexion à la base de données : ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ─── Session sécurisée ────────────────────────────────────
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // true en production HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// ─── Helpers ─────────────────────────────────────────────
function currentUser(): ?array {
    startSession();
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireLogin(): array {
    $u = currentUser();
    if (!$u) {
        header('Location: index.php?page=login');
        exit;
    }
    return $u;
}

function requireAdmin(): array {
    $u = requireLogin();
    if (!$u['is_admin']) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    return $u;
}

function jsonResponse(array $data, int $code = 200): never {
    // Ne pas envoyer de code 4xx/5xx sur hébergeur gratuit pour éviter qu'Apache n'écrase le JSON par du HTML
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// ─── Constantes devises ───────────────────────────────────
const COUNTRY_CURRENCY = [
    'FR'=>'EUR','DE'=>'EUR','IT'=>'EUR','ES'=>'EUR','BE'=>'EUR','NL'=>'EUR',
    'PT'=>'EUR','AT'=>'EUR','RO'=>'RON','HU'=>'HUF','HR'=>'EUR','FI'=>'EUR',
    'IE'=>'EUR','GR'=>'EUR','GB'=>'GBP','CH'=>'CHF','PL'=>'PLN','SE'=>'SEK',
    'US'=>'USD','MX'=>'USD','CA'=>'CAD','BR'=>'BRL','AR'=>'USD','CO'=>'USD',
    'BJ'=>'XOF','SN'=>'XOF','CI'=>'XOF','CM'=>'XAF','NG'=>'NGN','GH'=>'GHS',
    'MA'=>'MAD','AE'=>'AED','JP'=>'JPY','IN'=>'INR','LU'=>'EUR',
];

const CURRENCY_NAMES = [
    'EUR'=>'Euro (€)','USD'=>'Dollar américain ($)','GBP'=>'Livre sterling (£)',
    'CHF'=>'Franc suisse (CHF)','CAD'=>'Dollar canadien (CA$)','BRL'=>'Réal brésilien (R$)',
    'XOF'=>'Franc CFA UEMOA (FCFA)','XAF'=>'Franc CFA CEMAC (FCFA)','NGN'=>'Naira (₦)',
    'GHS'=>'Cedi ghanéen (GH₵)','MAD'=>'Dirham marocain (MAD)','JPY'=>'Yen japonais (¥)',
    'INR'=>'Roupie indienne (₹)','AED'=>'Dirham (AED)','PLN'=>'Złoty polonais (zł)',
    'SEK'=>'Couronne suédoise (kr)','RON'=>'Leu roumain (lei)','HUF'=>'Forint hongrois (Ft)',
];

const CURRENCY_SYMBOLS = [
    'EUR'=>'€','USD'=>'$','GBP'=>'£','CHF'=>'CHF','CAD'=>'CA$','BRL'=>'R$',
    'XOF'=>'FCFA','XAF'=>'FCFA','NGN'=>'₦','GHS'=>'GH₵','MAD'=>'MAD',
    'JPY'=>'¥','INR'=>'₹','AED'=>'AED','PLN'=>'zł','SEK'=>'kr','RON'=>'lei','HUF'=>'Ft',
];

function currencySymbol(string $code): string {
    return CURRENCY_SYMBOLS[$code] ?? $code;
}
function currencyName(string $code): string {
    return CURRENCY_NAMES[$code] ?? $code;
}
function fmtAmount(float $n, string $curr = ''): string {
    $formatted = number_format($n, 2, ',', ' ');
    return $curr ? $formatted . ' ' . currencySymbol($curr) : $formatted;
}
function maskIban(string $iban): string {
    if (strlen($iban) < 8) return $iban;
    return substr($iban, 0, 8) . ' **** **** ' . substr($iban, -4);
}
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
