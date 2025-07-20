<?php
session_start();
require __DIR__ . '/database.php';  // înlocuiește cu config.php dacă folosești PDO

// 1) verifică dacă ești autentificat
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 2) Șterge întâi toate intrările din tabelele copil

    // 2.1) share_links
    $stmt = $mysqli->prepare("DELETE FROM share_links WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // 2.2) alte tabele dependente, de exemplu:
    // $stmt = $mysqli->prepare("DELETE FROM answers WHERE user_id = ?");
    // $stmt->bind_param('i', $user_id);
    // $stmt->execute();

    // adaugă aici și alte DELETE-uri dacă mai ai tabele cu FK către registration.id

    // 3) Șterge în final contul din registration
    $stmt = $mysqli->prepare("DELETE FROM registration WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // 4) Golește și distruge sesiunea
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();

    // 5) Redirect către login / pagină de confirmare
    header('Location: login.php?account_deleted=1');
    exit;

} catch (Exception $e) {
    // în caz de eroare, setează un flash și redirecționează înapoi
    $_SESSION['flash_error'] = 'Nu am putut șterge contul: ' . $e->getMessage();
    header('Location: dashboard.php');
    exit;
}
