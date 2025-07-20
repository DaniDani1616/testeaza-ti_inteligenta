<?php
session_start();

// Ștergem toate variabilele de sesiune
$_SESSION = array();

// Ștergem cookie-ul de sesiune
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Distrugem sesiunea
session_destroy();

// Redirecționăm către homepage
header("Location: index.php");
exit;
?>