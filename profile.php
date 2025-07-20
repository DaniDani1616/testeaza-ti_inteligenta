<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$mysqli = require __DIR__ . '/database.php';
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("
    SELECT Numereal, Prenume, Nume, email, profile_pic,age
      FROM registration
     WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Utilizator inexistent.');
}
$user = $res->fetch_assoc();
$stmt->close();
if($user['age']=="10-14")
  $X="10-14";
if($user['age']=="15-17")
  $X="15-17";
if($user['age']=="18+")
  $X="18+";
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $real    = trim($_POST['Numereal']   ?? '');
    $prenume = trim($_POST['Prenume']    ?? '');
    $nume    = trim($_POST['Nume']       ?? '');
    $email   = trim($_POST['email']      ?? '');
    $pass    = $_POST['Parola']         ?? '';
    $pass2   = $_POST['CParola']        ?? '';
    $theme   = $_POST['theme']          ?? 'default';

    if ($real === '')    { $errors[] = 'Trebuie să completezi numele real.'; }
    if ($prenume === '') { $errors[] = 'Trebuie să completezi prenumele.'; }
    if (strlen($nume) < 3) {
        $errors[] = 'Username-ul trebuie să aibă cel puțin 3 caractere.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalid.';
    }
    if (!isset($themes[$theme])) {
        $errors[] = 'Tema selectată este invalidă.';
    }
    $newHash = null;
    if ($pass !== '') {
        if (strlen($pass) < 8) {
            $errors[] = 'Parola trebuie să aibă cel puțin 8 caractere.';
        }
        if (!preg_match('/[a-z]/i', $pass) || !preg_match('/[0-9]/', $pass)
         || !preg_match('/[!@#$%^&*\-_\+=]/', $pass)) {
            $errors[] = 'Parola trebuie să conțină literă, cifră și caracter special.';
        }
        if ($pass !== $pass2) {
            $errors[] = 'Parolele nu se potrivesc.';
        }
        if (empty($errors)) {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
        }
    }
    function checkDup($mysqli, $col, $val, $selfId) {
        $st = $mysqli->prepare("SELECT 1 FROM registration WHERE `$col` = ? AND id <> ? LIMIT 1");
        $st->bind_param('si', $val, $selfId);
        $st->execute();
        $st->store_result();
        $dup = $st->num_rows > 0;
        $st->close();
        return $dup;
    }
    if (empty($errors)) {
        if ($nume !== $user['Nume'] && checkDup($mysqli, 'Nume', $nume, $user_id)) {
            $errors[] = 'Username-ul este deja folosit.';
        }
        if ($email !== $user['email'] && checkDup($mysqli, 'email', $email, $user_id)) {
            $errors[] = 'Email-ul este deja folosit.';
        }
    }

    $profilePath = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $target = "uploads/profile/{$user_id}." . $ext;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $profilePath = $target;
        } else {
            $errors[] = 'Eroare la upload-ul pozei de profil.';
        }
    }

    if (empty($errors)) {
        $fields = "Numereal = ?, Prenume = ?, Nume = ?, email = ?, profile_pic = ?";
        $types  = "sssss";
        $params = [$real, $prenume, $nume, $email, $profilePath, $coverPath, $theme];

        if ($newHash) {
            $fields .= ", password_hash = ?";
            $types  .= "s";
            $params[] = $newHash;
        }
        $types   .= "i";
        $params[] = $user_id;

        $sql = "UPDATE registration SET $fields WHERE id = ?";
        $upd = $mysqli->prepare($sql);
        $upd->bind_param($types, ...$params);

        if ($upd->execute()) {
            $success = 'Profilul a fost actualizat cu succes.';
            $user = [
                'Numereal'   => $real,
                'Prenume'    => $prenume,
                'Nume'       => $nume,
                'email'      => $email,
                'profile_pic'=> $profilePath,
                'cover_pic'  => $coverPath,
                'theme'      => $theme
            ];
        } else {
            $errors[] = 'Eroare la salvare: ' . $mysqli->error;
        }
        $upd->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profilul Meu</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --card-bg: rgba(255,255,255,0.3);
      --card-blur: 10px;
      --text-color: #fff;
      --btn-text: #333;
      --card-border: rgba(255,255,255,0.5);
      --sidebar-bg: rgba(30, 30, 60, 0.85);
      --sidebar-width: 280px;
      --sidebar-icon-size: 24px;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    html, body { height:100%; }
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      font-family: 'Segoe UI','Poppins',sans-serif;
      color: var(--text-color);
      overflow-x:hidden;
      display: flex;
      flex-direction: column;
    }
    /* Flash message */
    #flash-message {
      position: fixed; top:20px; left:50%; transform:translateX(-50%);
      background:#e6ffed; color:#2e7d32;
      padding:12px 24px; border:1px solid #a5d6a7; border-radius:4px;
      opacity:0; transition:opacity .3s; z-index:1000;
      box-shadow:0 2px 6px rgba(0,0,0,0.2); font-weight:500;
    }
    #flash-message.show { opacity:1; }

    header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 15px 30px; margin: 20px;
      background: rgba(255,255,255,0.2); border-radius:16px;
      backdrop-filter: blur(10px); box-shadow:0 4px 20px rgba(0,0,0,0.1);
      border:1px solid var(--card-border); position: relative; z-index:50;
    }
    .nav-icons { display:flex; gap:15px; }
    .nav-icons button {
      background:rgba(255,255,255,0.3); border:none;
      width:44px; height:44px; border-radius:10px;
      cursor:pointer; display:flex; align-items:center;
      justify-content:center; transition:all .3s;
    }
    .nav-icons button:hover {
      transform:translateY(-2px);
      background:rgba(255,255,255,0.4);
    }
    .nav-icons svg { width:22px; height:22px; fill:white; transition:transform .3s; }
    .nav-icons button:hover svg { transform:scale(1.1); }

    #user-info {
      display:flex; align-items:center; gap:10px;
      background:rgba(255,255,255,0.7); color:#333;
      padding:10px 20px; border-radius:50px; font-weight:600;
    }
    #user-info img {
      width:48px; height:48px; border-radius:50%;
      object-fit:cover; border:2px solid white;
    }

    main {
      display:flex; flex:1; overflow:hidden;
    }
    /* Sidebar */
    .sidebar {
      width: var(--sidebar-width); background: var(--sidebar-bg);
      backdrop-filter: blur(10px); padding:20px 0; height:100%;
      overflow-y:auto; transition:transform .3s; box-shadow:3px 0 15px rgba(0,0,0,0.2);
      position: relative; z-index:40;
    }
    .sidebar.active { transform: translateX(0); }
    .sidebar-header { padding:0 20px 20px; border-bottom:1px solid rgba(255,255,255,0.1); }
    .user-profile { display:flex; align-items:center; gap:15px; }
    .user-avatar {
      width:60px; height:60px; border-radius:50%; overflow:hidden;
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      display:flex;align-items:center;justify-content:center;
      font-size:24px;font-weight:bold;color:#333;
    }
    .user-avatar img { width:100%; height:100%; object-fit:cover; }
    .user-details { flex:1; }
    .user-name { font-weight:600; font-size:18px; margin-bottom:5px; }
    .user-role {
      font-size:14px; opacity:.8; background:rgba(255,255,255,0.1);
      padding:3px 8px; border-radius:20px; display:inline-block;
    }
    .sidebar-menu { list-style:none; padding:0; margin:20px 0; }
    .menu-title { text-transform:uppercase; font-size:12px; padding:10px 20px; opacity:.6; }
    .menu-item { padding:0 15px; }
    .menu-link {
      display:flex; align-items:center; padding:12px 20px;
      color:white; text-decoration:none; border-radius:8px;
      margin-bottom:5px; transition:all .3s;
    }
    .menu-link:hover, .menu-link.active { background:rgba(255,255,255,0.15); }
    .menu-icon {
      width:var(--sidebar-icon-size); height:var(--sidebar-icon-size);
      display:flex; align-items:center; justify-content:center;
      margin-right:15px;
    }
    .menu-badge {
      background: var(--btn-grad-start); color:#333;
      border-radius:20px; padding:2px 8px; font-size:12px;
      font-weight:600; margin-left:auto;
    }
    .has-submenu .submenu {
      max-height:0; overflow:hidden;
      transition:max-height 0.3s ease; padding-left:20px;
    }
    .has-submenu.active .submenu { max-height:500px; }
    .menu-arrow { margin-left:auto; transition:transform 0.3s ease; }
    .has-submenu.active .menu-arrow i { transform:rotate(180deg); }

    /* Content */
    .content {
      flex:1; padding:30px; overflow-y:auto;
    }
    .content-header { margin-bottom:30px; }
    .content-title { font-size:28px; font-weight:600; margin-bottom:10px; }
    .content-subtitle { opacity:.8; }

    .card {
      background:var(--card-bg); backdrop-filter:blur(var(--card-blur));
      border:1px solid var(--card-border); border-radius:15px;
      padding:25px; transition:transform .3s,box-shadow .3s;
      margin-bottom:20px;
    }
    .card:hover {
      transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.1);
    }
    .avatar-container {
      display:flex; flex-direction:column; align-items:center;
      margin-bottom:20px;
    }
    .avatar-large {
      width:120px; height:120px; border-radius:50%;
      object-fit:cover; border:5px solid rgba(255,255,255,0.3);
      margin-bottom:15px;
    }
    .avatar-upload { position:relative; display:inline-block; }
    .avatar-upload input {
      position:absolute; left:0; top:0; opacity:0;
      width:100%; height:100%; cursor:pointer;
    }
    .avatar-upload-btn {
      background:rgba(255,255,255,0.25); color:white;
      padding:8px 15px; border-radius:20px; font-size:14px;
      cursor:pointer; transition:background 0.3s;
    }
    .avatar-upload-btn:hover { background:rgba(255,255,255,0.35); }

    .form-group { position:relative; margin-bottom:25px; }
    .form-control {
      width:100%; padding:15px; font-size:16px;
      border:none; border-radius:10px;
      background:rgba(255,255,255,0.25); color:white;
      transition:all 0.3s;
    }
    .form-control:focus { outline:none; background:rgba(255,255,255,0.35); }
    .form-label {
      position:absolute; top:15px; left:15px; pointer-events:none;
      transition:all 0.3s; color:rgba(255,255,255,0.8);
    }
    .form-control:focus ~ .form-label,
    .form-control:not(:placeholder-shown) ~ .form-label {
      top:-22px; left:0; font-size:14px; color:var(--text-color);
    }

    .btn-submit {
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      color:var(--btn-text); border:none; padding:15px 30px;
      border-radius:50px; font-weight:600; font-size:16px;
      cursor:pointer; transition:all 0.3s; display:block;
      width:100%; margin-top:20px;
    }
    .btn-submit:hover {
      transform:translateY(-3px);
      box-shadow:0 5px 15px rgba(0,0,0,0.2);
    }

    .error {
      color:#ff6b6b; background:rgba(255,107,107,0.1);
      padding:15px; border-radius:10px; margin-bottom:20px;
    }
    .success {
      color:#69db7c; background:rgba(105,219,124,0.1);
      padding:15px; border-radius:10px; margin-bottom:20px;
    }

    /* Mobile menu */
    .mobile-menu-btn {
      display: none;
      position: fixed;
      top: 25px;
      left: 20px;
      background: rgba(255,255,255,0.3);
      border: none;
      border-radius: 8px;
      width: 44px;
      height: 44px;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
      cursor: pointer;
      z-index: 200;
    }
    
    @media (max-width: 992px) {
      .mobile-menu-btn {
        display: flex;
      }
      .sidebar {
        position: fixed;
        transform: translateX(-100%);
      }
      .sidebar.active {
        transform: translateX(0);
      }
    }
    /* Chatbot & theme/lang */
    .chatbot {
      position:fixed; bottom:25px; right:25px; width:60px; height:60px;
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      border-radius:50%; display:flex; align-items:center; justify-content:center;
      font-size:28px; color:#333; box-shadow:0 5px 15px rgba(0,0,0,0.3);
      cursor:pointer; z-index:100; transition:all .3s;
    }
    .chatbot:hover { transform:scale(1.1) rotate(5deg); }

    #theme-btn {
      position:fixed; bottom:25px; left:25px; width:55px; height:55px;
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      border:none; border-radius:50%; cursor:pointer; font-size:22px;
      color:var(--btn-text); box-shadow:0 5px 10px rgba(0,0,0,0.2);
      z-index:100; transition:all .3s;
    }
    #theme-btn:hover {
      transform:rotate(15deg) scale(1.1);
      box-shadow:0 8px 15px rgba(0,0,0,0.25);
    }

    #theme-selector {
      position:fixed; bottom:95px; left:25px;
      background:rgba(255,255,255,0.95); padding:15px;
      border-radius:15px; display:none;
      box-shadow:0 8px 20px rgba(0,0,0,0.2);
      border:1px solid rgba(0,0,0,0.1);
      z-index:100;
    }
    #theme-selector button {
      margin:8px; padding:10px 15px;
      border:none; border-radius:8px; cursor:pointer;
      font-size:.95rem; font-weight:600; transition:all .2s;
    }
    #theme-selector button:hover {
      transform:translateY(-2px);
      box-shadow:0 3px 8px rgba(0,0,0,0.15);
    }

    .lang-toggle {
      position:fixed; bottom:25px; left:100px;
      background:rgba(255,255,255,0.25); border-radius:50px;
      padding:6px; display:flex;
      backdrop-filter:blur(6px);
      box-shadow:0 3px 8px rgba(0,0,0,0.1);
      border:1px solid rgba(255,255,255,0.3);
      z-index:100;
    }
    .lang-btn {
      padding:10px 18px; border:none; border-radius:50px;
      background:transparent; color:var(--text-color);
      cursor:pointer; font-weight:600; transition:all .3s;
      font-size:.95rem;
    }
    .lang-btn.active {
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      color:var(--btn-text); box-shadow:0 3px 8px rgba(0,0,0,0.15);
    }
    .lang-btn:hover:not(.active) { background:rgba(255,255,255,0.15); }
    #flash-message2 {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 99, 71, 0.95);
    color: #fff;
    padding: 12px 20px;
    border-radius: 4px;
    font-family: sans-serif;
    display: none;
    z-index: 1000;
  }
  #flash-message3 {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 99, 71, 0.95);
    color: #fff;
    padding: 12px 20px;
    border-radius: 4px;
    font-family: sans-serif;
    display: none;
    z-index: 1000;
  }
</style>

<div id="flash-message2">Îmi pare rău, testul este disponibil doar celor peste 15 ani.</div>
<div id="flash-message3">Îmi pare rău, testul este disponibil doar celor peste 18 ani.</div>

</head>
<body>

  <?php if ($success): ?>
    <div id="flash-message" class="success show"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div id="flash-message" class="error show">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Mobile menu toggle -->
  <button class="mobile-menu-btn" id="mobileMenuBtn">
    <i class="fas fa-bars"></i>
  </button>

  <header>
    <div class="nav-icons">
      <button title="Acasă"><a href="home.php"><svg viewBox="0 0 576 512"><path d="M280.37 148.26L96 300.11V464a16 16 0 0016 16l112-.29a16 16 0 0016-15.74V368a16 16 0 0116-16h64a16 16 0 0116 16v96a16 16 0 0016 16l112 .29a16 16 0 0016-16V300L295.67 148.26a12.19 12.19 0 00-15.3 0z"/></svg></a></button>
      <button title="Contact" class="nav-btn" onclick="location.href='informatii.php'">
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 448 512"
    width="24"
    height="24"
    fill="currentColor"
  >
    <path d="M224 256A128 128 0 1 0 224 0a128 128 0 0 0 0 256zm89.6 32h-11.2c-22.9 10.3-48.4 16-74.4 16s-51.5-5.7-74.4-16h-11.2C60.6 288 0 348.6 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-73.8-60.6-134.4-134.4-134.4z"/>
  </svg>
</button>
    </div>
    <div id="user-info">
      <?php if (!empty($user['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
      <?php endif; ?>
      <span>@<?= htmlspecialchars($user['Nume']) ?></span>
    </div>
  </header>

  <main>
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="user-profile">
          <div class="user-avatar">
            <?php if (!empty($user['profile_pic'])): ?>
              <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
            <?php else: ?>
              <?= htmlspecialchars(substr($user['Numereal'],0,1)) ?>
            <?php endif; ?>
          </div>
          <div class="user-details">
            <div class="user-name"><?= htmlspecialchars($user['Prenume'] . ' ' . $user['Numereal']) ?></div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
      </div>
      <ul class="sidebar-menu">
        <li class="menu-title">Navigare Principală</li>
        <li class="menu-item"><a href="dashboard.php" class="menu-link"><div class="menu-icon"><i class="fas fa-home"></i></div><div class="menu-text">Acasă</div></a></li>
        <li class="menu-item"><a href="profile.php" class="menu-link active"><div class="menu-icon"><i class="fas fa-user"></i></div><div class="menu-text">Editare Profil</div></a></li>
        <li class="menu-title">Acțiuni</li>
        <li class="menu-item"><a href="admin.php" class="menu-link"><div class="menu-icon"><i class="fas fa-plus-circle"></i></div><div class="menu-text">Creează</div></a></li>
        <li class="menu-item has-submenu" id="tests-submenu">
          <a href="#" class="menu-link main-test-link">
            <div class="menu-icon"><i class="fas fa-file-alt"></i></div>
            <div class="menu-text">Teste</div>
            <div class="menu-badge">4</div>
            <div class="menu-arrow"><i class="fas fa-chevron-down"></i></div>
          </a>
          <ul class="submenu">
            <li class="menu-item"><a href="index.php" class="menu-link test-link"><div class="menu-icon"><i class="fas fa-play"></i></div><div class="menu-text">Test PQ</div></a></li>
            <li class="menu-item"><a href="test10-14.php" class="menu-link test-link"><div class="menu-icon"><i class="fas fa-play"></i></div><div class="menu-text">Test IQ 10-14 ani</div></a></li> 
            <?php if ($X=="15-17" || $X=="18+"): ?>
  <li class="menu-item">
    <a href="test15-17.php" class="menu-link test-link">
      <div class="menu-icon"><i class="fas fa-play"></i></div>
      <div class="menu-text">Test IQ 15-17 ani</div>
    </a>
  </li>
<?php else: ?>
  <li class="menu-item">
    <a href="#" id="blocked-test-link1" class="menu-link test-link">
      <div class="menu-icon"><i class="fas fa-play"></i></div>
      <div class="menu-text">Test IQ 15-17 ani</div>
    </a>
  </li>
<?php endif; ?>

<?php if ($X=="18+"): ?><li class="menu-item"><a href="test18.php" class="menu-link test-link"><div class="menu-icon"><i class="fas fa-play"></i></div><div class="menu-text">Test IQ 18+ ani</div></a></li>
  <?php else: ?><li class="menu-item"><a href="#" id="blocked-test-link2" class="menu-link test-link"><div class="menu-icon"><i class="fas fa-play"></i></div><div class="menu-text">Test IQ 18+ ani</div></a></li><?php endif; ?>
          </ul>
        </li>
        <li class="menu-item"><a href="teste_rezolvate.php" class="menu-link"><div class="menu-icon"><i class="fas fa-check-circle"></i></div><div class="menu-text">Teste Rezolvate</div></a></li>
        <li class="menu-title">Administrare cont</li>
    <li class="menu-item">
      <a href="javascript:;" onclick="if(confirm('Ești sigur că vrei să-ți ștergi contul? Această operațiune este ireversibilă.')) location.href='delete_account.php';" class="menu-link">
       <div class="menu-icon"><i class="fas fa-user-slash"></i></div>
       <div class="menu-text">Șterge contul</div>
     </a>
   </li>
        <li class="menu-item"><a href="logout.php" class="menu-link"><div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div><div class="menu-text">Ieșire</div></a></li>
      </ul>
    </aside>

    <section class="content">
      <div class="content-header">
        <h1 class="content-title">Editare Profil</h1>
        <p class="content-subtitle">Actualizează informațiile personale și preferințele</p>
      </div>
      
      <div class="card">
        <form method="post" enctype="multipart/form-data">
          <div class="avatar-container">
            <?php if (!empty($user['profile_pic'])): ?>
              <img class="avatar-large" src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
            <?php else: ?>
            <div class="user-avatar">
              <?= htmlspecialchars(substr($user['Numereal'],0,1)) ?></div>
            <?php endif; ?>
            <div class="avatar-upload">
              <button type="button" class="avatar-upload-btn">Schimbă poza</button>
              <input type="file" name="profile_pic" accept="image/*">
            </div>
          </div>

          <div class="form-group">
            <input type="text" name="Numereal" class="form-control" value="<?= htmlspecialchars($user['Numereal']) ?>" placeholder=" " required>
            <label class="form-label">Nume real</label>
          </div>
          
          <div class="form-group">
            <input type="text" name="Prenume" class="form-control" value="<?= htmlspecialchars($user['Prenume']) ?>" placeholder=" " required>
            <label class="form-label">Prenume</label>
          </div>
          
          <div class="form-group">
            <input type="text" name="Nume" class="form-control" value="<?= htmlspecialchars($user['Nume']) ?>" placeholder=" " required>
            <label class="form-label">Username</label>
          </div>
          
          <div class="form-group">
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" placeholder=" " readonly>
            <label class="form-label">Email</label>
          </div>
          
          <div class="form-group">
            <input type="password" name="Parola" class="form-control" placeholder=" ">
            <label class="form-label">Parolă nouă (opțional)</label>
          </div>
          
          <div class="form-group">
            <input type="password" name="CParola" class="form-control" placeholder=" ">
            <label class="form-label">Confirmă parola</label>
          </div>
          <button type="submit" class="btn-submit">Salvează modificările</button>
        </form>
      </div>
    </section>
  </main>
  <button id="theme-btn">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Blue</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Orange</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Green</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Gold</button>
  </div>
  <script>
    (function(){
      const msg = document.getElementById('flash-message');
      if (!msg) return;
      requestAnimationFrame(()=> msg.classList.add('show'));
      setTimeout(()=>{ msg.classList.remove('show'); setTimeout(()=>msg.remove(),300); }, 4000);
    })();
    document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('blocked-test-link1');
    const flash = document.getElementById('flash-message2');

    if (link) {
      link.addEventListener('click', e => {
        e.preventDefault();            
        flash.style.display = 'block'; 

        setTimeout(() => {
          flash.style.display = 'none';
        }, 3000);
      });
    }
  });
  document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('blocked-test-link2');
    const flash = document.getElementById('flash-message3');

    if (link) {
      link.addEventListener('click', e => {
        e.preventDefault();           
        flash.style.display = 'block'; 

        setTimeout(() => {
          flash.style.display = 'none';
        }, 3000);
      });
    }
  });
    const themeBtn = document.getElementById('theme-btn'),
          themeSel = document.getElementById('theme-selector');
    themeBtn.addEventListener('click', ()=> themeSel.style.display = themeSel.style.display==='block'?'none':'block');
    themeSel.querySelectorAll('button').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        document.documentElement.style.setProperty('--gradient-1', btn.dataset.g1);
        document.documentElement.style.setProperty('--gradient-2', btn.dataset.g2);
        document.documentElement.style.setProperty('--btn-grad-start', btn.dataset.b1);
        document.documentElement.style.setProperty('--btn-grad-end', btn.dataset.b2);
        themeSel.style.display='none';
      });
    });
    document.addEventListener('click', e=>{
      if (!themeSel.contains(e.target)&&e.target!==themeBtn) themeSel.style.display='none';
    });

    document.querySelectorAll('.theme-option').forEach(option => {
      option.addEventListener('click', () => {
        document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
        document.getElementById('selected-theme').value = option.dataset.theme;
      });
    });

    document.querySelectorAll('.lang-btn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.lang-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

     const mobileBtn = document.getElementById('mobileMenuBtn'),
          sidebar = document.getElementById('sidebar');
    mobileBtn.addEventListener('click', () => sidebar.classList.toggle('active'));
    document.addEventListener('click', e => {
        if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    const testsSubmenu = document.getElementById('tests-submenu');
    testsSubmenu.querySelector('.main-test-link').addEventListener('click', e=>{
      e.preventDefault();
      testsSubmenu.classList.toggle('active');
    });

    document.querySelectorAll('.form-control').forEach(input => {
      if (input.value) {
        const lbl = input.nextElementSibling;
        lbl.style.top = '-22px';
        lbl.style.fontSize = '14px';
      }
    });
  </script>
</body>
</html>
