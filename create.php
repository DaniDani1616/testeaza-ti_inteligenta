<?php
session_start();

// only run when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // collect and validate POST inputs
    $titlu = trim($_POST['titlu'] ?? '');
    $nr    = trim($_POST['nrintrebari'] ?? '');
    $timp  = trim($_POST['timpacordat'] ?? '');
    $tip   = trim($_POST['tip'] ?? '');
    $age   = trim($_POST['age'] ?? '');

    // basic server‑side validation
    if ($titlu === '' || $nr === '' || $timp === '' || $tip === '' || $age === '') {
        $_SESSION['flash_error'] = 'Toate câmpurile sunt obligatorii.';
        header('Location: create.php');
        exit;
    }

    // connect
    $mysqli = require __DIR__ . '/database2.php';
    if ($mysqli->connect_errno) {
        die('Conexiune BD eșuată: ' . $mysqli->connect_error);
    }

    $sql = "INSERT INTO creation (titlu, nrintrebari, timpacordat, tip, age) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die('Eroare SQL (prepare): ' . $mysqli->error);
    }

    $stmt->bind_param('sssss', $titlu, $nr, $timp, $tip, $age);
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = 'Creat cu succes!';
        header('Location: creare_intrebari.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Eroare la crearea înregistrării.';
        header('Location: create.php');
        exit;
    }
}
// if not POST, just show the form below
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Creare Test</title>
</head>
<body>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <form action="create.php" method="post">
    <div>
      <label for="titlu">Titlul testului</label><br>
      <input type="text" id="titlu" name="titlu" required>
    </div>

    <div>
      <label for="tip">Tip test</label><br>
      <select id="tip" name="tip" required>
        <option value="" disabled selected hidden>-- Alege tipul --</option>
        <option value="IQ">IQ</option>
        <option value="PQ">PQ</option>
      </select>
    </div>

    <div>
      <label for="age">Vârstă</label><br>
      <select id="age" name="age" required>
        <option value="" disabled selected hidden>-- Alege vârsta --</option>
        <option value="10-14">10‑14</option>
        <option value="15-17">15‑17</option>
        <option value="18+">18+</option>
      </select>
    </div>

    <div>
      <label for="nrintrebari">Număr întrebări</label><br>
      <input type="number" id="nrintrebari" name="nrintrebari" required>
    </div>

    <div>
      <label for="timpacordat">Timp acordat (minute)</label><br>
      <input type="number" id="timpacordat" name="timpacordat" required>
    </div>

    <div>
      <button type="submit">Creare Test</button>
    </div>
  </form>
</body>
</html>
