<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Diagnostic Base de Données NovaGroup</title>
    <style>
        body { font-family: sans-serif; background: #0d1b2e; color: #fff; padding: 30px; line-height: 1.6; }
        .box { background: #1a2f50; border-radius: 10px; padding: 20px; max-width: 600px; margin: auto; }
        .ok { color: #2ecc71; font-weight: bold; }
        .err { color: #e74c3c; font-weight: bold; }
        code { background: #122240; padding: 2px 6px; border-radius: 4px; color: #d4b05a; }
    </style>
</head>
<body>
<div class="box">
    <h2>Diagnostic NovaGroup</h2>
    <p><b>Hôte DB :</b> <code><?= htmlspecialchars(DB_HOST) ?></code></p>
    <p><b>Base DB :</b> <code><?= htmlspecialchars(DB_NAME) ?></code></p>
    <p><b>Utilisateur DB :</b> <code><?= htmlspecialchars(DB_USER) ?></code></p>
    <hr style="border:0; border-top:1px solid #334e68; margin:15px 0;">
    <?php
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo '<p class="ok">✔ Connexion à MySQL réussie !</p>';
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            echo '<p class="err">✖ La base de données est vide ! Aucun fichier SQL n\'a été importé.</p>';
        } else {
            echo '<p class="ok">✔ Tables détectées (' . count($tables) . ') : ' . htmlspecialchars(implode(', ', $tables)) . '</p>';
            if (in_array('users', $tables)) {
                $count = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
                echo '<p class="ok">✔ Table <code>users</code> trouvée (' . $count . ' utilisateurs enregistrés).</p>';
            } else {
                echo '<p class="err">✖ Table <code>users</code> introuvable. Veuillez importer novagroup.sql dans phpMyAdmin.</p>';
            }
        }
    } catch (PDOException $e) {
        echo '<p class="err">✖ Erreur de connexion MySQL :</p>';
        echo '<pre style="background:#000; padding:10px; border-radius:6px; color:#ff6b6b; white-space:pre-wrap;">' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
    ?>
    <p style="margin-top:20px;"><a href="index.php" style="color:#d4b05a;">← Retour au site</a></p>
</div>
</body>
</html>
