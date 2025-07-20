<?php
session_start();
$display_name = '';
$avatar_letter = '';
$is_logged_in = false;

if(isset($_SESSION['user_id'])) {
    $is_logged_in = true;
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
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portal Educațional</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --card-bg: rgba(255,255,255,0.3);
      --card-blur: 5px;
      --text-color: #fff;
      --btn-text: #333;
      --primary-accent: #ff9d00;
      --card-border: rgba(255, 255, 255, 0.5);
    }
    *, *::before, *::after { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
    }
    html, body { 
      height: 100%; 
      scroll-behavior: smooth;
    }
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      font-family: 'Segoe UI', 'Poppins', Tahoma, sans-serif;
      display: flex; 
      flex-direction: column; 
      color: var(--text-color);
      overflow-x: hidden;
      line-height: 1.6;
    }
    header {
      display: flex; 
      align-items: center; 
      justify-content: space-between;
      padding: 15px 30px; 
      background: rgba(255,255,255,0.2);
      border-radius: 16px; 
      margin: 20px;
      backdrop-filter: blur(10px);
      position: relative;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: 1px solid var(--card-border);
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
      transition: all 0.3s ease;
    }
    .nav-icons button:hover {
      transform: translateY(-2px);
      background: rgba(255,255,255,0.4);
    }
    .nav-icons svg { 
      width: 22px; 
      height: 22px; 
      fill: white; 
      transition: transform 0.3s;
    }
    .nav-icons button:hover svg {
      transform: scale(1.1);
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
    .auth-buttons {
      display: flex;
      gap: 12px;
    }
    .auth-btn {
      padding: 10px 25px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border: none;
      border-radius: 50px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      font-weight: bold;
      cursor: pointer;
      color: var(--btn-text);
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 1rem;
      text-align: center;
    }
    .auth-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
    }
    .register-btn {
      background: rgba(255,255,255,0.3);
      color: var(--text-color);
    }
    .register-btn:hover {
      background: rgba(255,255,255,0.4);
    }
    main { 
      flex: 1; 
      display: flex; 
      flex-direction: column; 
      align-items: center; 
      padding: 20px;
    }
    .hero {
      text-align: center;
      max-width: 800px;
      margin: 40px auto;
      padding: 30px;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
    }
    .hero h1 {
      font-size: 2.8rem;
      margin-bottom: 20px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .hero p {
      font-size: 1.2rem;
      margin-bottom: 30px;
      line-height: 1.8;
    }
    .grid { 
      display: grid; 
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 35px; 
      width: 90%; 
      max-width: 1200px;
      margin-top: 20px;
    }
    .card { 
      position: relative; 
      background: var(--card-bg);
      backdrop-filter: blur(var(--card-blur)); 
      border-radius: 20px;
      overflow: hidden; 
      height: 220px; 
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 12px 25px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
      cursor: pointer;
    }
    .card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
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
      z-index: 10;
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
      .grid {
        grid-template-columns: 1fr;
      }
      .hero {
        padding: 20px;
      }
      .hero h1 {
        font-size: 2rem;
      }
      .auth-buttons {
        width: 100%;
        justify-content: center;
      }
    }
    footer {
      text-align: center;
      padding: 20px;
      background: rgba(0,0,0,0.1);
      margin-top: 40px;
    }
  </style>
</head>
<body>

  <header>
    <div class="nav-icons">
      <button title="Home"><svg viewBox="0 0 576 512"><path d="M280.37 148.26L96 300.11V464a16 16 0 0016 16l112-.29a16 16 0 0016-15.74V368a16 16 0 0116-16h64a16 16 0 0116 16v96a16 16 0 0016 16l112 .29a16 16 0 0016-16V300L295.67 148.26a12.19 12.19 0 00-15.3 0z"/></svg></button>
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
    <div>
      <?php if($is_logged_in): ?>
        <!-- User menu for authenticated users -->
        <a href="dashboard.php">
        <div id="user-info">
      <?php if (!empty($user['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Avatar">
      <?php endif; ?>
      <span>@<?= htmlspecialchars($user['Nume']) ?></span>
    </div>
    </a>
      <?php else: ?>
        <!-- Buttons for guests -->
        <div class="auth-buttons">
          <a href="login.php" class="auth-btn">Autentificare</a>
          <a href="index.html" class="auth-btn register-btn">Înregistrare</a>
        </div>
      <?php endif; ?>
    </div>
  </header>
  
  <main>
    <div class="hero">
      <h1>Testeaza-ti inteligenta nu doar mintala cat si psihica.</h1>
      <p>Bine ați venit pe platforma noastră educațională! Aici veți găsi resurse de calitate pentru dezvoltarea personală și profesională.</p>
    </div>
    
    <div class="grid">
      <div class="card" data-title="Dezvoltare Cognitivă" data-info="Strategii și exerciții pentru îmbunătățirea funcțiilor cognitive" data-user="@Expert">
        <div class="card-content"><h3>Dezvoltare Cognitivă</h3><p>Exerciții pentru memorie, atenție și logică</p><p>Dificultate: Medie</p><p>Postat de: @Expert</p></div>
      </div>
      <div class="card" data-title="Abilități Spațiale" data-info="Exerciții de vizualizare și orientare spațială" data-user="@Neuro">
        <div class="card-content"><h3>Abilități Spațiale</h3><p>Vizualizare 3D și rotații mentale</p><p>Dificultate: Avansată</p><p>Postat de: @Neuro</p></div>
      </div>
      <div class="card" data-title="Matematică Aplicată" data-info="Aplicații practice ale matematicii în viața de zi cu zi" data-user="@Profesor">
        <div class="card-content"><h3>Matematică Aplicată</h3><p>Modele și secvențe în natură și tehnologie</p><p>Dificultate: Variată</p><p>Postat de: @Profesor</p></div>
      </div>
    </div>
  </main>
  
  <button id="theme-btn">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
  </div>
  <script>
    // Translations for Romanian and English
    const translations = {
      ro: {
        login: "Autentificare",
        register: "Înregistrare",
        heroTitle: "Testeaza-ti inteligenta nu doar mintala cat si psihica.",
        heroText: "Bine ați venit pe platforma noastră educațională! Aici veți găsi resurse de calitate pentru dezvoltarea personală și profesională.",
        profile: "Profil",
        settings: "Setări",
        logout: "Deconectare"
      },
      en: {
        login: "Login",
        register: "Register",
        heroTitle: "Educational Portal",
        heroText: "Welcome to our educational platform! Here you will find quality resources for personal and professional development.",
        profile: "Profile",
        settings: "Settings",
        logout: "Logout"
      }
    };
    
    let currentLang = 'ro';
    
    // Update interface based on language
    function updateLanguage() {
      const loginBtn = document.querySelector('.auth-btn');
      if(loginBtn) {
        loginBtn.textContent = translations[currentLang].login;
      }
      
      const registerBtn = document.querySelector('.register-btn');
      if(registerBtn) {
        registerBtn.textContent = translations[currentLang].register;
      }
      
      document.querySelector('.hero h1').textContent = translations[currentLang].heroTitle;
      document.querySelector('.hero p').textContent = translations[currentLang].heroText;
      
      // Update dropdown menu items if user is logged in
      const dropdownItems = document.querySelectorAll('.dropdown-item span');
      if(dropdownItems.length > 0) {
        dropdownItems[0].textContent = translations[currentLang].profile;
        dropdownItems[1].textContent = translations[currentLang].settings;
        dropdownItems[2].textContent = translations[currentLang].logout;
      }
    }
    
    // Language buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentLang = btn.dataset.lang;
        updateLanguage();
      });
    });
    
    // Theme functionality
    const selector = document.getElementById('theme-selector');
    document.getElementById('theme-btn').addEventListener('click', () => {
      selector.style.display = selector.style.display === 'block' ? 'none' : 'block';
    });
    
    selector.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
      const g1 = btn.dataset.g1, g2 = btn.dataset.g2, b1 = btn.dataset.b1, b2 = btn.dataset.b2;
      document.documentElement.style.setProperty('--gradient-1', g1);
      document.documentElement.style.setProperty('--gradient-2', g2);
      document.documentElement.style.setProperty('--btn-grad-start', b1);
      document.documentElement.style.setProperty('--btn-grad-end', b2);
      selector.style.display = 'none';
    }));
    
    // Card animation
    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('click', () => {
        card.classList.toggle('revealed');
      });
    });
    
    // User menu functionality
    document.addEventListener('DOMContentLoaded', function() {
      const userMenu = document.getElementById('user-menu');
      if(userMenu) {
        const dropdownMenu = document.getElementById('dropdown-menu');
        
        userMenu.addEventListener('click', function(e) {
          e.stopPropagation();
          const isActive = userMenu.classList.contains('active');
          
          // Close all other dropdowns first
          document.querySelectorAll('.user-menu.active').forEach(el => {
            if(el !== userMenu) {
              el.classList.remove('active');
              el.querySelector('.dropdown-menu').classList.remove('active');
            }
          });
          
          // Toggle this dropdown
          userMenu.classList.toggle('active');
          dropdownMenu.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if(!userMenu.contains(e.target)) {
            userMenu.classList.remove('active');
            dropdownMenu.classList.remove('active');
          }
        });
        
        // Prevent closing when clicking inside dropdown
        dropdownMenu.addEventListener('click', function(e) {
          e.stopPropagation();
        });
      }
    });
    
    // Initialize
    updateLanguage();
  </script>
</body>
</html>