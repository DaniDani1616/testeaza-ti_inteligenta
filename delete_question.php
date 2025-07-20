<?php
// delete_question.php
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
    // 1) începem tranzacția
    $pdo->beginTransaction();

    // 2) ștergem răspunsurile legate
    $stmt1 = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
    $stmt1->execute([$id]);

    // 3) ștergem întrebarea
    $stmt2 = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt2->execute([$id]);

    // 4) commit
    $pdo->commit();

} catch (Exception $e) {
    // rollback la eroare
    $pdo->rollBack();
    die("Eroare la ștergere: " . $e->getMessage());
}

// 5) verificăm câte întrebări au rămas
$count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();

// 6) redirect în funcție de rezultat
if ($count == 0) {
    header('Location: admin.php');
} else {
    header('Location: test.php');
}
exit;
