<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$mysqli = require __DIR__ . '/database.php';

$flash = $_SESSION['flash_welcome'] ?? null;
unset($_SESSION['flash_welcome']);


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

$fullname = trim("{$user['Prenume']} {$user['Numereal']}");

?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
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
      font-family: 'Segoe UI', 'Poppins', sans-serif;
      color: var(--text-color);
      overflow-x:hidden;
      display: flex;
      flex-direction: column;
    }
   
    #flash-message {
      position: fixed; top:20px; left:50%; transform:translateX(-50%);
      background:#e6ffed; color:#2e7d32;
      padding:12px 24px; border:1px solid #a5d6a7; border-radius:4px;
      opacity:0; transition:opacity .3s; z-index:1000;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
      font-weight: 500;
    }
    #flash-message.show { opacity:1; }
    .has-submenu .submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      padding-left: 20px;
    }
    .has-submenu.active .submenu {
      max-height: 500px; 
    }
    .menu-arrow {
      margin-left: auto;
      transition: transform 0.3s ease;
    }
    .has-submenu.active .menu-arrow i {
      transform: rotate(180deg);
    } 

    
    header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 15px 30px; margin: 20px;
      background: rgba(255,255,255,0.2); border-radius: 16px;
      backdrop-filter: blur(10px); box-shadow:0 4px 20px rgba(0,0,0,0.1);
      border:1px solid var(--card-border); position: relative; z-index:50;
    }
    .nav-icons { display: flex; gap: 15px; }
    .nav-icons button {
      background: rgba(255,255,255,0.3); border:none; width:44px; height:44px;
      border-radius:10px; cursor:pointer; display:flex; align-items:center;
      justify-content:center; transition:all .3s;
    }
    .nav-icons button:hover { transform:translateY(-2px); background:rgba(255,255,255,0.4);}
    .nav-icons svg { width:22px; height:22px; fill:white; transition:transform .3s; }
    .nav-icons button:hover svg{ transform:scale(1.1); }

    #user-info {
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

    
    .content {
      flex:1; padding:30px; overflow-y:auto;
    }
    .content-header { margin-bottom:30px; }
    .content-title { font-size:28px; font-weight:600; margin-bottom:10px; }
    .content-subtitle { opacity:.8; }

    .card-grid {
      display:grid;
      margin-right:0px; margin-bottom:0px;
    }
    .card {
      background:var(--card-bg); backdrop-filter:blur(var(--card-blur));
      border:1px solid var(--card-border); border-radius:15px;
      padding:25px; transition:transform .3s,box-shadow .3s;
      margin-top:50px;
    }
    .card:hover {
      transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.1);
    }
    .card-header { display:flex; align-items:center; margin-bottom:20px; }
    .card-icon {
      width:50px;height:50px;border-radius:12px;
      background:rgba(255,255,255,0.2);display:flex;
      align-items:center;justify-content:center;margin-right:15px;font-size:24px;
    }
    .card-title { font-size:18px; font-weight:600; }
    .user-data dt {
      font-weight:bold; opacity:.8; font-size:14px; margin-top:12px;
    }
    .user-data dd {
      margin:5px 0 0 0; font-size:16px; font-weight:500;
    }

    .stats-grid {
      display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-top:15px;
    }
    .stat-item {
      text-align:center; padding:15px; background:rgba(255,255,255,0.15);
      border-radius:10px;
    }
    .stat-value { font-size:24px; font-weight:700; margin:10px 0; }
    .stat-label { font-size:14px; opacity:.8; }

    .progress-bar {
      height:8px; background:rgba(255,255,255,0.2); border-radius:4px;
      overflow:hidden; margin-top:15px;
    }
    .progress-fill {
      height:100%; background:var(--btn-grad-start); border-radius:4px;
      width:75%;
    }

    
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

  <?php if ($flash): ?>
    <div id="flash-message"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>


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
        <li class="menu-item"><a href="#" class="menu-link active"><div class="menu-icon"><i class="fas fa-home"></i></div><div class="menu-text">Acasă</div></a></li>
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
        <h1 class="content-title">Bun venit în panoul de control</h1>
        <p class="content-subtitle">Gestionează-ți activitatea și datele de aici</p>
      </div>

      <div class="card-grid">
        <div class="card">
          <div class="card-header"><div class="card-icon"><i class="fas fa-user"></i></div><h2 class="card-title">Profilul tău</h2></div>
          <dl class="user-data">
            <dt>Nume real:</dt><dd><?= htmlspecialchars($fullname) ?></dd>
            <dt>Username:</dt><dd><?= htmlspecialchars($user['Nume']) ?></dd>
            <dt>Email:</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
          </dl>
        </div>
      </div>
      
  <div class="card">
  <div class="card-header">
    <div class="card-icon">
      <i class="fas fa-brain"></i>  
    </div>
    <h2 class="card-title">Bine ai venit!</h2>
  </div>
  <div>
    <p style="font-size:18px;padding: 20px; line-height: 1.6;font">
      <b>Bine ai venit pe site-ul nostru, poți începe să rezolvi teste și să vezi cât de inteligent ești, atât mental cât și psihic.  
      Poți chiar să creezi propriul tău test și mai apoi să îl rezolvi.</b>
    </p>
  </div>
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

    const themeBtn = document.getElementById('theme-btn');
    const themeSel = document.getElementById('theme-selector');
    
    themeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
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
    
    document.addEventListener('click', (e) => {
      if (!themeSel.contains(e.target) && e.target !== themeBtn) {
        themeSel.style.display = 'none';
      }
    });
    
    const translations = {
      ro: {
        welcome: "Bun venit în",
        dashboard: "panoul de control",
        manage: "Gestionează-ți activitatea și datele de aici",
        profile: "Profilul tău",
        stats: "Statistici",
        notifications: "Notificări recente",
        activity: "Activitate recentă",
        navigation: "Navigare Principală",
        home: "Acasă",
        data: "Date",
        actions: "Acțiuni",
        create: "Creează",
        tests: "Teste",
        solvedTests: "Teste Rezolvate",
        settingsMenu: "Setări",
        settings: "Setări",
        logout: "Ieșire",
        projects: "Proiecte",
        completed: "Finalizate",
        inProgress: "În curs",
        monthly: "Progres lunar",
        action: "Acțiune",
        details: "Detalii",
        date: "Dată",
        status: "Status"
      },
      en: {
        welcome: "Welcome to",
        dashboard: "Dashboard",
        manage: "Manage your activity and data from here",
        profile: "Your Profile",
        stats: "Statistics",
        notifications: "Recent Notifications",
        activity: "Recent Activity",
        navigation: "Main Navigation",
        home: "Home",
        data: "Data",
        actions: "Actions",
        create: "Create",
        tests: "Tests",
        solvedTests: "Solved Tests",
        settingsMenu: "Settings",
        settings: "Settings",
        logout: "Logout",
        projects: "Projects",
        completed: "Completed",
        inProgress: "In Progress",
        monthly: "Monthly Progress",
        action: "Action",
        details: "Details",
        date: "Date",
        status: "Status"
      }
    };
    
    function updateLanguage(lang) {
    
      document.querySelector('.content-title').textContent = 
        `${translations[lang].welcome} ${translations[lang].dashboard}`;
      document.querySelector('.content-subtitle').textContent = translations[lang].manage;
      
     
      const cardTitles = document.querySelectorAll('.card-title');
      cardTitles[0].textContent = translations[lang].profile;
      cardTitles[1].textContent = translations[lang].stats;
      cardTitles[2].textContent = translations[lang].notifications;
      cardTitles[3].textContent = translations[lang].activity;
      
   
      const statLabels = document.querySelectorAll('.stat-label');
      statLabels[0].textContent = translations[lang].projects;
      statLabels[1].textContent = translations[lang].completed;
      statLabels[2].textContent = translations[lang].inProgress;
    
      const progressText = document.querySelector('.progress-bar').previousElementSibling;
      progressText.querySelector('span:first-child').textContent = translations[lang].monthly;
      
      document.querySelectorAll('.menu-title').forEach((title, index) => {
        if (index === 0) title.textContent = translations[lang].navigation;
        if (index === 1) title.textContent = translations[lang].actions;
        if (index === 2) title.textContent = translations[lang].settingsMenu;
      });
      
      const menuItems = document.querySelectorAll('.menu-text');
      menuItems[0].textContent = translations[lang].home;
      menuItems[1].textContent = translations[lang].data;
      menuItems[2].textContent = translations[lang].create;
      menuItems[3].textContent = translations[lang].tests;
      menuItems[4].textContent = translations[lang].solvedTests;
      menuItems[5].textContent = translations[lang].settings;
      menuItems[6].textContent = translations[lang].logout;
      const tableHeaders = document.querySelectorAll('table th');
      if (tableHeaders.length > 0) {
        tableHeaders[0].textContent = translations[lang].action;
        tableHeaders[1].textContent = translations[lang].details;
        tableHeaders[2].textContent = translations[lang].date;
        tableHeaders[3].textContent = translations[lang].status;
      }
    }
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateLanguage(btn.dataset.lang);
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
    if (testsSubmenu) {
      testsSubmenu.querySelector('.main-test-link').addEventListener('click', e => {
        e.preventDefault();
        testsSubmenu.classList.toggle('active');
      });
    }
  </script>
</body>
</html>
