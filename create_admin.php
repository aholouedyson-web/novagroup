<?php
require_once __DIR__ . '/includes/config.php';

echo "<h2>Création Admin NovaGroup...</h2>";

try {
    $pdo = db();

    // 🔴 Supprimer anciens admins
    $pdo->exec("DELETE FROM users WHERE is_admin = 1");

    // ✅ Nouvel admin
    $email = "admin@gmail.com";
    $password = "123456"; // change si tu veux

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (
            first_name, last_name, dob, email, password, phone, country, iban, bank_name, currency, balance, is_admin
        ) VALUES (
            'Admin', 'Root', '1990-01-01', ?, ?, '00000000', 'BJ', 'ADMINIBAN123', 'Nova Bank', 'XOF', 0, 1
        )
    ");

    $stmt->execute([$email, $hash]);

    echo "<p style='color:green;'>✅ Admin créé avec succès !</p>";
    echo "<p>Email : <b>$email</b></p>";
    echo "<p>Mot de passe : <b>$password</b></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
}