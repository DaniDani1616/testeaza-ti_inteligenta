<?php
// chestionar.php
session_start();
require 'config.php';

// dacă nu e autentificat, duce la login
if (!isset($_SESSION['user_id']) && !isset($_GET['share'])) {
  header('Location: login.php');
  exit;
}
$mysqli = require __DIR__ . '/database.php';
// Dacă accesăm prin link partajat
if (isset($_GET['share'])) {
  $shareId = $_GET['share'];
  $stmt = $mysqli->prepare("SELECT user_id FROM share_links WHERE share_id = ?");
  $stmt->bind_param('s', $shareId);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $_SESSION['shared_user_id'] = $row['user_id'];
  } else {
      header('Location: login.php');
      exit;
  }
  $stmt->close();
}

// preluăm lista de întrebări
$qs = $pdo->query("SELECT id, text, category FROM questions ORDER BY id")->fetchAll();
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
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Chestionar Dinamic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --primary-accent: #ff9d00;
      --text-color: #fff;
      --card-bg: rgba(255,255,255,0.2);
      --card-border: rgba(255,255,255,0.3);
      --form-bg: rgba(255,255,255,0.1);
      --delete-color: rgba(255,0,0,0.2);
    }
      
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      color: var(--text-color);
      min-height: 100vh;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
      line-height: 1.6;
    }
    #user-info {
      display:flex; align-items:center; gap:10px;
      background: rgba(255,255,255,0.7); color:#333;
      padding:10px 20px; border-radius:50px; font-weight:600;
    }
    #user-info img {
      width:48px; height:48px; border-radius:50%; object-fit:cover;
      border:2px solid white;
    }
    header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:15px;
      background:var(--card-bg);
      border-radius:16px;
      border:1px solid var(--card-border);
      backdrop-filter:blur(10px);
      margin-bottom:20px;
    }
    .nav-icons {
  display: flex;
  /* margin-left: 750px; */   /* <— ăsta nu ne mai trebuie */
  /* top: 10%; */              /* nici asta */
}

/* wrapperul care aliniază titlul și butonul */
.title-wrapper {
  display: inline-flex;        /* sau flex, în funcție de layout */
  align-items: center;
  gap: 12px;                   /* spațiu între titlu și buton */
}

/* restul stilului tău pentru nav-icons/button rămâne */
.nav-icons button {
      background: rgba(255, 255, 255, 0.2);
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      cursor: pointer;
      font-size: 20px;
      color: white;
      transition: all 0.3s ease;
      z-index: 1;
    }
.nav-icons button:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-3px);
}
    .container { max-width:900px; margin:0 auto }
    h2 { color:var(--primary-accent) }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      padding: 30px;
      color:white;
      border-radius: 20px;
      max-width: 600px;
      width: 90%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: white;
    }
    /* form */
    .form-container {
      background:var(--form-bg);
      backdrop-filter:blur(5px);
      border-radius:16px;
      padding:25px;
    }
    fieldset {
      position: relative;
      border:none;
      margin-bottom:1.5rem;
    }
    legend {
      font-size:18px;
      margin-bottom:10px;
      display:block;
    }
    .options {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
      gap:15px;
    }
    label.option {
      background:rgba(255,255,255,0.2);
      padding:15px;
      border-radius:15px;
      text-align:center;
      cursor:pointer;
      position:relative;
    }
    label.option input {
      position:absolute; opacity:0; width:0; height:0;
    }
    label.option.selected {
      background:rgba(255,157,0,0.4);
      border:2px solid var(--primary-accent);
    }

    /* buton submit */
    .controls {
      display:flex;
      justify-content:center;
      margin-top:20px;
    }
    .btn {
      padding:12px 25px;
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      border:none;
      border-radius:50px;
      color:#333;
      font-weight:bold;
      cursor:pointer;
    }

    /* buton stergere */
    .delete-btn {
      position:absolute;
      bottom:50px;
      right:0;
      background:var(--delete-color);
      border:none;
      padding:6px;
      border-radius:6px;
      cursor:pointer;
      font-size:0.9rem;
      color:#900;
      transition:background .2s;
    }
    .delete-btn:hover {
      background:rgba(255,0,0,0.4);
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
  </style>
</head>
<body>
  <header>
    <div><a href="home.php" style="color:var(--text-color);text-decoration:none"><i class="fas fa-home"></i></a></div>
    <a href="dashboard.php">
    <div id="user-info">
      <?php if (!empty($user['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
      <?php endif; ?>
      <span>@<?= htmlspecialchars($user['Nume']) ?></span>
    </div>
    </a>
  </header>
  <div id="infoModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Informatii pentru rezolvare</h2>
        <button class="close-modal">&times;</button>
      </div>
      <p>Numerele "1,2,3,4" semnifica:</p>
      <ul style="margin: 15px 0; padding-left: 20px;font-size:20px;">
        <li>"1" = "Nu corespunde"</li>
        <li>"2" = "Corespunde uneori"</li>
        <li>"3" = "Corespunde deseori"</li>
        <li>"4" = "Corespunde pe deplin"</li>
      </ul>
    </div>
  </div>
  <div id="shareModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Partajează chestionarul</h2>
      <button class="close-share-modal">&times;</button>
    </div>
    <p>Trimite acest link altcuiva pentru a completa chestionarul:</p>
    <div style="display:flex;margin-top:20px">
      <input type="text" id="shareLink" readonly style="flex:1;padding:10px;border-radius:8px 0 0 8px;border:none">
      <button id="copyBtn" style="padding:10px 15px;background:#4CAF50;color:white;border:none;border-radius:0 8px 8px 0;cursor:pointer">Copiază</button>
    </div>
    <p id="copyStatus" style="margin-top:10px;color:#4CAF50;display:none"><i class="fas fa-check"></i> Link copiat!</p>
  </div>
</div>
  <div class="container">
  <div class="title-wrapper">
  <h2><i class="fas fa-question-circle"></i> Chestionar Dinamic</h2>
  <div class="nav-icons">
  <button id="infoBtn"><i class="fas fa-info-circle"></i></button>
  </div>
</div>
    <form method="post" action="process.php" class="form-container">
      <?php foreach($qs as $q): ?>
        <fieldset>
          <button
            type="button"
            class="delete-btn"
            onclick="if(confirm('Sigur vrei să ștergi întrebarea #<?= $q['id'] ?>?')) location.href='delete_question.php?id=<?= $q['id'] ?>';"
            title="Șterge întrebarea"
          >
            <i class="fas fa-trash"></i>
          </button>

          <legend><?=htmlspecialchars($q['text'])?></legend>
          <div class="options">
            <?php for($i=1; $i<=4; $i++): ?>
              <label class="option">
                <input type="radio" name="q<?=$q['id']?>" value="<?=$i?>" required>
                <div><?=$i?></div>
              </label>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="cat<?=$q['id']?>" value="<?=$q['category']?>">
        </fieldset>
      <?php endforeach; ?>

      <div class="controls">
        <button class="btn"><i class="fas fa-paper-plane"></i> Trimite răspunsurile</button>
        <div class="nav-icons">
        <button style="margin-left:5px;"id="shareBtn"><i class="fas fa-share-alt"></i></button>
        </div>
      </div>
    </form>
  </div>
  <button id="theme-btn">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Blue</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Orange</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Green</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Gold</button>
  </div>
  <script>
    document.querySelectorAll('.option input').forEach(radio => {
      radio.addEventListener('change', () => {
        let opts = radio.closest('.options').querySelectorAll('.option');
        opts.forEach(o => o.classList.remove('selected'));
        radio.parentNode.classList.add('selected');
      });
    });
    // 1) selectăm elementele
    const infoBtn    = document.getElementById('infoBtn');
    const infoModal  = document.getElementById('infoModal');
    const closeModal = infoModal.querySelector('.close-modal');

    // 2) când dai click pe butonul „i”
    infoBtn.addEventListener('click', () => {
      infoModal.style.display = 'flex';
    });

    // 3) când dai click pe X
    closeModal.addEventListener('click', () => {
      infoModal.style.display = 'none';
    });

    // 4) când dai click în afara conținutului modalului
    window.addEventListener('click', (e) => {
      if (e.target === infoModal) {
        infoModal.style.display = 'none';
      }
    });
    // Theme selector
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
    // Butonul de partajare
const shareBtn = document.getElementById('shareBtn');
const shareModal = document.getElementById('shareModal');
const closeShareModal = shareModal.querySelector('.close-share-modal');
const copyBtn = document.getElementById('copyBtn');
const shareLink = document.getElementById('shareLink');
const copyStatus = document.getElementById('copyStatus');

// Deschide modalul de partajare
shareBtn.addEventListener('click', () => {
  // Generează un ID unic pentru sesiunea de partajare
  const shareId = Math.random().toString(36).substr(2, 9);
  
  // Salvează ID-ul în sesiune
  fetch('save_share_id.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ shareId: shareId, userId: <?= $user_id ?> })
  })
  .then(() => {
    // Construiește link-ul
    const currentUrl = window.location.href.split('?')[0];
    const shareUrl = `${currentUrl}?share=${shareId}`;
    shareLink.value = shareUrl;
    shareModal.style.display = 'flex';
  });
});

// Închide modalul
closeShareModal.addEventListener('click', () => {
  shareModal.style.display = 'none';
});

// Copiază link-ul
copyBtn.addEventListener('click', () => {
  shareLink.select();
  document.execCommand('copy');
  copyStatus.style.display = 'block';
  setTimeout(() => copyStatus.style.display = 'none', 2000);
});

// Închide când se dă click în afara modalului
window.addEventListener('click', (e) => {
  if (e.target === shareModal) shareModal.style.display = 'none';
});
  </script>
</body>
</html>
