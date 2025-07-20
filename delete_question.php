<?php

session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID întrebării invalid.');
}

$id = (int)$_GET['id'];

try {
   
    $pdo->beginTransaction();

 
    $stmt1 = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
    $stmt1->execute([$id]);

 
    $stmt2 = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt2->execute([$id]);

 
    $pdo->commit();

} catch (Exception $e) {
 
    $pdo->rollBack();
    die("Eroare la ștergere: " . $e->getMessage());
}


$count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();


if ($count == 0) {
    header('Location: admin.php');
} else {
    header('Location: test.php');
}
exit;
