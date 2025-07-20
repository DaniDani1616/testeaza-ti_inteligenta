<?php
session_start();
require __DIR__ . '/database.php'; 


if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
  
    $stmt = $mysqli->prepare("DELETE FROM share_links WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();


    $stmt = $mysqli->prepare("DELETE FROM registration WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();


    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();

   
    header('Location: login.php?account_deleted=1');
    exit;

} catch (Exception $e) {
   
    $_SESSION['flash_error'] = 'Nu am putut șterge contul: ' . $e->getMessage();
    header('Location: dashboard.php');
    exit;
}
