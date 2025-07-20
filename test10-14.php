<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conectare la baza de date
$mysqli = require __DIR__ . '/database.php';

// Obținere date utilizator (inclusiv profile_pic, theme)
$user_id = $_SESSION['user_id'];
$sql = "SELECT Numereal, Prenume, Nume, email, profile_pic
        FROM registration
       WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Nume complet
$fullname = trim("{$user['Prenume']} {$user['Numereal']}");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test WISC pentru Copii (10-14 ani)</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --gradient-1: #4361ee;
      --gradient-2: #3f37c9;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --primary-accent: #4cc9f0;
      --text-color: #fff;
      --btn-text: #333;
      --card-bg: rgba(255, 255, 255, 0.2);
      --card-border: rgba(255, 255, 255, 0.3);
      --whiteboard-bg: #f8f9fa;
      --toolbar-bg: #e9ecef;
      --correct: #66bb6a;
      --incorrect: #ef5350;
      --background: #f5f7fa;
      --card: #ffffff;
      --shadow: rgba(0, 0, 0, 0.1);
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
      width: 100%;
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
      position: relative;
      z-index: 50;
      overflow: hidden;
    }

    header::before {
      content: "";
      position: absolute;
      top: -50px;
      right: -50px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    header::after {
      content: "";
      position: absolute;
      bottom: -30px;
      left: -30px;
      width: 100px;
      height: 100px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .header-title h1 {
      font-size: 1.8rem;
      margin-bottom: 5px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .header-title p {
      font-size: 1.1rem;
      opacity: 0.9;
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

    .nav-icons button {
      background: rgba(255, 255, 255, 0.2);
      width: 50px;
      height: 50px;
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
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-3px);
    }

    /* Content Sections */
    .content-section {
      background: var(--card-bg);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
      animation: slideUp 0.5s ease-out;
      position: relative;
      margin-bottom: 20px;
      display: none;
    }

    .content-section.active {
      display: block;
    }

    /* Intro Section */
    .info-card {
      background: linear-gradient(135deg, rgba(227, 242, 253, 0.7), rgba(187, 222, 251, 0.7));
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      border-left: 5px solid var(--primary-accent);
    }

    .info-card h2 {
      color: var(--gradient-1);
      margin-bottom: 15px;
      font-size: 1.5rem;
    }

    .info-card p {
      margin-bottom: 15px;
      line-height: 1.6;
      color: #333;
    }

    .scale-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin: 20px 0;
    }

    .scale-item {
      background: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      text-align: center;
      transition: transform 0.3s ease;
    }

    .scale-item:hover {
      transform: translateY(-5px);
    }

    .scale-item h3 {
      color: var(--gradient-1);
      margin-bottom: 10px;
      font-size: 1.1rem;
    }

    .scale-item p {
      font-size: 0.9rem;
      color: #555;
    }

    .btn-start {
      display: block;
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.2rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 20px;
      box-shadow: 0 4px 15px rgba(92, 107, 192, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-start:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(92, 107, 192, 0.4);
    }

    .pulse {
      animation: pulse 1.5s infinite;
    }

    /* Quiz Section */
    .question-container {
      background: var(--card);
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      margin-bottom: 25px;
    }

    .question-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .question-scale {
      background: var(--primary-accent);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
    }

    #question {
      font-size: 1.4rem;
      margin-bottom: 20px;
      font-weight: 600;
      line-height: 1.4;
      color: #333;
    }

    .question-image {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
      margin: 15px auto;
      display: block;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      background: #f8f9fa;
      padding: 15px;
    }

    .svg-container {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    .sequence-svg {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 10px;
    }

    .options-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
      margin: 25px 0;
    }

    .option-btn {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 15px;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
      color: #333;
    }

    .option-btn:hover {
      background: #e9ecef;
      transform: translateY(-2px);
    }

    .option-btn.correct {
      background: var(--correct);
      color: white;
      border-color: var(--correct);
    }

    .option-btn.incorrect {
      background: var(--incorrect);
      color: white;
      border-color: var(--incorrect);
    }

    .timer-container {
      display: flex;
      align-items: center;
      gap: 15px;
      background: #f1f3f5;
      padding: 15px;
      border-radius: 12px;
      margin: 20px 0;
      color: #333;
    }

    .timer-icon {
      font-size: 1.5rem;
      color: var(--gradient-1);
    }

    #timer {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--gradient-1);
    }

    .progress-container {
      margin: 30px 0;
    }

    .progress-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      color: #333;
    }

    .progress-bar {
      height: 12px;
      background: #e9ecef;
      border-radius: 10px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--primary-accent), var(--gradient-1));
      width: 0%;
      transition: width 0.5s ease;
      border-radius: 10px;
    }

    /* Results Section */
    .result-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .result-header h2 {
      color: var(--gradient-1);
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .iq-score {
      font-size: 3.5rem;
      font-weight: 800;
      color: var(--gradient-1);
      margin: 20px 0;
      text-align: center;
    }

    .iq-description {
      text-align: center;
      max-width: 600px;
      margin: 0 auto 30px;
      font-size: 1.1rem;
      line-height: 1.6;
      color: #333;
    }

    .scales-results {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin: 30px 0;
    }

    .scale-result {
      background: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      text-align: center;
      transition: all 0.3s ease;
    }

    .scale-result:hover {
      transform: translateY(-5px);
    }

    .scale-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--gradient-1);
    }

    .scale-score {
      font-size: 2rem;
      font-weight: 700;
      margin: 10px 0;
      color: #333;
    }

    .scale-bar {
      height: 10px;
      background: #e9ecef;
      border-radius: 5px;
      margin: 15px 0;
      overflow: hidden;
    }

    .scale-fill {
      height: 100%;
      background: var(--primary-accent);
      width: 0%;
      transition: width 1s ease;
    }

    .interpretation {
      background: linear-gradient(135deg, rgba(227, 242, 253, 0.7), rgba(187, 222, 251, 0.7));
      border-radius: 15px;
      padding: 25px;
      margin: 30px 0;
    }

    .interpretation h3 {
      color: var(--gradient-1);
      margin-bottom: 15px;
      font-size: 1.4rem;
    }

    .interpretation p {
      color: #333;
      line-height: 1.6;
    }

    .btn-restart {
      display: block;
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.2rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 20px auto;
      max-width: 300px;
      box-shadow: 0 4px 15px rgba(92, 107, 192, 0.3);
      text-align: center;
    }

    .btn-restart:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(92, 107, 192, 0.4);
    }

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
      color:white;
    }

    /* Buton teme */
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

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
      }
      
      .header-title h1 {
        font-size: 1.5rem;
      }
      
      .options-grid {
        grid-template-columns: 1fr;
      }
      
      .scale-info, .scales-results {
        grid-template-columns: 1fr;
      }
      
      #question {
        font-size: 1.2rem;
      }
      
      #theme-btn {
        bottom: 20px;
        left: 20px;
      }
      
      #theme-selector {
        bottom: 90px;
        left: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
  <header>
    <div class="header-title">
        <h1><i class="fas fa-brain"></i> Test de Inteligență WISC pentru Copii</h1>
        <p>Vârsta 10-14 ani | Evaluare pe patru subscare</p>
    </div>
    <div class="nav-icons">
        <button onclick="location.reload()"><i class="fas fa-redo"></i></button>
        <button id="infoBtn"><i class="fas fa-info-circle"></i></button>
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

    <div id="intro" class="content-section active">
      <div class="info-card">
        <h2>Bun venit la Testul WISC pentru Copii!</h2>
        <p>Acest test a fost special adaptat pentru copiii cu vârsta între 10 și 14 ani și evaluează patru dimensiuni importante ale inteligenței:</p>
        
        <div class="scale-info">
          <div class="scale-item">
            <h3>VCI - Înțelegere Verbală</h3>
            <p>Abilități verbale, vocabular și raționament bazat pe cuvinte</p>
          </div>
          <div class="scale-item">
            <h3>PRI - Raționament Perceptiv</h3>
            <p>Abilități vizuo-spațiale și rezolvare de probleme non-verbale</p>
          </div>
          <div class="scale-item">
            <h3>WMI - Memorie de Lucru</h3>
            <p>Capacitatea de a reține și manipula informații pe termen scurt</p>
          </div>
          <div class="scale-item">
            <h3>PSI - Viteză de Procesare</h3>
            <p>Viteza de procesare a informațiilor și atenția vizuală</p>
          </div>
        </div>
        
        <p><strong>Instrucțiuni:</strong> Vei răspunde la 20 de întrebări cu timp limitat. Răspunde cât mai corect și mai rapid posibil!</p>
      </div>
      
      <button id="startBtn" class="btn-start pulse">Începe Testul <i class="fas fa-play"></i></button>
    </div>

    <div id="quiz" class="content-section">
      <div class="question-container">
        <div class="question-header">
          <div class="question-number">Întrebarea <span id="current">1</span>/20</div>
          <div class="question-scale" id="scaleIndicator">VCI</div>
        </div>
        
        <h2 id="question">Încărcare întrebare...</h2>
        <div id="imageContainer" class="svg-container"></div>
        
        <div class="options-grid" id="options"></div>
      </div>
      
      <div class="timer-container">
        <div class="timer-icon"><i class="fas fa-clock"></i></div>
        <div id="timer">Timp rămas: 30 secunde</div>
      </div>
      
      <div class="progress-container">
        <div class="progress-header">
          <span>Progres test</span>
          <span id="progressPercent">0%</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" id="progressBar"></div>
        </div>
      </div>
    </div>

    <div id="result" class="content-section">
      <div class="result-header">
        <h2><i class="fas fa-star"></i> Rezultate Finale</h2>
        <p>Evaluare completă a performanței pe patru subscare</p>
        
        <div class="iq-score">126</div>
        <div class="iq-description">
          Felicitări! Scorul tău se încadrează în categoria superioară. 
          Ai demonstrat abilități excelente de raționament și rezolvare de probleme.
        </div>
      </div>
      
      <div class="scales-results">
        <div class="scale-result">
          <div class="scale-title">VCI - Înțelegere Verbală</div>
          <div class="scale-score">18</div>
          <div class="scale-description">Excelent</div>
          <div class="scale-bar">
            <div class="scale-fill" style="width: 90%"></div>
          </div>
          <p>Abilități verbale foarte bine dezvoltate</p>
        </div>
        
        <div class="scale-result">
          <div class="scale-title">PRI - Raționament Perceptiv</div>
          <div class="scale-score">16</div>
          <div class="scale-description">Superior</div>
          <div class="scale-bar">
            <div class="scale-fill" style="width: 80%"></div>
          </div>
          <p>Raționament vizual foarte bun</p>
        </div>
        
        <div class="scale-result">
          <div class="scale-title">WMI - Memorie de Lucru</div>
          <div class="scale-score">15</div>
          <div class="scale-description">Superior</div>
          <div class="scale-bar">
            <div class="scale-fill" style="width: 75%"></div>
          </div>
          <p>Memorie excelentă pe termen scurt</p>
        </div>
        
        <div class="scale-result">
          <div class="scale-title">PSI - Viteză de Procesare</div>
          <div class="scale-score">14</div>
          <div class="scale-description">Mediu-Superior</div>
          <div class="scale-bar">
            <div class="scale-fill" style="width: 70%"></div>
          </div>
          <p>Viteză bună de procesare</p>
        </div>
      </div>
      
      <div class="interpretation">
        <h3>Interpretare rezultate</h3>
        <p>Rezultatele indică un potențial intelectual ridicat, cu puncte forte deosebite în domeniul abilităților verbale și de raționament. Scorul total se încadrează în categoria superioară, indicând o capacitate excelentă de învățare și rezolvare de probleme.</p>
        <p>Punctele tale forte în VCI și PRI sugerează că te descurci excelent atât în sarcinile care implică limbajul, cât și în cele care necesită raționament vizual și abstract.</p>
      </div>
      
      <button class="btn-restart" onclick="location.reload()"><i class="fas fa-redo"></i> Refă Testul</button>
    </div>
  </div>

  <div id="infoModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Despre Testul WISC</h2>
        <button class="close-modal">&times;</button>
      </div>
      <p>Testul WISC (Wechsler Intelligence Scale for Children) este un instrument de evaluare a inteligenței pentru copiii cu vârste între 6 și 16 ani.</p>
      <p>Acest test online este o adaptare simplificată care evaluează patru domenii cognitive principale:</p>
      <ul style="margin: 15px 0; padding-left: 20px;">
        <li>Înțelegere verbală (VCI)</li>
        <li>Raționament perceptiv (PRI)</li>
        <li>Memorie de lucru (WMI)</li>
        <li>Viteză de procesare (PSI)</li>
      </ul>
      <p>Testul conține 20 de întrebări cu timp limitat. Pentru rezultate precise, este important să răspunzi cât mai corect și mai rapid posibil.</p>
      <p><strong>Notă:</strong> Acesta este un test de orientare și nu înlocuiește o evaluare psihologică completă.</p>
    </div>
  </div>
  
  <!-- Buton teme -->
  <button id="theme-btn" title="Schimbă tema">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4361ee" data-g2="#3f37c9" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
  </div>

  <script>
    // Intrebări revizuite bazate pe standarde WISC reale
    const questions = [
      // VCI - Înțelegere Verbală
      {
        question: "Ce înseamnă cuvântul 'generos'?",
        options: ["zgârcit", "dăruitor", "egoist", "avaros"],
        answer: "dăruitor",
        scale: "VCI",
        timeLimit: 15
      },
      {
        question: "Cum sunt asemănătoare un caiet și o carte?",
        options: ["Ambele sunt verzi", "Ambele au copertă", "Ambele sunt obiecte de învățare", "Ambele sunt pătrate"],
        answer: "Ambele sunt obiecte de învățare",
        scale: "VCI",
        timeLimit: 20
      },
      {
        question: "Care este antonimul cuvântului 'rapid'?",
        options: ["vitejos", "încet", "accelerat", "grabnic"],
        answer: "încet",
        scale: "VCI",
        timeLimit: 12
      },
      {
        question: "Ce înseamnă 'a procrastina'?",
        options: ["a lucra intens", "a amâna", "a termina rapid", "a organiza"],
        answer: "a amâna",
        scale: "VCI",
        timeLimit: 18
      },
      {
        question: "Care cuvânt nu se potrivește cu celelalte?",
        options: ["trandafir", "lalea", "brad", "crin"],
        answer: "brad",
        scale: "VCI",
        timeLimit: 15
      },
      
      // PRI - Raționament Perceptiv
      {
        question: "Care formă completează șirul logic?",
        svg: `
          <svg class="sequence-svg" width="300" height="120" viewBox="0 0 300 120">
            <rect x="20" y="20" width="40" height="40" fill="#4361ee" rx="5"/>
            <circle cx="100" cy="40" r="20" fill="#4cc9f0"/>
            <rect x="160" y="20" width="40" height="40" fill="#4361ee" rx="5"/>
            <circle cx="220" cy="40" r="20" fill="#4cc9f0"/>
            <rect x="260" y="20" width="40" height="40" fill="#4361ee" rx="5" stroke="#f87171" stroke-width="3"/>
            <text x="30" y="90" font-size="12">1</text>
            <text x="90" y="90" font-size="12">2</text>
            <text x="150" y="90" font-size="12">3</text>
            <text x="210" y="90" font-size="12">4</text>
            <text x="270" y="90" font-size="12">?</text>
          </svg>
        `,
        options: ["A", "B", "C", "D"],
        answer: "C",
        scale: "PRI",
        timeLimit: 25
      },
      {
        question: "Care formă este diferită?",
        svg: `
          <svg class="sequence-svg" width="300" height="120" viewBox="0 0 300 120">
            <rect x="20" y="20" width="50" height="50" fill="#4361ee" rx="10"/>
            <circle cx="100" cy="45" r="25" fill="#4cc9f0"/>
            <rect x="160" y="20" width="50" height="50" fill="#4361ee" rx="10"/>
            <circle cx="240" cy="45" r="25" fill="#4cc9f0" stroke="#f87171" stroke-width="3"/>
            <text x="25" y="90" font-size="12">1</text>
            <text x="95" y="90" font-size="12">2</text>
            <text x="155" y="90" font-size="12">3</text>
            <text x="235" y="90" font-size="12">4</text>
          </svg>
        `,
        options: ["1", "2", "3", "4"],
        answer: "4",
        scale: "PRI",
        timeLimit: 20
      },
      {
        question: "Ce formă ar trebui să fie în locul semnului întrebării?",
        svg: `
          <svg class="sequence-svg" width="300" height="120" viewBox="0 0 300 120">
            <circle cx="50" cy="40" r="20" fill="#4361ee"/>
            <rect x="100" y="20" width="40" height="40" fill="#4cc9f0" rx="5"/>
            <circle cx="170" cy="40" r="20" fill="#4361ee"/>
            <rect x="220" y="20" width="40" height="40" fill="#4cc9f0" rx="5"/>
            <rect x="270" y="20" width="40" height="40" fill="#4361ee" rx="5" stroke="#f87171" stroke-width="3"/>
            <text x="40" y="90" font-size="12">A</text>
            <text x="90" y="90" font-size="12">B</text>
            <text x="160" y="90" font-size="12">C</text>
            <text x="210" y="90" font-size="12">D</text>
            <text x="260" y="90" font-size="12">?</text>
          </svg>
        `,
        options: ["Cerc", "Pătrat", "Triunghi", "Dreptunghi"],
        answer: "Cerc",
        scale: "PRI",
        timeLimit: 30
      },
      {
        question: "Care dintre acestea este oglindirea corectă a formei?",
        svg: `
          <svg class="sequence-svg" width="300" height="120" viewBox="0 0 300 120">
            <polygon points="30,60 50,20 70,60" fill="#4361ee"/>
            <rect x="120" y="20" width="40" height="40" fill="#4cc9f0" rx="5"/>
            <rect x="180" y="20" width="40" height="40" fill="#4cc9f0" rx="5" stroke="#f87171" stroke-width="3"/>
            <polygon points="260,60 280,20 300,60" fill="#4361ee" transform="scale(-1,1) translate(-560,0)"/>
            <text x="40" y="100" font-size="12">Original</text>
            <text x="120" y="100" font-size="12">A</text>
            <text x="180" y="100" font-size="12">B</text>
            <text x="260" y="100" font-size="12">C</text>
          </svg>
        `,
        options: ["A", "B", "C", "Niciuna"],
        answer: "C",
        scale: "PRI",
        timeLimit: 25
      },
      {
        question: "Care formă completează modelul?",
        svg: `
          <svg class="sequence-svg" width="300" height="120" viewBox="0 0 300 120">
            <circle cx="50" cy="40" r="20" fill="#4361ee"/>
            <circle cx="120" cy="40" r="20" fill="#4cc9f0"/>
            <circle cx="190" cy="40" r="20" fill="#4361ee"/>
            <circle cx="260" cy="40" r="20" fill="#4cc9f0" stroke="#f87171" stroke-width="3"/>
            <rect x="30" y="80" width="40" height="40" fill="#4361ee" rx="5"/>
            <rect x="100" y="80" width="40" height="40" fill="#4cc9f0" rx="5"/>
            <rect x="170" y="80" width="40" height="40" fill="#4361ee" rx="5"/>
            <rect x="240" y="80" width="40" height="40" fill="#4cc9f0" rx="5"/>
            <text x="40" y="35" font-size="12">?</text>
          </svg>
        `,
        options: ["A", "B", "C", "D"],
        answer: "A",
        scale: "PRI",
        timeLimit: 25
      },
      
      // WMI - Memorie de Lucru
      {
        question: "Ține minte: 4, 7, 2. Repetă invers.",
        options: ["2, 7, 4", "4, 7, 2", "7, 4, 2", "2, 4, 7"],
        answer: "2, 7, 4",
        scale: "WMI",
        timeLimit: 20
      },
      {
        question: "Ține minte: 8, 3, 6, 1. Repetă invers.",
        options: ["1, 6, 3, 8", "8, 3, 6, 1", "6, 3, 8, 1", "1, 3, 6, 8"],
        answer: "1, 6, 3, 8",
        scale: "WMI",
        timeLimit: 30
      },
      {
        question: "Ține minte: 9, 2. Repetă invers.",
        options: ["9, 2", "2, 9", "6, 2", "2, 6"],
        answer: "2, 9",
        scale: "WMI",
        timeLimit: 15
      },
      {
        question: "Ține minte: 5, 1, 7, 3. Repetă invers.",
        options: ["3, 7, 1, 5", "5, 1, 7, 3", "1, 3, 5, 7", "7, 3, 1, 5"],
        answer: "3, 7, 1, 5",
        scale: "WMI",
        timeLimit: 30
      },
      {
        question: "Ține minte: 6, 4, 9. Repetă invers.",
        options: ["9, 4, 6", "6, 4, 9", "4, 6, 9", "9, 6, 4"],
        answer: "9, 4, 6",
        scale: "WMI",
        timeLimit: 20
      },
      
      // PSI - Viteză de Procesare
      {
        question: "Găsește cifra 5 cât mai repede: 3 5 7 9",
        options: ["3", "5", "7", "9"],
        answer: "5",
        scale: "PSI",
        timeLimit: 6
      },
      {
        question: "Selectează litera 'R' rapid: P Q R S",
        options: ["P", "Q", "R", "S"],
        answer: "R",
        scale: "PSI",
        timeLimit: 6
      },
      {
        question: "Găsește cifra 3 cât mai repede: 1 3 5 7",
        options: ["1", "3", "5", "7"],
        answer: "3",
        scale: "PSI",
        timeLimit: 6
      },
      {
        question: "Selectează litera 'K' rapid: J K L M",
        options: ["J", "K", "L", "M"],
        answer: "K",
        scale: "PSI",
        timeLimit: 6
      },
      {
        question: "Găsește cifra 8 cât mai repede: 2 4 6 8",
        options: ["2", "4", "6", "8"],
        answer: "8",
        scale: "PSI",
        timeLimit: 6
      }
    ];

    let currentQuestion = 0;
    let scores = { VCI: 0, PRI: 0, WMI: 0, PSI: 0 };
    let timeBonus = { VCI: 0, PRI: 0, WMI: 0, PSI: 0 };
    let timer;
    let timeLeft;
    let startTime;

    // Elemente DOM
    const introSection = document.getElementById('intro');
    const quizSection = document.getElementById('quiz');
    const resultSection = document.getElementById('result');
    const startBtn = document.getElementById('startBtn');
    const questionElement = document.getElementById('question');
    const imageContainer = document.getElementById('imageContainer');
    const optionsContainer = document.getElementById('options');
    const timerElement = document.getElementById('timer');
    const currentElement = document.getElementById('current');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const scaleIndicator = document.getElementById('scaleIndicator');
    const infoBtn = document.getElementById('infoBtn');
    const infoModal = document.getElementById('infoModal');
    const closeModal = document.querySelector('.close-modal');
    const themeBtn = document.getElementById('theme-btn');
    const themeSelector = document.getElementById('theme-selector');

    // Butonul de start
    startBtn.addEventListener('click', startQuiz);
    
    // Modal
    infoBtn.addEventListener('click', () => {
      infoModal.style.display = 'flex';
    });
    
    closeModal.addEventListener('click', () => {
      infoModal.style.display = 'none';
    });
    
    window.addEventListener('click', (e) => {
      if (e.target === infoModal) {
        infoModal.style.display = 'none';
      }
    });

    function startQuiz() {
      introSection.classList.remove('active');
      quizSection.classList.add('active');
      loadQuestion();
    }

    function loadQuestion() {
      clearInterval(timer);
      const q = questions[currentQuestion];
      
      // Actualizare întrebare
      questionElement.textContent = q.question;
      currentElement.textContent = currentQuestion + 1;
      scaleIndicator.textContent = q.scale;
      
      // Actualizare imagine (dacă există)
      imageContainer.innerHTML = '';
      if (q.svg) {
        imageContainer.innerHTML = q.svg;
      }
      
      // Actualizare opțiuni
      optionsContainer.innerHTML = '';
      q.options.forEach(option => {
        const button = document.createElement('button');
        button.className = 'option-btn';
        button.textContent = option;
        button.addEventListener('click', () => checkAnswer(option));
        optionsContainer.appendChild(button);
      });
      
      // Actualizare progres
      const progress = ((currentQuestion) / questions.length) * 100;
      progressBar.style.width = `${progress}%`;
      progressPercent.textContent = `${Math.round(progress)}%`;
      
      // Pornire timer
      startTime = new Date();
      startTimer(q.timeLimit);
    }

    function startTimer(seconds) {
      timeLeft = seconds;
      updateTimerDisplay();
      
      timer = setInterval(() => {
        timeLeft--;
        updateTimerDisplay();
        
        if (timeLeft <= 0) {
          clearInterval(timer);
          checkAnswer(null);
        }
      }, 1000);
    }

    function updateTimerDisplay() {
      timerElement.textContent = `Timp rămas: ${timeLeft} secunde`;
    }

    function checkAnswer(selected) {
      clearInterval(timer);
      const q = questions[currentQuestion];
      const endTime = new Date();
      const timeTaken = (endTime - startTime) / 1000; // în secunde
      
      // Calcul bonus de timp (max 0.5 puncte pentru răspuns rapid)
      let timeBonusValue = 0;
      if (selected === q.answer && timeTaken < q.timeLimit) {
        // Bonus proporțional cu timpul rămas (max 0.5 puncte)
        timeBonusValue = Math.min(0.5, (1 - timeTaken / q.timeLimit) * 0.5);
      }
      
      // Marcare răspuns corect/incorect
      if (selected !== null) {
        const options = optionsContainer.querySelectorAll('.option-btn');
        options.forEach(opt => {
          if (opt.textContent === q.answer) {
            opt.classList.add('correct');
          } else if (opt.textContent === selected && selected !== q.answer) {
            opt.classList.add('incorrect');
          }
        });
      }
      
      // Actualizare scor
      if (selected === q.answer) {
        scores[q.scale] += 1 + timeBonusValue;
        timeBonus[q.scale] += timeBonusValue;
      }
      
      // Trecem la următoarea întrebare după o scurtă întârziere
      setTimeout(() => {
        currentQuestion++;
        if (currentQuestion < questions.length) {
          loadQuestion();
        } else {
          showResults();
        }
      }, 1500);
    }

    function showResults() {
      quizSection.classList.remove('active');
      resultSection.classList.add('active');
      
      // Calcul scor total
      const totalScore = Object.values(scores).reduce((a, b) => a + b, 0);
      const iq = Math.floor(100 + totalScore * 1.3);
      
      // Actualizare scor IQ
      document.querySelector('.iq-score').textContent = iq;
      
      // Actualizare descriere IQ
      let description = "";
      if (iq >= 130) {
        description = "Excelent! Scorul tău se încadrează în categoria superioară. Ai demonstrat abilități excepționale de raționament și rezolvare de probleme.";
      } else if (iq >= 115) {
        description = "Foarte bine! Scorul tău se încadrează în categoria superioară. Ai abilități cognitive foarte bine dezvoltate.";
      } else if (iq >= 85) {
        description = "Bun! Scorul tău este în medie. Ai abilități cognitive bine dezvoltate, corespunzătoare vârstei tale.";
      } else {
        description = "Acceptabil! Scorul tău este mai jos de medie. Acesta este doar un test simplificat - pentru o evaluare completă, consultă un specialist.";
      }
      document.querySelector('.iq-description').textContent = description;
      
      // Actualizare scoruri pe subscare
      const scaleElements = document.querySelectorAll('.scale-result');
      scaleElements[0].querySelector('.scale-score').textContent = scores.VCI.toFixed(1);
      scaleElements[1].querySelector('.scale-score').textContent = scores.PRI.toFixed(1);
      scaleElements[2].querySelector('.scale-score').textContent = scores.WMI.toFixed(1);
      scaleElements[3].querySelector('.scale-score').textContent = scores.PSI.toFixed(1);
      
      // Găsim punctele forte
      const maxScore = Math.max(...Object.values(scores));
      const strengths = [];
      if (scores.VCI === maxScore) strengths.push("VCI (Înțelegere Verbală)");
      if (scores.PRI === maxScore) strengths.push("PRI (Raționament Perceptiv)");
      if (scores.WMI === maxScore) strengths.push("WMI (Memorie de Lucru)");
      if (scores.PSI === maxScore) strengths.push("PSI (Viteză de Procesare)");
      
      // Actualizare interpretare
      const interpretationElement = document.querySelector('.interpretation p');
      if (strengths.length > 0) {
        interpretationElement.innerHTML = `Rezultatele indică un potențial intelectual ridicat, cu puncte forte deosebite în domeniul <strong>${strengths.join('</strong> și <strong>')}</strong>. `;
      } else {
        interpretationElement.innerHTML = "Rezultatele indică o performanță echilibrată pe toate dimensiunile cognitive. ";
      }
      
      interpretationElement.innerHTML += `Scorul total se încadrează în categoria ${iq >= 130 ? "superioară" : iq >= 115 ? "superioară" : iq >= 85 ? "medie" : "sub medie"}, indicând o capacitate ${iq >= 115 ? "excelentă" : "bună"} de învățare și rezolvare de probleme.`;
    }
    
    // Funcționalitatea butonului de teme
    themeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      themeSelector.style.display = themeSelector.style.display === 'block' ? 'none' : 'block';
    });
    
    themeSelector.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        const g1 = btn.dataset.g1;
        const g2 = btn.dataset.g2;
        const b1 = btn.dataset.b1;
        const b2 = btn.dataset.b2;
        
        document.documentElement.style.setProperty('--gradient-1', g1);
        document.documentElement.style.setProperty('--gradient-2', g2);
        document.documentElement.style.setProperty('--btn-grad-start', b1);
        document.documentElement.style.setProperty('--btn-grad-end', b2);
        
        themeSelector.style.display = 'none';
      });
    });
    
    document.addEventListener('click', (e) => {
      if (!themeSelector.contains(e.target) && e.target !== themeBtn) {
        themeSelector.style.display = 'none';
      }
    });
  </script>
</body>
</html>