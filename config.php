<?php
// Conexiune PDO la MySQL
$host = 'sql206.infinityfree.com';
$db   = 'if0_39518451_creare_teste';
$user = '	if0_39518451';
$pass = 'Dani2008Fotbal';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Afișează o eroare în mediu de dezvoltare
    die("Eroare conexiune DB: " . $e->getMessage());
}
?>
