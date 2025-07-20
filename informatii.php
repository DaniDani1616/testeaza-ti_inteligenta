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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Help Center - Discover Online</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --accent-start: #a1ffce;
      --accent-end: #faffd1;
      --text-light: #fff;
      --text-dark: #333;
      --card-bg: rgba(255, 255, 255, 0.2);
      --card-border: rgba(255, 255, 255, 0.3);
      --primary-accent: #ff9d00;
    }
    
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      color: var(--text-dark);
      min-height: 100vh;
      padding: 15px;
      line-height: 1.6;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
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

    @keyframes spin {
      to { transform: translateY(-50%) rotate(360deg); }
    }
    
    main {
      flex: 1;
      background: transparent;
      padding: 20px 0;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: var(--text-light);
      font-size: 2rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .card {
      background: var(--card-bg);
      backdrop-filter: blur(5px);
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px 20px;
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
      border: 1px solid var(--card-border);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      background: rgba(255,255,255,0.3);
    }
    
    .card-icon {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
      background: linear-gradient(135deg, var(--accent-start), var(--accent-end));
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: var(--text-dark);
    }
    
    .card-title {
      font-weight: bold;
      margin-bottom: 15px;
      font-size: 1.4rem;
      color: var(--text-light);
    }
    
    .card-desc {
      font-size: 1rem;
      line-height: 1.6;
      color: #f0f0f0;
      margin-bottom: 20px;
      flex-grow: 1;
    }
    
    .card-btn {
      padding: 10px 25px;
      background: linear-gradient(135deg, var(--accent-start), var(--accent-end));
      border: none;
      border-radius: 50px;
      color: var(--text-dark);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .card-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    
    .faq-section {
      max-width: 800px;
      margin: 50px auto;
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: 1px solid var(--card-border);
    }
    
    .faq-section h3 {
      text-align: center;
      margin-bottom: 30px;
      color: var(--text-light);
      font-size: 1.8rem;
    }
    
    .faq-item {
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      padding-bottom: 20px;
    }
    
    .faq-question {
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      padding: 10px 0;
      color: var(--text-light);
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    .faq-answer {
      padding: 10px 0;
      color: #f0f0f0;
      display: none;
    }
    
    .faq-answer.show {
      display: block;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .contact-section {
      text-align: center;
      margin: 50px auto;
      max-width: 600px;
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border: 1px solid var(--card-border);
    }
    
    .contact-section h3 {
      margin-bottom: 20px;
      color: var(--text-light);
    }
    
    .contact-info {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
    }
    
    .contact-item {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--text-light);
      padding: 10px 20px;
      background: rgba(255,255,255,0.1);
      border-radius: 50px;
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
    
    @media (max-width: 768px) {
      .grid {
        grid-template-columns: 1fr;
      }
      
      header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
      }
      
      nav {
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .faq-section, .contact-section {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
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
      <h2>Explorează Secțiunile de Ajutor</h2>
      
      <div class="grid">
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-star"></i>
          </div>
          <div class="card-title">Începători</div>
          <div class="card-desc">Pentru inceput poti rezolva testele creeate de noi.</div>
        </div>
        
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-puzzle-piece"></i>
          </div>
          <div class="card-title">Creare Quiz</div>
          <div class="card-desc">Din pagina contului tau poti apasa pe creeaza si te va duce intr-o pagina unde iti vei putea creea propriul quiz PQ.</div>
        </div>
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-puzzle-piece"></i>
          </div>
          <div class="card-title">Rezolvare Teste</div>
          <div class="card-desc">Din pagina contului tau poti apasa pe teste si vei vedea toate testele oferite de noi.</div>
        </div>
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-puzzle-piece"></i>
          </div>
          <div class="card-title">Trimite teste</div>
          <div class="card-desc">Dupa ce creezi un test il poti trimite si altor persoane pentru al rezolva.</div>
        </div>
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-palette"></i>
          </div>
          <div class="card-title">Personalizare</div>
          <div class="card-desc">Adaptează tema și culorile site-ului după preferințe, personalizează profilul tău.</div>
        </div>
        
        <div class="card">
          <div class="card-icon">
            <i class="fas fa-user-circle"></i>
          </div>
          <div class="card-title">Cont</div>
          <div class="card-desc">Actualizează informațiile contului.Pentru a schimba e-mailul ne poti contacta pe e-mail.</div>
        </div>
      
      <div class="faq-section">
        <h3>Întrebări Frecvente</h3>
        
        <div class="faq-item">
          <div class="faq-question">
            Cum îmi pot crea un cont?
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Pentru a crea un cont, accesează pagina de înregistrare, completează formularul cu datele tale și urmează instrucțiunile de verificare. Procesul este rapid și gratuit.
          </div>
        </div>
        
        <div class="faq-item">
          <div class="faq-question">
            Cum pot schimba tema site-ului?
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            În stanga jos gasesti un buton unde poti schimba tema site site-ului.
          </div>
        </div>
        
        <div class="faq-item">
          <div class="faq-question">
            Ce fac dacă am uitat parola?
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Pe pagina de autentificare, apasă pe "Ai uitat parola?". Vei primi un link care expira in cateva secunde pnetru a fi cat mai securizat totul, si apasand pe acel link te duce la creearea noii parole.
          </div>
        </div>
      </div>
      <div class="contact-section">
        <h2>Contact</h2>
        <h3>Ai nevoie de mai mult ajutor?</h3>
        <p>Echipa noastră de suport este disponibilă să îți răspundă la orice întrebare ai putea avea. Contactează-ne prin oricare dintre metodele de mai jos:</p>
        
        <div class="contact-info">
          <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <span>suport@discoveronline.com</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-phone"></i>
            <span>+40 123 456 789</span>
          </div>
        </div>
      </div>
    </main>
    
  </div>
  <button id="theme-btn">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Blue</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Orange</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Green</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Gold</button>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
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
      
      // FAQ accordion functionality
      const faqQuestions = document.querySelectorAll('.faq-question');
      faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
          const answer = question.nextElementSibling;
          const icon = question.querySelector('i');
          
          // Toggle answer visibility
          answer.classList.toggle('show');
          
          // Toggle icon
          if (answer.classList.contains('show')) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
          } else {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
          }
        });
      });
      
      // Card hover effect enhancement
      const cards = document.querySelectorAll('.card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          card.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', () => {
          card.style.transform = 'translateY(0)';
        });
      });
    });
  </script>
</body>
</html>