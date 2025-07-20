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
  <title>Tablă de Scris - Test IQ Avansat</title>
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
      --primary-accent: #ff9d00;
      --text-color: #fff;
      --btn-text: #333;
      --card-bg: rgba(255, 255, 255, 0.2);
      --card-border: rgba(255, 255, 255, 0.3);
      --whiteboard-bg: #f8f9fa;
      --toolbar-bg: #e9ecef;
      --warning: #ff6a00;
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
      max-width: 1200px;
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
    
    /* Conținut principal */
    .content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .question-container {
      background: var(--card-bg);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
      animation: slideUp 0.5s ease-out;
      position: relative;
    }
    
    .question-container h2 {
      margin-bottom: 10px;
      border-bottom: 2px solid rgba(255,255,255,0.3);
      padding-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .question-container h2 i {
      color: var(--primary-accent);
    }
    
    /* Progress bar */
    .progress-container {
      width: 100%;
      background: rgba(255,255,255,0.3);
      height: 8px;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 20px;
    }
    .progress-bar {
      width: 0;
      height: 100%;
      background: var(--primary-accent);
      transition: width 0.5s ease;
    }
    
    .shapes-sequence {
      display: flex;
      justify-content: center;
      gap: 25px;
      margin: 30px 0;
      flex-wrap: wrap;
    }
    
    .sequence-image {
      background: transparent;
      border-radius: 10px;
      overflow: hidden;
      width: 200px;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
    }
    .sequence-image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      background: transparent;
    }
    .option .shape {
      background: transparent;
    }
    .option .shape img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }
    .options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    
    .option {
      background: rgba(255,255,255,0.2);
      padding: 20px;
      border-radius: 15px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .option.text-only {
      padding: 20px;
      text-align: center;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      cursor: pointer;
      transition: all 0.3s;
      font-weight: 600;
      font-size: 18px;
    }
    
    .option:hover {
      background: rgba(255,255,255,0.3);
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .option.selected {
      background: rgba(255, 157, 0, 0.4);
      border: 2px solid var(--primary-accent);
      transform: scale(1.05);
    }
    
    .option p {
      margin-top: 15px;
      font-weight: 600;
      font-size: 18px;
    }
    
    /* Răspuns text */
    .text-answer {
      width: 100%;
      margin-top: 20px;
    }
    .text-answer textarea {
      width: 100%;
      min-height: 100px;
      border-radius: 10px;
      border: 1px solid #ccc;
      padding: 10px;
      font-size: 16px;
    }
    
    /* Timer display */
    .timer-display {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 18px;
      background: rgba(0,0,0,0.3);
      padding: 5px 10px;
      border-radius: 8px;
    }
    
    /* Tablă de scris */
    .whiteboard-section {
      display: none;
      flex-direction: column;
      background: var(--whiteboard-bg);
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      margin: 20px 0;
      overflow: hidden;
      border: 1px solid #ddd;
      animation: slideIn 0.4s ease-out;
    }
    
    .whiteboard-header {
      padding: 15px 20px;
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .whiteboard-tools {
      display: flex;
      padding: 12px 15px;
      background: var(--toolbar-bg);
      border-bottom: 1px solid #ddd;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .tool-btn {
      padding: 10px 18px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 500;
      color: #333;
      font-size: 16px;
    }
    
    .tool-btn.active {
      background: var(--primary-accent);
      color: white;
      border-color: var(--primary-accent);
    }
    
    .tool-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    #color-picker {
      height: 38px;
      border: none;
      cursor: pointer;
      background: white;
      border-radius: 8px;
      padding: 3px;
    }
    
    .whiteboard-canvas-container {
      height: 350px;
      overflow: hidden;
      position: relative;
      background: white;
    }
    
    #whiteboard-canvas {
      background: white;
      cursor: crosshair;
      width: 100%;
      height: 100%;
    }
    
    /* Butoane de control */
    .controls {
      display: flex;
      justify-content: space-between;
      margin-top: 25px;
      gap: 15px;
      flex-wrap: wrap;
    }
    
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
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex: 1;
      min-width: 150px;
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn:active {
      transform: translateY(1px);
    }
    
    .btn-whiteboard {
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      color: white;
    }
    
    .btn-review {
      background: linear-gradient(135deg, #ff9d00, #ff6a00);
      color: white;
      margin-top: 20px;
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
    
    .theme-btn, .lang-btn {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border: none;
      border-radius: 50%;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: var(--btn-text);
      transition: all 0.3s ease;
    }
    
    .theme-btn:hover, .lang-btn:hover {
      transform: rotate(15deg) scale(1.1);
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    
    #theme-selector {
      position: fixed;
      bottom: 100px;
      left: 25px;
      background: rgba(255,255,255,0.95);
      padding: 15px;
      border-radius: 15px;
      display: none;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      z-index: 100;
      border: 1px solid rgba(0,0,0,0.1);
      animation: fadeIn 0.3s ease-out;
    }
    
    #theme-selector button {
      margin: 8px;
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      transition: all 0.2s;
    }
    
    #theme-selector button:hover {
      transform: translateY(-3px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    
    /* Rezultate */
    .results-container {
      background: var(--card-bg);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      border: 1px solid var(--card-border);
      animation: slideUp 0.5s ease-out;
      text-align: center;
    }
    
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s;
    }
    
    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 20px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      color: #333;
      text-align: center;
    }
    
    .modal h3 {
      margin-bottom: 20px;
      font-size: 24px;
      color: var(--warning);
    }
    
    .modal p {
      margin-bottom: 25px;
      font-size: 18px;
      line-height: 1.5;
    }
    
    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .modal-btn {
      padding: 12px 25px;
      border: none;
      border-radius: 50px;
      font-weight: bold;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s;
      min-width: 120px;
    }
    
    .modal-btn.confirm {
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      color: var(--btn-text);
    }
    
    .modal-btn.mark {
      background: linear-gradient(135deg, #ff9d00, #ff6a00);
      color: white;
    }
    
    .modal-btn.cancel {
      background: #e0e0e0;
      color: #333;
    }
    
    .modal-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes fadeOut {
      from { opacity: 1; }
      to { opacity: 0; }
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
      
      .question-container {
        padding: 20px;
      }
      
      .progress-container {
        margin-bottom: 15px;
      }
      
      .shapes-sequence {
        gap: 15px;
      }
      
      .shape,
      .sequence-image {
        width: 50px;
        height: 50px;
      }
      
      .shape.triangle {
        border-left: 25px solid transparent;
        border-right: 25px solid transparent;
        border-bottom: 45px solid #ff6a00;
      }
      
      .options {
        grid-template-columns: 1fr;
      }
      
      .controls {
        flex-direction: column;
      }
      
      .btn {
        min-width: 100%;
      }
      
      .floating-controls {
        flex-direction: column;
        bottom: 20px;
        left: 20px;
      }
      
      #theme-selector {
        bottom: 150px;
      }
      
      /* Adjust dropdown position on mobile */
      .dropdown-menu {
        top: 75px;
        right: 10px;
        min-width: 180px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="nav-icons">
        <button title="Home"><a href="home.php"><svg viewBox="0 0 576 512"><path d="M280.37 148.26L96 300.11V464a16 16 0 0016 16l112-.29a16 16 0 0016-15.74V368a16 16 0 0116-16h64a16 16 0 0116 16v96a16 16 0 0016 16l112 .29a16 16 0 0016-16V300L295.67 148.26a12.19 12.19 0 00-15.3 0z"/></svg></a></button>
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
      <div class="question-container" id="question-container">
        <h2><i class="fas fa-question-circle"></i> Întrebarea <span id="question-number"></span></h2>
        <div class="progress-container"><div class="progress-bar" id="progress-bar"></div></div>
        <div class="timer-display" id="timer-display">00:00</div>
        <p id="question-prompt"></p>
        <div id="sequence-container" class="shapes-sequence"></div>
        <div id="response-container"></div>
        
        <div class="whiteboard-section" id="whiteboard-section">
          <div class="whiteboard-header">
            <h3><i class="fas fa-paint-brush"></i> Tablă de Scris</h3>
            <div style="font-size: 0.9em;">Desenează pentru a rezolva problema</div>
          </div>
          
          <div class="whiteboard-tools">
            <button class="tool-btn active" id="pen-tool">
              <i class="fas fa-pen"></i> Pix
            </button>
            <button class="tool-btn" id="eraser-tool">
              <i class="fas fa-eraser"></i> Radieră
            </button>
            <input type="color" id="color-picker" value="#000000">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: #333; font-weight:500;">Grosime:</span>
              <input type="range" id="brush-size" min="1" max="20" value="5">
            </div>
            <button class="tool-btn" id="clear-btn">
              <i class="fas fa-trash-alt"></i> Șterge tot
            </button>
          </div>
          
          <div class="whiteboard-canvas-container">
            <canvas id="whiteboard-canvas"></canvas>
          </div>
        </div>
        
        <div class="controls">
          <button class="btn" id="prev-btn">
            <i class="fas fa-arrow-left"></i> Înapoi
          </button>
          
          <button class="btn btn-mark" id="mark-question-btn">
            <i class="fas fa-flag"></i> Marchează
          </button>
          
          <button class="btn btn-whiteboard" id="toggle-whiteboard-btn">
            <i class="fas fa-chalkboard"></i> Tablă de scris
          </button>
          
          <button class="btn" id="next-btn">
            <span id="next-btn-text"></span> <i class="fas fa-arrow-right"></i>
          </button>
        </div>
        
        <!-- Buton pentru reluarea întrebărilor marcate -->
        <button class="btn btn-review" id="review-marked-btn" style="display: none; margin-top: 20px;">
          <i class="fas fa-redo"></i> Reia întrebările marcate
        </button>
      </div>
    </div>
  </div>
  
  <!-- Modal pentru confirmare trecere peste întrebare -->
  <div id="skip-confirm-modal" class="modal">
    <div class="modal-content">
      <h3>Confirmă</h3>
      <p id="skip-modal-message">Nu ai răspuns la întrebare. Ești sigur că vrei să treci mai departe?</p>
      <p>Dacă o marchezi, o poți revizui mai târziu.</p>
      <div class="modal-buttons">
        <button class="modal-btn confirm" id="skip-btn">Treci peste</button>
        <button class="modal-btn mark" id="mark-skip-btn">Marchează și treci peste</button>
        <button class="modal-btn cancel" id="cancel-skip-btn">Anulează</button>
      </div>
    </div>
  </div>
  
  <!-- Butoane flotante -->
  <div class="floating-controls">
    <button class="theme-btn" id="theme-btn">🎨</button>
  </div>
  
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Define questions array
      const questions = [
        {
          id: 1,
          prompt: 'Care este următoarea formă în secvență?',
          type: 'mcq',
          options: [
            { value: 'A', img: 'A.jpg', label: 'Cerc' },
            { value: 'B', img: 'B.jpg', label: 'Pătrat' },
            { value: 'C', img: 'C.jpg', label: 'Romb' },
            { value: 'D', img: 'D.jpg', label: 'Triunghi' }
          ],
          sequenceImages: [
            'romb.jpg'
          ],
          correctAnswer: 'C'
        },
        {
          id: 2,
          prompt: 'Dacă 473982 îi corespunde lui 1419 și 329684 lui 1418 atunci 751694 îi corespunde lui?',
          type: 'text',
          correctAnswer: '1420'
        },
        {
          id: 3,
          prompt: 'Ce cuvânt dintre paranteze este cel mai apropiat ca sens de cuvântul scris cu majuscule? SUBSCRIERE',
          type: 'mcq',
          options: [
            { value: 'A',  label: 'citare' },
            { value: 'B',  label: 'încuviințare' },
            { value: 'C',  label: 'completare' },
            { value: 'D',  label: 'invitare' },
            { value: 'E',  label: 'contribuire' },
          ],
          correctAnswer: 'B'
        },
        {
          id: 4,
          prompt: 'Rezolvați anagrama dintre paranteze pentru a completa citatul: "A scrie despre muzică este ca și cum ai dansa despre... (CHIAR TEATRU)"',
          type: 'text',
          correctAnswer: 'artă'
        },
        {
          id: 5,
          prompt: 'Ce înseamnă sedițiune?',
          type: 'mcq',
          options: [
            { value: 'A',  label: 'reacţie faţă de stimulente' },
            { value: 'B',  label: 'inducere a stării de calm' },
            { value: 'C',  label: 'cu referire la băuturi' },
            { value: 'D',  label: 'acțiune violentă împotriva unei nedreptăți' },
            { value: 'E',  label: 'acumulare sub formă de straturi' },
          ],
          correctAnswer: 'D'
        },
         {
        id: 6,
          prompt: 'Ce cuvânt nu se potrivește cu celelalte?',
          type: 'mcq',
          options: [
            { value: 'A',  label: 'parbriz' },
            { value: 'B',  label: 'portal' },
            { value: 'C',  label: 'bovindou' },
            { value: 'D',  label: 'hublou' },
          ],
          correctAnswer: 'B'
        },
        {
          id: 7,
          prompt: '975, 319, 753   Ce număr continuă în mod logic seria de mai sus?', 
          type: 'text',
          correctAnswer: '197'
        },
        {
          id: 8,
          prompt: 'Ce număr nu se potrivește cu celelalte?',
          type: 'mcq',
          options: [
            { value: 'A',  label: '983' },
            { value: 'B',  label: '5893' },
            { value: 'C',  label: '315' },
            { value: 'D',  label: '6741' }
          ],
          sequenceImages: [
            '8.jpg'
          ],
          correctAnswer: 'C'
        },
        {
          id: 9,
          prompt: 'Ce număr ar trebui să înlocuiască semnul de întrebare',
          type: 'mcq',
          options: [
            { value: 'A',  label: '5' },
            { value: 'B',  label: '9' },
            { value: 'C',  label: '7' },
            { value: 'D',  label: '6' }
          ],
          sequenceImages: [
            '9.png'
          ],
          correctAnswer: 'D'
        },
        {
          id: 10,
          prompt: 'Ce figură nu se potrivește cu celelalte?',
          type: 'mcq',
          options: [
            { value: 'A', img: '10A.jpeg'},
            { value: 'B', img: '10B.jpeg'},
            { value: 'C', img: '10C.jpeg'},
            { value: 'D', img: '10D.jpeg' },
            { value: 'E', img: '10E.jpeg' },
            { value: 'F', img: '10F.jpeg' }
          ],
          
          correctAnswer: 'B'
        },
        {
        id: 11,
          prompt: 'Suma vårstelor lui Archibald şi Bertie este 19. Suma vârstelor lui Archibald şi Charlie este 37. Suma vårstelor lui Bertie și Charlie este 52. Căți ani are Archibald:',
          type: 'mcq',
          options: [
            { value: 'A',  label: '14' },
            { value: 'B',  label: '17' },
            { value: 'C',  label: '35' },
            { value: 'D',  label: '2' },
          ],
          correctAnswer: 'D'
        },
        {
          id: 12,
          prompt: 'Găsiți cuvântul!  ---ERRE--',
          type: 'text',
          correctAnswer: 'interregn'
        },
        {
          id: 13,
          prompt: 'Dintre figurile următoare, care completează în mod logic seria de mai sus?',
          type: 'mcq',
          options: [
            { value: 'A', img: '13A.png'},
            { value: 'B', img: '13B.png'},
            { value: 'C', img: '13C.png'},
            { value: 'D', img: '13D.png' },
            { value: 'E', img: '13E.png' }
          ],
          sequenceImages: [
            '13C1.png',
            '13C2.png',
            '13C3.png'
          ],
          correctAnswer: 'A'
        },
        {
          id: 14,
          type: 'mcq',
          options: [
            { value: 'A', img: '14A.jpeg'},
            { value: 'B', img: '14B.jpeg'},
            { value: 'C', img: '14C.jpeg'},
            { value: 'D', img: '14D.jpeg' },
            { value: 'E', img: '14E.jpeg' }
          ],
          sequenceImages: [
            '14C1.png',
            '14C2.png',
            '14C3.png'

          ],
          correctAnswer: 'D'
        },
        {
          id: 15,
          prompt: 'Porniți de la o literă aflată într-un colț și descrieți o spirală în sensul acelor de ceasornic în perimetrul dat terminând la litera din centru, pentru a obține un cuvânt format din nouă litere.Trebuie să completați literele lipsă.',
          type: 'text',
          correctAnswer: 'impartial',
        sequenceImages: [
            '15RR.jpeg'
          ],
        },
      ];
      
      let currentQuestionIndex = 0;
      let markedQuestions = [];
      let currentLang = 'ro';
      let score = 0;
      let timerDuration = 30;
      let timerInterval = null;
      let userAnswers = {};
      let isReviewMode = false;
      let originalQuestions = [...questions]; // Salvează întrebările originale

      const translations = {
        ro: { 
          home: "Acasă", 
          contact: "Contact", 
          login: "Autentificare", 
          question: "Întrebarea ", 
          next: "Următorul", 
          finish: "Finalizează testul", 
          circle: "Cerc", 
          square: "Pătrat", 
          rhombus: "Romb", 
          triangle: "Triunghi", 
          whiteboard: "Tablă de Scris", 
          drawHint: "Desenează pentru a rezolva problema", 
          pen: "Pix", 
          eraser: "Radieră", 
          thickness: "Grosime:", 
          clear: "Șterge tot", 
          prev: "Înapoi", 
          mark: "Marchează", 
          unmark: "Detașează", 
          timeUp: "Timp expirat!", 
          results: "Rezultate", 
          restart: "Reia quiz-ul", 
          review: "Reia întrebările marcate",
          confirmSkip: "Nu ai răspuns la întrebare. Ești sigur că vrei să treci mai departe?",
          skipWarning: "Dacă o marchezi, o poți revizui mai târziu.",
          skip: "Treci peste",
          markSkip: "Marchează și treci peste",
          cancel: "Anulează",
          reviewMarked: "Reia întrebările marcate",
          noMarked: "Nu ai nicio întrebare marcată!",
          reviewMode: "Mod Revizuire",
          reviewComplete: "Revizuire completă"
        },
        en: { 
          home: "Home", 
          contact: "Contact", 
          login: "Login", 
          question: "Question ", 
          next: "Next", 
          finish: "Finish test", 
          circle: "Circle", 
          square: "Square", 
          rhombus: "Rhombus", 
          triangle: "Triangle", 
          whiteboard: "Whiteboard", 
          drawHint: "Draw to solve the problem", 
          pen: "Pen", 
          eraser: "Eraser", 
          thickness: "Thickness:", 
          clear: "Clear All", 
          prev: "Previous", 
          mark: "Mark", 
          unmark: "Unmark", 
          timeUp: "Time's up!", 
          results: "Results", 
          restart: "Restart quiz", 
          review: "Review marked questions",
          confirmSkip: "You haven't answered the question. Are you sure you want to continue?",
          skipWarning: "If you mark it, you can review it later.",
          skip: "Skip",
          markSkip: "Mark and Skip",
          cancel: "Cancel",
          reviewMarked: "Review marked questions",
          noMarked: "You have no marked questions!",
          reviewMode: "Review Mode",
          reviewComplete: "Review Complete"
        }
      };

      // Element references
      const qNumberEl = document.getElementById('question-number');
      const qPromptEl = document.getElementById('question-prompt');
      const seqContainer = document.getElementById('sequence-container');
      const respContainer = document.getElementById('response-container');
      const prevBtn = document.getElementById('prev-btn');
      const nextBtn = document.getElementById('next-btn');
      const toggleWBBtn = document.getElementById('toggle-whiteboard-btn');
      const whiteboardSection = document.getElementById('whiteboard-section');
      const timerDisplay = document.getElementById('timer-display');
      const markBtn = document.getElementById('mark-question-btn');
      const questionContainer = document.getElementById('question-container');
      const progressBar = document.getElementById('progress-bar');
      const nextBtnText = document.getElementById('next-btn-text');
      const skipModal = document.getElementById('skip-confirm-modal');
      const skipBtn = document.getElementById('skip-btn');
      const markSkipBtn = document.getElementById('mark-skip-btn');
      const cancelSkipBtn = document.getElementById('cancel-skip-btn');
      const skipMessage = document.getElementById('skip-modal-message');
      const reviewMarkedBtn = document.getElementById('review-marked-btn');

      // Timer functions
      function startTimer() {
        clearTimer();
        let timeLeft = timerDuration;
        updateTimerDisplay(timeLeft);
        timerInterval = setInterval(() => {
          timeLeft--;
          if (timeLeft <= 0) {
            clearTimer();
            handleTimeUp();
          } else {
            updateTimerDisplay(timeLeft);
          }
        }, 1000);
      }
      
      function clearTimer() {
        if (timerInterval) clearInterval(timerInterval);
      }
      
      function updateTimerDisplay(seconds) {
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        timerDisplay.textContent = `${m}:${s}`;
      }
      
      function handleTimeUp() {
        saveAnswer(false);
        goToNextQuestion();
      }

      // Language update
      function updateLanguage() {
        const loginBtn = document.querySelector('.auth-btn');
        if(loginBtn) {
          loginBtn.textContent = translations[currentLang].login;
        }
        
        const registerBtn = document.querySelector('.register-btn');
        if(registerBtn) {
          registerBtn.textContent = translations[currentLang].register;
        }
        
        // Update mark button
        const isMarked = markedQuestions.includes(questions[currentQuestionIndex].id);
        markBtn.innerHTML = `<i class="fas fa-flag"></i> ${
          isMarked ? translations[currentLang].unmark : translations[currentLang].mark
        }`;
        
        // Next button text
        const total = questions.length;
        if (currentQuestionIndex === total - 1) {
          nextBtnText.textContent = isReviewMode ? 
            translations[currentLang].reviewComplete : 
            translations[currentLang].finish;
        } else {
          nextBtnText.textContent = translations[currentLang].next;
        }
      }
      
      // Render question
      function renderQuestion() {
        const q = questions[currentQuestionIndex];
        const total = questions.length;
        qNumberEl.textContent = q.id;
        
        // Set special title for review mode
        const titlePrefix = isReviewMode ? 
          `<span style="color: #ff9d00;">${translations[currentLang].reviewMode}:</span> ` : 
          '';
        document.querySelector('.question-container h2').innerHTML = 
          `<i class="fas fa-question-circle"></i> ${titlePrefix}Întrebarea <span id="question-number">${q.id}</span>`;
        
        qPromptEl.textContent = q.prompt;
        
        // Progress bar
        const percent = ((currentQuestionIndex + 1) / total) * 100;
        progressBar.style.width = percent + '%';
        
        // Sequence images
        seqContainer.innerHTML = '';
        if (q.sequenceImages) {
          q.sequenceImages.forEach(src => {
            const div = document.createElement('div'); 
            div.classList.add('sequence-image');
            const img = document.createElement('img'); 
            img.src = src; 
            img.alt = '';
            div.appendChild(img);
            seqContainer.appendChild(div);
          });
        }
        
        // Response area
        respContainer.innerHTML = '';
        if (q.type === 'mcq' && q.options) {
          const optsDiv = document.createElement('div'); 
          optsDiv.classList.add('options');
          
          q.options.forEach(opt => {
            const optDiv = document.createElement('div'); 
            optDiv.dataset.value = opt.value;
            
            // Check if it's a text-only option
            if (opt.img) {
              optDiv.classList.add('option');
              const shapeDiv = document.createElement('div'); 
              shapeDiv.classList.add('shape');
              const img = document.createElement('img'); 
              img.src = opt.img; 
              img.alt = opt.value;
              shapeDiv.appendChild(img);
              optDiv.appendChild(shapeDiv);
              
              const p = document.createElement('p');
              p.textContent = `${opt.value}. ${opt.label}`;
              optDiv.appendChild(p);
            } else {
              // Text-only option
              optDiv.classList.add('option', 'text-only');
              optDiv.textContent = `${opt.value}. ${opt.label}`;
            }
            
            // Preselect if already answered
            if (userAnswers[q.id] === opt.value) {
              optDiv.classList.add('selected');
            }
            
            optDiv.addEventListener('click', () => {
              respContainer.querySelectorAll('.option').forEach(o => {
                o.classList.remove('selected');
              });
              optDiv.classList.add('selected');
              userAnswers[q.id] = opt.value;
            });
            
            optsDiv.appendChild(optDiv);
          });
          
          respContainer.appendChild(optsDiv);
        } else if (q.type === 'text') {
          const div = document.createElement('div'); 
          div.classList.add('text-answer');
          const textarea = document.createElement('textarea'); 
          textarea.id = 'text-answer-input'; 
          textarea.placeholder = translations[currentLang].drawHint;
          if (userAnswers[q.id]) {
            textarea.value = userAnswers[q.id];
          }
          div.appendChild(textarea);
          respContainer.appendChild(div);
        }
        
        // Mark button
        const isMarked = markedQuestions.includes(q.id);
        markBtn.innerHTML = `<i class="fas fa-flag"></i> ${
          isMarked ? translations[currentLang].unmark : translations[currentLang].mark
        }`;
        
        // Next button text
        if (currentQuestionIndex === total - 1) {
          nextBtnText.textContent = isReviewMode ? 
            translations[currentLang].reviewComplete : 
            translations[currentLang].finish;
        } else {
          nextBtnText.textContent = translations[currentLang].next;
        }
        
        // Show/hide review button
        if (currentQuestionIndex === originalQuestions.length - 1 && markedQuestions.length > 0 && !isReviewMode) {
          reviewMarkedBtn.style.display = 'block';
          reviewMarkedBtn.textContent = translations[currentLang].reviewMarked;
        } else {
          reviewMarkedBtn.style.display = 'none';
        }
        
        // Update language
        updateLanguage();
        
        // Start timer
        startTimer();
      }

      // Whiteboard functions
      const canvas = document.getElementById('whiteboard-canvas');
      const ctx = canvas.getContext('2d');
      let isDrawing = false;
      let lastX = 0;
      let lastY = 0;
      let currentTool = 'pen';
      let currentColor = '#000000';
      let currentBrushSize = 5;
      
      function initCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        ctx.strokeStyle = currentColor;
        ctx.lineWidth = currentBrushSize;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        clearCanvas();
      }
      
      function clearCanvas() {
        ctx.fillStyle = 'white'; 
        ctx.fillRect(0, 0, canvas.width, canvas.height);
      }
      
      function startDrawing(e) { 
        isDrawing=true; 
        [lastX,lastY]=[e.offsetX,e.offsetY]; 
      }
      
      function draw(e) { 
        if(!isDrawing) return; 
        ctx.beginPath(); 
        ctx.moveTo(lastX,lastY); 
        ctx.lineTo(e.offsetX,e.offsetY); 
        ctx.stroke(); 
        [lastX,lastY]=[e.offsetX,e.offsetY]; 
      }
      
      function stopDrawing() { 
        isDrawing=false; 
      }
      
      function setTool(tool) {
        currentTool=tool;
        document.querySelectorAll('.tool-btn').forEach(btn=>btn.classList.remove('active'));
        if(tool==='pen'){ 
          document.getElementById('pen-tool').classList.add('active'); 
          ctx.strokeStyle=currentColor; 
          ctx.globalCompositeOperation='source-over'; 
        }
        else if(tool==='eraser'){ 
          document.getElementById('eraser-tool').classList.add('active'); 
          ctx.strokeStyle='#FFFFFF'; 
          ctx.globalCompositeOperation='destination-out'; 
        }
      }
      
      function toggleWhiteboard() {
        if(whiteboardSection.style.display==='flex'){ 
          whiteboardSection.style.display='none'; 
          clearTimer(); 
        }
        else{ 
          whiteboardSection.style.display='flex'; 
          initCanvas(); 
        }
      }

      // Save answer
      function saveAnswer(isManual = true) {
        const q = questions[currentQuestionIndex];
        let answer = null;
        
        if(q.type==='mcq'){
          const sel = respContainer.querySelector('.option.selected');
          answer = sel? sel.dataset.value: null;
        } else if(q.type==='text'){
          const ta = document.getElementById('text-answer-input'); 
          answer = ta? ta.value.trim(): null;
        }
        
        // Only save if answer exists
        if (answer !== null) {
          userAnswers[q.id] = answer;
          
          // Calculate score
          const correct = answer && q.correctAnswer ? 
            (answer.toString().toLowerCase() === q.correctAnswer.toString().toLowerCase()) : 
            false;
            
          if(correct) score++;
        }
      }

      

      // Navigation functions
      function goToNextQuestion() {
        clearTimer();
        const total = questions.length;
        if(currentQuestionIndex < total - 1) {
          currentQuestionIndex++; 
          renderQuestion();
        } else {
          if (isReviewMode) {
            // After finishing review, return to original questions
            resetToOriginalQuestions();
          } else {
            showResults();
          }
        }
      }
      
      function goToPrevQuestion() {
        clearTimer();
        if(currentQuestionIndex > 0) { 
          currentQuestionIndex--; 
          renderQuestion(); 
        }
      }
      
      // Show results
      function showResults() {
        questionContainer.style.display = 'none';
        clearTimer();
        const container = document.querySelector('.content');
        container.innerHTML = '';
        
        const resDiv = document.createElement('div'); 
        resDiv.classList.add('results-container');
        
        const total = originalQuestions.length;
        const correctAnswers = Object.keys(userAnswers).filter(qId => {
          const q = originalQuestions.find(q => q.id == qId);
          return q && userAnswers[qId] === q.correctAnswer;
        }).length;
        
        resDiv.innerHTML = `
          <h2>${translations[currentLang].results}</h2>
          <p>Ai răspuns corect la ${correctAnswers} din ${total} întrebări.</p>
          <p>Scor final: ${Math.round((correctAnswers / total) * 100)}%</p>
        `;
        
        const btnRestart = document.createElement('button'); 
        btnRestart.classList.add('btn'); 
        btnRestart.textContent = translations[currentLang].restart;
        btnRestart.addEventListener('click', () => location.reload());
        resDiv.appendChild(btnRestart);
        
        container.appendChild(resDiv);
      }

      // Review marked questions
      function reviewMarked() {
        if(!markedQuestions.length) {
          return;
        }
        
        // Create a new set of questions from the marked ones
        const reviewQuestions = originalQuestions.filter(q => markedQuestions.includes(q.id));
        
        if (reviewQuestions.length === 0) {
          return;
        }
        
        // Replace the current questions with the review set
        questions.length = 0;
        Array.prototype.push.apply(questions, reviewQuestions);
        currentQuestionIndex = 0;
        isReviewMode = true;
        
        // Now re-render the first question of the review set
        renderQuestion();
      }
      
      // Reset to original questions after review
      function resetToOriginalQuestions() {
        // Restore original questions
        questions.length = 0;
        Array.prototype.push.apply(questions, originalQuestions);
        currentQuestionIndex = 0;
        isReviewMode = false;
        
        // Show notification and render first question
        renderQuestion();
      }

      // Toggle question mark
      function toggleMark() {
        const q = questions[currentQuestionIndex];
        const idx = markedQuestions.indexOf(q.id);
        if(idx === -1) {
          markedQuestions.push(q.id);
        } else {
          markedQuestions.splice(idx, 1);
        }
        updateMarkButton();
      }
      
      function updateMarkButton() {
        const isMarked = markedQuestions.includes(questions[currentQuestionIndex].id);
        markBtn.innerHTML = `<i class="fas fa-flag"></i> ${
          isMarked ? translations[currentLang].unmark : translations[currentLang].mark
        }`;
      }

      // Check if question is answered
      function isQuestionAnswered() {
        const q = questions[currentQuestionIndex];
        
        if (q.type === 'mcq') {
          return !!respContainer.querySelector('.option.selected');
        } else if (q.type === 'text') {
          const ta = document.getElementById('text-answer-input');
          return ta && ta.value.trim() !== '';
        }
        return false;
      }

      // Show skip confirmation
      function showSkipConfirmation() {
        skipMessage.textContent = translations[currentLang].confirmSkip;
        skipModal.style.display = 'flex';
      }

      
      document.getElementById('pen-tool').addEventListener('click', () => setTool('pen'));
      document.getElementById('eraser-tool').addEventListener('click', () => setTool('eraser'));
      document.getElementById('toggle-whiteboard-btn').addEventListener('click', toggleWhiteboard);
      document.getElementById('mark-question-btn').addEventListener('click', toggleMark);
      reviewMarkedBtn.addEventListener('click', reviewMarked);
      
      document.getElementById('color-picker').addEventListener('input', e => { 
        currentColor = e.target.value; 
        if(currentTool==='pen') { 
          ctx.strokeStyle = currentColor; 
        } 
      });
      
      document.getElementById('brush-size').addEventListener('input', e => { 
        currentBrushSize = e.target.value; 
        ctx.lineWidth = currentBrushSize; 
      });
      
      document.getElementById('clear-btn').addEventListener('click', () => { 
        clearCanvas(); ; 
      });
      
      canvas.addEventListener('mousedown', startDrawing);
      canvas.addEventListener('mousemove', draw);
      canvas.addEventListener('mouseup', stopDrawing);
      canvas.addEventListener('mouseout', stopDrawing);
      
      const selector = document.getElementById('theme-selector');
      document.getElementById('theme-btn').addEventListener('click', () => { 
        selector.style.display = selector.style.display==='block'?'none':'block'; 
      });
      
      selector.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
        const g1 = btn.dataset.g1, g2 = btn.dataset.g2, b1 = btn.dataset.b1, b2 = btn.dataset.b2;
        document.documentElement.style.setProperty('--gradient-1', g1);
        document.documentElement.style.setProperty('--gradient-2', g2);
        document.documentElement.style.setProperty('--btn-grad-start', b1);
        document.documentElement.style.setProperty('--btn-grad-end', b2);
        selector.style.display = 'none'; 
      }));
      
      prevBtn.addEventListener('click', goToPrevQuestion);
      
      nextBtn.addEventListener('click', () => {
        const q = questions[currentQuestionIndex];
        const isMarked = markedQuestions.includes(q.id);
        
        if (isQuestionAnswered()) {
          saveAnswer(true);
          goToNextQuestion();
        } else {
          // If question is already marked, skip without modal
          if (isMarked) {
            goToNextQuestion();
          } else {
            showSkipConfirmation();
          }
        }
      });
      
      skipBtn.addEventListener('click', () => {
        skipModal.style.display = 'none';
        goToNextQuestion();
      });
      
      markSkipBtn.addEventListener('click', () => {
        // Mark the question
        const qId = questions[currentQuestionIndex].id;
        if (!markedQuestions.includes(qId)) {
          markedQuestions.push(qId);
          updateMarkButton();
        }
        
        skipModal.style.display = 'none';
        goToNextQuestion();
      });
      
      cancelSkipBtn.addEventListener('click', () => {
        skipModal.style.display = 'none';
      });

      // Initialize
      setTool('pen'); 
      renderQuestion();
      
      // Add fadeOut animation
      const styleEl = document.createElement('style');
      styleEl.textContent = `@keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(20px); } }`;
      document.head.appendChild(styleEl);
    });
  </script>
</body>
</html>
