<?php
if (empty($_POST["Numereal"])) {
    die("Numele este necesar.");
}
if (empty($_POST["Prenume"])) {
    die("Prenumele este necesar.");
}
if (empty($_POST["Nume"])) {
    die("Username-ul este necesar.");
}
if (!filter_var($_POST["email"] ?? '', FILTER_VALIDATE_EMAIL)) {
    die("Este necesar un email valid.");
}
if (strlen($_POST["Parola"] ?? '') < 8) {
    die("Sunt necesare cel puțin 8 caractere în parolă.");
}
if (!preg_match("/[a-z]/i", $_POST["Parola"])) {
    die("Parola trebuie să conțină cel puțin o literă.");
}
if (!preg_match("/[0-9]/", $_POST["Parola"])) {
    die("Parola trebuie să conțină cel puțin o cifră.");
}
if ($_POST["Parola"] !== ($_POST["CParola"] ?? '')) {
    die("Parolele nu se potrivesc.");
}
if (!preg_match("/[!@#$%^&*\\-_\+=]/", $_POST["Parola"])) {
    die("Parola trebuie să conțină cel puțin un caracter special (!@#$...).");
}
if (empty($_POST["age"])) {
    die("Introduceti varsta");
}

$password_hash = password_hash($_POST["Parola"], PASSWORD_DEFAULT);

$mysqli = require __DIR__ . "/database.php";
if (!$mysqli || $mysqli->connect_errno) {
    die("Conexiune BD eșuată: " . ($mysqli ? $mysqli->connect_error : ''));
}

function checkDuplicate($mysqli, $column, $value, $message) {
    $sql = "SELECT 1 FROM registration WHERE `$column` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Eroare internă BD.");
    }
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        die($message);
    }
    $stmt->close();
}

$sql = "INSERT INTO registration (Numereal, Prenume, Nume, email, password_hash, age) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Eroare SQL (prepare): " . $mysqli->error);
}

checkDuplicate($mysqli, 'Nume',     $_POST['Nume'],     'Username deja utilizat.');
checkDuplicate($mysqli, 'email',    $_POST['email'],    'Email deja utilizat.');

$nume_real = $_POST["Numereal"];
$prenume   = $_POST["Prenume"];
$username  = $_POST["Nume"];
$email     = $_POST["email"];
$age       = $_POST["age"];

$stmt->bind_param("ssssss", $nume_real, $prenume, $username, $email, $password_hash, $age);

if ($stmt->execute()) {
    session_start();
    $_SESSION['flash_success'] = 'Înregistrat cu succes!';
    header('Location: dashboard.php');
    exit;
}
