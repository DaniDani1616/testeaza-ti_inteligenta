<?php
session_start();
$display_name = '';
$avatar_letter = '';
$is_logged_in = false;

if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Conectare la baza de date
$mysqli = require __DIR__ . '/database.php';
// Obținere date utilizator (inclusiv profile_pic, theme)
$user_id = $_SESSION['user_id'];
$sql = "SELECT Numereal, Prenume, Nume, email, profile_pic,age
      FROM registration
     WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if($user['age']=="10-14")
  $X="10-14";
if($user['age']=="15-17")
  $X="15-17";
if($user['age']=="18+")
  $X="18+";
// Nume complet
$fullname = trim("{$user['Prenume']} {$user['Numereal']}");

// Tema curentă
$theme = $user['theme'] ?? 'default';
$test_message     = '';
$test_results     = [];
$test_dominants   = [];
$typeInfo = [
  'M' => [
      'title' => 'Mizantropul',
      'description' => 'Mizantropul are o perspectivă sceptică asupra oamenilor și a intențiilor lor. 
          Caracteristici principale:
          - Încredere limitată în ceilalți
          - Tendința de a controla situațiile
          - Convingerea că interesul personal motivează majoritatea acțiunilor
          - Preferința pentru autonomie și independență',
      'strengths' => ['Realism', 'Precauție', 'Autosuficiență'],
      'weaknesses' => ['Cinicism', 'Distanță emoțională', 'Pesimism'],
      'quote' => '"Încrederea se câștigă picătură cu picătură, dar se pierde ca un potop."'
  ],
  'N' => [
      'title' => 'Naivul',
      'description' => 'Naivul trăiește într-o lume idealizată, văzând doar binele în oameni. 
          Caracteristici principale:
          - Încredere excesivă și necondiționată
          - Optimism exagerat
          - Convingerea că toate lucrurile se vor rezolva de la sine
          - Lipsă de realism în evaluarea riscurilor',
      'strengths' => ['Deschidere', 'Compasiune', 'Încredere'],
      'weaknesses' => ['Vulnerabilitate', 'Lipsă de discernământ', 'Deziluzie'],
      'quote' => '"Văd binele în toți oamenii, chiar și atunci când nu-l merită."'
  ],
  'O' => [
      'title' => 'Optimistul',
      'description' => 'Optimistul găsește echilibrul între realism și speranță. 
          Caracteristici principale:
          - Perspective echilibrate asupra oamenilor
          - Abilitatea de a vedea oportunități în dificultăți
          - Încredere moderată, dar nu oarbă
          - Reziliență în fața obstacolelor',
      'strengths' => ['Reziliență', 'Flexibilitate', 'Pozitivitate'],
      'weaknesses' => ['Subestimarea riscurilor', 'Tendința de a ignora semnalele negative'],
      'quote' => '"Fiecare obstacol este o oportunitate deghizată."'
  ]
];
if (isset($_SESSION['test_results'])) {
  $test_message   = $_SESSION['test_results']['message'];
  $test_results   = $_SESSION['test_results']['results'];
  $test_dominants = $_SESSION['test_results']['dominantTypes'];
  // Dacă nu vrei să mai rămână în sesiune
  // unset($_SESSION['test_results']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Rezolvate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --primary-accent: #ff9d00;
      --text-color: #fff;
      --btn-text: #333;
      --card-bg: rgba(255, 255, 255, 0.2);
      --card-border: rgba(255, 255, 255, 0.3);
      --form-bg: rgba(255, 255, 255, 0.1);
      --sidebar-bg: rgba(30, 30, 60, 0.85);
      --sidebar-width: 280px;
      --sidebar-icon-size: 24px;
      --card-blur: 10px;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    html, body { height:100%; }
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      font-family: 'Segoe UI', 'Poppins', sans-serif;
      color: var(--text-color);
      overflow-x:hidden;
      display: flex;
      flex-direction: column;
    }
    .has-submenu .submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      padding-left: 20px;
    }
    .has-submenu.active .submenu {
      max-height: 500px; /* suficient cât să arate toate elementele */
    }
    .menu-arrow {
      margin-left: auto;
      transition: transform 0.3s ease;
    }
    .has-submenu.active .menu-arrow i {
      transform: rotate(180deg);
    } 

    /* Header */
    header {
      display: flex; 
      align-items: center; 
      justify-content: space-between;
      padding: 15px 30px; 
      margin: 20px;
      background: rgba(255,255,255,0.2); 
      border-radius: 16px;
      backdrop-filter: blur(10px); 
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: 1px solid var(--card-border); 
      position: relative; 
      z-index: 50;
    }
    .nav-icons { 
      display: flex; 
      gap: 15px; 
    }
    .nav-icons button {
      background: rgba(255,255,255,0.3); 
      border: none; 
      width: 44px; 
      height: 44px;
      border-radius: 10px; 
      cursor: pointer; 
      display: flex; 
      align-items: center;
      justify-content: center; 
      transition: all .3s;
    }
    .nav-icons button:hover { 
      transform: translateY(-2px); 
      background: rgba(255,255,255,0.4);
    }
    .nav-icons svg { 
      width: 22px; 
      height: 22px; 
      fill: white; 
      transition: transform .3s; 
    }
    .nav-icons button:hover svg{ 
      transform: scale(1.1); 
    }#user-info {
      display:flex; align-items:center; gap:10px;
      background: rgba(255,255,255,0.7); color:#333;
      padding:10px 20px; border-radius:50px; font-weight:600;
    }
    #user-info img {
      width:48px; height:48px; border-radius:50%; object-fit:cover;
      border:2px solid white;
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
    .user-avatar img {
      width:100%; height:100%; object-fit:cover;
    }
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
      display:flex; align-items:center; padding:12px 20px; color:white;
      text-decoration:none; border-radius:8px; margin-bottom:5px;
      transition:all .3s;
    }
    .menu-link:hover, .menu-link.active { background:rgba(255,255,255,0.15); }
    .menu-icon { width:var(--sidebar-icon-size); height:var(--sidebar-icon-size);
      display:flex; align-items:center; justify-content:center; margin-right:15px;
    }
    .menu-badge {
      background: var(--btn-grad-start); color:#333; border-radius:20px;
      padding:2px 8px; font-size:12px; font-weight:600; margin-left:auto;
    }
    .has-submenu .submenu {
      max-height:0; overflow:hidden; transition:max-height .3s; padding-left:20px;
    }
    .has-submenu.active .submenu { max-height:500px; }
    .menu-arrow { margin-left:auto; transition:transform .3s; }
    .has-submenu.active .menu-arrow { transform:rotate(180deg); }

    /* Content */
    .content {
      flex: 1; 
      padding: 30px; 
      overflow-y: auto;
    }
    .content-header { 
      margin-bottom: 30px; 
    }
    .content-title { 
      font-size: 28px; 
      font-weight: 600; 
      margin-bottom: 10px; 
    }
    .content-subtitle { 
      opacity: .8; 
    }

    .card-grid {
      display: grid; 
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px; 
      margin-bottom: 30px;
    }
    .card {
      background: var(--card-bg); 
      backdrop-filter: blur(var(--card-blur));
      border: 1px solid var(--card-border); 
      border-radius: 15px;
      padding: 25px; 
      transition: transform .3s, box-shadow .3s;
      position: relative;
      overflow: hidden;
      min-height: 300px;
    }
    .card:hover {
      transform: translateY(-5px); 
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .card-content { 
      position: absolute; 
      top: 0; 
      left: 0;
      width: 100%; 
      height: 100%; 
      padding: 25px;
      opacity: 0; 
      transition: opacity 0.5s; 
      color: var(--btn-text);
      background: rgba(255,255,255,0.9);
      display: flex; 
      flex-direction: column; 
      justify-content: center;
      backdrop-filter: blur(5px);
    }
    .card.revealed .card-content { 
      opacity: 1; 
    }
    .card-content h3 {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: #2c3e50;
    }
    .card-content p {
      margin: 5px 0;
      color: #34495e;
    }
    .card-content blockquote {
      margin-top: 15px;
      font-style: italic;
      padding-left: 15px;
      border-left: 3px solid var(--primary-accent);
      color: #555;
    }
    .card-front {
      text-align: center;
      padding: 20px;
    }
    .card-title {
      font-size: 1.5rem;
      margin-bottom: 15px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end)); 
      -webkit-background-clip: text; 
      background-clip: text;
      color: transparent;
    }
    .card-message {
      margin-bottom: 20px;
      font-size: 1.1rem;
      line-height: 1.4;
    }
    .card-hint {
      position: absolute;
      bottom: 15px;
      width: 100%;
      text-align: center;
      font-style: italic;
      opacity: 0.8;
    }

    #theme-btn { 
      position: fixed; 
      bottom: 25px; 
      left: 25px;
      width: 55px; 
      height: 55px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border: none; 
      border-radius: 50%; 
      box-shadow: 0 5px 10px rgba(0,0,0,0.2);
      cursor: pointer; 
      color: var(--btn-text); 
      font-size: 22px;
      z-index: 100;
      transition: all 0.3s ease;
    }
    #theme-btn:hover {
      transform: rotate(15deg) scale(1.1);
      box-shadow: 0 8px 15px rgba(0,0,0,0.25);
    }
    #theme-selector { 
      position: fixed; 
      bottom: 95px; 
      left: 25px;
      background: rgba(255,255,255,0.95); 
      padding: 15px; 
      border-radius: 15px;
      display: none; 
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      z-index: 100;
      border: 1px solid rgba(0,0,0,0.1);
    }
    #theme-selector button { 
      margin: 8px; 
      padding: 10px 15px;
      border: none; 
      border-radius: 8px; 
      cursor: pointer; 
      font-size: 0.95rem;
      font-weight: 600;
      transition: all 0.2s;
    }
    #theme-selector button:hover {
      transform: translateY(-2px);
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }
    
    /* Language toggle */
    .lang-toggle {
      position: fixed;
      bottom: 25px;
      left: 100px;
      background: rgba(255,255,255,0.25);
      border-radius: 50px;
      padding: 6px;
      display: flex;
      backdrop-filter: blur(6px);
      z-index: 100;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.3);
    }
    .lang-btn {
      padding: 10px 18px;
      border: none;
      border-radius: 50px;
      background: transparent;
      color: var(--text-color);
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s;
      font-size: 0.95rem;
    }
    .lang-btn.active {
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      color: var(--btn-text);
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }
    .lang-btn:hover:not(.active) {
      background: rgba(255,255,255,0.15);
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .card {
      animation: fadeIn 0.6s ease-out;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
      }
      .card-grid {
        grid-template-columns: 1fr;
      }
      .content {
        padding: 20px;
      }
      .content-title {
        font-size: 2rem;
      }
    }
    
    /* Start test button */
    .auth-btn {
      display: inline-block;
      margin-top: 20px;
      padding: 15px 30px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      color: var(--btn-text);
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .auth-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    .auth-btn i {
      margin-right: 10px;
    }
    
    /* Hero section */
    .hero {
      text-align: center;
      padding: 40px 20px;
      max-width: 800px;
      margin: 0 auto;
    }
    .hero h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end)); 
      -webkit-background-clip: text; 
      background-clip: text;
      color: transparent;
    }
    .hero p {
      font-size: 1.2rem;
      margin-bottom: 30px;
      line-height: 1.6;
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
            <div class="user-name"><?= htmlspecialchars($fullname) ?></div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
      </div>
        <ul class="sidebar-menu">
            <li class="menu-title">Navigare Principală</li>
            <li class="menu-item"><a href="dashboard.php" class="menu-link"><div class="menu-icon"><i class="fas fa-home"></i></div><div class="menu-text">Acasă</div></a></li>
            <li class="menu-item"><a href="profile.php" class="menu-link"><div class="menu-icon"><i class="fas fa-user"></i></div><div class="menu-text">Editare Profil</div></a></li>
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
            <li class="menu-item"><a href="teste_rezolvate.php" class="menu-link active"><div class="menu-icon"><i class="fas fa-check-circle"></i></div><div class="menu-text">Teste Rezolvate</div></a></li>
            <li class="menu-title">Administrare cont</li>
    <li class="menu-item">
      <a href="javascript:;" onclick="if(confirm('Ești sigur că vrei să-ți ștergi contul? Această operațiune este ireversibilă.')) location.href='delete_account.php';" class="menu-link">
       <div class="menu-icon"><i class="fas fa-user-slash"></i></div>
       <div class="menu-text">Șterge contul</div>
     </a>
   </li>
            <li class="menu-item"><a href="logout.php" class="menu-link"><div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div><div class="menu-text">Ieșire</div></a></li>
        </ul>
        </div>
    </aside>
    
    <section class="content">
        <div class="content-header">
            <h1 class="content-title">Teste Rezolvate</h1>
            <p class="content-subtitle">Vizionează scorul și detalii despre rezolvările tale</p>
        </div>
        
        <div class="card">
            <?php if (empty($test_results)): ?>
                <section class="hero">
                    <h1>Testul PQ</h1>
                    <p>Completează testul pentru a-ți vedea rezultatele</p>
                    <a href="index.php" class="auth-btn">
                        <i class="fas fa-play-circle"></i> Începe testul
                    </a>
                </section>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($test_dominants as $type): 
                        $info = $typeInfo[$type] ?? null;
                        if (!$info) continue;
                    ?>
                    <div class="card" onclick="this.classList.toggle('revealed')">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($info['title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($info['description'])) ?></p>
                            <p><strong>Puncte tari:</strong> <?= implode(', ', $info['strengths']) ?></p>
                            <p><strong>Puncte slabe:</strong> <?= implode(', ', $info['weaknesses']) ?></p>
                            <blockquote><?= htmlspecialchars($info['quote']) ?></blockquote>
                        </div>
                        <div class="card-front">
                            <div class="card-message"><?= nl2br(htmlspecialchars($test_message)) ?></div>
                            <h2 class="card-title"><?= htmlspecialchars($info['title']) ?></h2>
                            <p style="font-size:30px;background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end)); 
                      -webkit-background-clip: text; color: transparent;">Click pentru detalii<p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
   document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('blocked-test-link1');
    const flash = document.getElementById('flash-message2');

    if (link) {
      link.addEventListener('click', e => {
        e.preventDefault();            // oprește navigarea
        flash.style.display = 'block'; // afișează mesajul

        // după 3 secunde, ascunde mesajul
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
        e.preventDefault();            // oprește navigarea
        flash.style.display = 'block'; // afișează mesajul

        // după 3 secunde, ascunde mesajul
        setTimeout(() => {
          flash.style.display = 'none';
        }, 3000);
      });
    }
  });
    // Theme selector
    const themeBtn = document.getElementById('theme-btn'),
          themeSel = document.getElementById('theme-selector');
    themeBtn.addEventListener('click', () => {
        themeSel.style.display = themeSel.style.display === 'block' ? 'none' : 'block';
    });
    themeSel.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.documentElement.style.setProperty('--gradient-1', btn.dataset.g1);
            document.documentElement.style.setProperty('--gradient-2', btn.dataset.g2);
            document.documentElement.style.setProperty('--btn-grad-start', btn.dataset.b1);
            document.documentElement.style.setProperty('--btn-grad-end', btn.dataset.b2);
            themeSel.style.display = 'none';
        });
    });
    document.addEventListener('click', e => {
        if (!themeSel.contains(e.target) && e.target !== themeBtn) {
            themeSel.style.display = 'none';
        }
    });

    // Language toggle
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Mobile menu
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

    // Submenu Tests
    const testsSubmenu = document.getElementById('tests-submenu');
    testsSubmenu.querySelector('.main-test-link').addEventListener('click', e => {
        e.preventDefault();
        testsSubmenu.classList.toggle('active');
    });
</script>
</body>
</html>