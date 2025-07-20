<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$message = '';
$nr=0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text']); $cat = $_POST['category'];
    if ($text && in_array($cat, ['M','N','O'])) {
        $pdo->prepare("INSERT INTO questions (text,category) VALUES (?,?)")
            ->execute([$text, $cat]);
        $message = 'Întrebare salvată cu succes!';
        $nr++;
    }
}
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Chestionar</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Variabile și stiluri comune */
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
      --result-bg: rgba(0, 0, 0, 0.4);
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
    
    .container {
      max-width: 900px;
      margin: 0 auto;
    }
    
    /* Header */
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 15px 30px;
      background: var(--card-bg);
      border-radius: 16px;
      margin-bottom: 20px;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: 1px solid var(--card-border);
      animation: fadeIn 0.6s ease-out;
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
    .nav-icons {
      display: flex;
      gap: 15px;
    }
    
    .nav-icons a, .nav-icons button {
      background: rgba(255,255,255,0.3);
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      text-decoration: none;
      color: var(--text-color);
      border: none;
      cursor: pointer;
      font-size: 18px;
    }
    
    .nav-icons a:hover, .nav-icons button:hover {
      transform: translateY(-3px);
      background: rgba(255,255,255,0.4);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Conținut principal */
    .content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .admin-container {
      background: var(--card-bg);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
      animation: slideUp 0.5s ease-out;
      position: relative;
    }
    
    .admin-container h1 {
      margin-bottom: 20px;
      border-bottom: 2px solid rgba(255,255,255,0.3);
      padding-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 28px;
    }
    
    .admin-container h1 i {
      color: var(--primary-accent);
    }
    
    .admin-title {
      text-align: center;
      margin-bottom: 25px;
    }
    
    /* Form styling */
    .form-container {
      background: var(--form-bg);
      border-radius: 16px;
      padding: 25px;
      backdrop-filter: blur(5px);
      margin-top: 20px;
      max-width: 700px;
      margin: 0 auto;
    }
    
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 12px;
      font-size: 18px;
      font-weight: 500;
      padding-left: 5px;
    }
    
    textarea, select {
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.9);
      font-size: 16px;
      color: #333;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: all 0.3s;
    }
    
    textarea:focus, select:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 157, 0, 0.4);
    }
    
    textarea {
      min-height: 120px;
      resize: vertical;
    }
    
    select {
      appearance: none;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 15px center;
      background-size: 18px;
      padding-right: 45px;
    }
    
    /* Butoane */
    .btn {
      padding: 15px 30px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border: none;
      border-radius: 50px;
      color: var(--btn-text);
      font-weight: bold;
      cursor: pointer;
      font-size: 17px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      min-width: 200px;
      text-decoration: none;
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn:active {
      transform: translateY(1px);
    }
    
    .btn-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
      margin-top: 30px;
    }
    
    /* Message styling */
    .message {
      padding: 15px;
      border-radius: 10px;
      margin: 15px 0;
      text-align: center;
      font-weight: 500;
      background: rgba(0, 255, 106, 0.2);
      border: 1px solid rgba(0, 255, 106, 0.3);
      animation: fadeIn 0.5s;
    }
    
    /* Butoane flotante */
    .floating-controls {
      position: fixed;
      bottom: 25px;
      left: 25px;
      display: flex;
      gap: 15px;
      z-index: 100;
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
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideUp {
      from { 
        opacity: 0;
        transform: translateY(30px);
      }
      to { 
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      header {
        padding: 12px 20px;
        flex-wrap: wrap;
        gap: 10px;
      }
      
      .admin-container {
        padding: 20px;
      }
      
      .form-container {
        padding: 20px;
      }
      
      .btn {
        width: 100%;
      }
      
      .floating-controls {
        flex-direction: column;
        bottom: 20px;
        left: 20px;
      }
      
      #theme-selector {
        bottom: 150px;
      }
    }
    #flash-message {
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
  <div id="flash-message">Introdu intrebari pentru a vedea testul.</div>
</head>
<body>
  <div class="container">
    <header>
      <div class="nav-icons">
        <a href="home.php" title="Acasă"><i class="fas fa-home"></i></a>
      </div>
      <a href="dashboard.php">
    <div id="user-info">
      <?php if (!empty($user['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
      <?php endif; ?>
      <span>@<?= htmlspecialchars($user['Nume']) ?></span>
    </div>
    </a>
    </header>
    
    <div class="content">
      <div class="admin-container">
        <h1><i class="fas fa-cog"></i> Administrare Întrebări</h1>
        
        <div class="admin-title">
          <h2>Chestionar: "Gândire în mod pozitiv?"</h2>
          <p>Adăugați întrebări noi pentru chestionar</p>
        </div>
        
        <?php if($message): ?>
          <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="form-container">
          <form method="post">
            <div class="form-group">
              <label for="text">Text întrebare:</label>
              <textarea name="text" id="text" rows="4" required placeholder="Introduceți textul întrebării..."></textarea>
            </div>
            
            <div class="form-group">
              <label for="category">Categorie:</label>
              <select name="category" id="category">
                <option value="M">Mizantrop</option>
                <option value="N">Naiv</option>
                <option value="O">Optimist</option>
              </select>
            </div>
            
            <div class="btn-container">
              <button type="submit" class="btn"><i class="fas fa-save"></i> Salvează întrebarea</button>
              <?php if($nr>0): ?>
              <a href="test.php" class="btn"><i class="fas fa-external-link-alt"></i> Deschide chestionarul</a>
              <?php else: ?>
                <a href="#" id="blocked-test-link" class="btn"><i class="fas fa-external-link-alt"></i> Deschide chestionarul</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Butoane flotante -->
  <button id="theme-btn">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Blue</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Orange</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Green</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Gold</button>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('blocked-test-link');
    const flash = document.getElementById('flash-message');

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
    document.addEventListener('DOMContentLoaded', function() {
      // Funcționalitate pentru teme
      const themeBtn = document.getElementById('theme-btn');
      const themeSelector = document.getElementById('theme-selector');
      
      themeBtn.addEventListener('click', function() {
        themeSelector.style.display = themeSelector.style.display === 'block' ? 'none' : 'block';
      });
      
      themeSelector.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
          const g1 = this.dataset.g1;
          const g2 = this.dataset.g2;
          const b1 = this.dataset.b1;
          const b2 = this.dataset.b2;
          
          document.documentElement.style.setProperty('--gradient-1', g1);
          document.documentElement.style.setProperty('--gradient-2', g2);
          document.documentElement.style.setProperty('--btn-grad-start', b1);
          document.documentElement.style.setProperty('--btn-grad-end', b2);
          
          themeSelector.style.display = 'none';
          showNotification('Temă schimbată!');
        });
      });
      
      // Funcționalitate pentru limbi
      const langBtn = document.getElementById('lang-btn');
      let currentLang = 'ro';
      
      langBtn.addEventListener('click', function() {
        currentLang = currentLang === 'ro' ? 'en' : 'ro';
        const langText = currentLang === 'ro' ? 'Limba schimbată în Română' : 'Language changed to English';
        showNotification(langText);
      });
      
      
      // Adaugă keyframes pentru fadeOut dacă nu există
      const styleEl = document.createElement('style');
      styleEl.textContent = `
        @keyframes fadeOut {
          from { opacity: 1; transform: translateY(0); }
          to { opacity: 0; transform: translateY(20px); }
        }
      `;
      document.head.appendChild(styleEl);
      
      // Adaugă animație la încărcarea paginii
      document.body.style.opacity = 0;
      setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease-in';
        document.body.style.opacity = 1;
      }, 100);
    });
  </script>
</body>
</html>