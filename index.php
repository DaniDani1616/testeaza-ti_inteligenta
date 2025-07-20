<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['username'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
} else {
    header('Location: login.php');
    exit;
}
$mysqli = require __DIR__ . '/database.php';
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

$fullname = trim("{$user['Prenume']} {$user['Numereal']}");
$questions = [
    1 => ['text' => 'Mă simt foarte apropiat de ceilalți oameni;', 'category' => 'O'],
    2 => ['text' => 'În adâncul sufletului sunt convins/ă că nu mi-se poate întâmpla nimic rău;', 'category' => 'N'],
    3 => ['text' => 'Majoritatea oamenilor nu merită nici o atenție;', 'category' => 'M'],
    4 => ['text' => 'Am încredere în toți oamenii;', 'category' => 'N'],
    5 => ['text' => 'În cele din urmă trebuie să te bizui doar pe tine însuți;', 'category' => 'M'],
    6 => ['text' => 'Trebuie doar să vrei ceva cu adevărat pentru ca să se poată realiza;', 'category' => 'N'],
    7 => ['text' => 'Găsesc imediat plăcere în lucrul pe care îl fac;', 'category' => 'O'],
    8 => ['text' => 'Nu am dușmani;', 'category' => 'N'],
    9 => ['text' => 'Încrederea e bună, controlul și mai bun;', 'category' => 'M'],
    10 => ['text' => 'În relațiile cu ceilalți sunt sincer/ă;', 'category' => 'O'],
    11 => ['text' => 'Nu permit nimănui să fie prea apropiat față de mine;', 'category' => 'M'],
    12 => ['text' => 'Nu mă gândesc la ziua de mâine;', 'category' => 'N'],
    13 => ['text' => 'Trebuie să se cunoască mai întâi bine pe cineva, pentru a-i acorda încredere;', 'category' => 'M'],
    14 => ['text' => 'Îmi dau seama imediat când cineva este rău intenționat;', 'category' => 'O'],
    15 => ['text' => 'Chiar și atunci când oamenii fac ceva rău, nu înseamnă că sunt răi;', 'category' => 'N'],
    16 => ['text' => 'Oricine poate fi cumpărat;', 'category' => 'M'],
    17 => ['text' => 'După ploaie apare întotdeauna soarele;', 'category' => 'O'],
    18 => ['text' => 'Oamenii s-ar înțelege bine unii cu alții, chiar și fără legi;', 'category' => 'N'],
    19 => ['text' => 'Sunt convins/ă că și prietenii mă vorbesc de rău;', 'category' => 'M'],
    20 => ['text' => 'Față de străini sunt nepărtinitor/nepărtinitoare;', 'category' => 'O'],
    21 => ['text' => 'Pot suporta critica.', 'category' => 'O'],
];

$results = ['M' => 0, 'N' => 0, 'O' => 0];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $num => $q) {
        $fieldName = 'q' . $num;
        if (isset($_POST[$fieldName])) {
            $val = intval($_POST[$fieldName]);
            if ($val >= 3 && $val <= 4) {
                $cat = $q['category'];
                $results[$cat]++;
            }
        }
    }

    $M = $results['M'];
    $N = $results['N'];
    $O = $results['O'];

    if ($M === 7 && $N < 7 && $O < 7) {
        $message = 'Faci parte din tipul mizantropului';
    } elseif ($N === 7 && $M < 7 && $O < 7) {
        $message = 'Faci parte din tipul Naivului';
    } elseif ($O === 7 && $M < 7 && $N < 7) {
        $message = 'Faci parte din tipul Optimistului';
    } elseif ($O === $M && $O > 0 && $M > 0 && $N < $O && $N < $M) {
        $message = 'Faci parte din tipul Optimistului și din tipul mizantropului';
    } elseif ($O === $N && $O > 0 && $N > 0 && $M < $O && $M < $N) {
        $message = 'Faci parte din tipul Optimistului și din tipul Naivului';
    } elseif ($M === $N && $M > 0 && $N > 0 && $O < $N && $O < $M) {
        $message = 'Faci parte din tipul mizantropului și din tipul Naivului';
    } else {
        $message = 'Rezultatul nu s-a încadrat într-o categorie unică conform criteriilor.';
    }

    $maxScore = max($results['M'], $results['N'], $results['O']);
    $dominantTypes = [];
    if ($results['M'] === $maxScore) $dominantTypes[] = 'M';
    if ($results['N'] === $maxScore) $dominantTypes[] = 'N';
    if ($results['O'] === $maxScore) $dominantTypes[] = 'O';

    $raspunsuri = [];
    for ($i = 1; $i <= 21; $i++) {
        $field = 'q' . $i;
        $raspunsuri[$i] = isset($_POST[$field]) ? intval($_POST[$field]) : 0;
    }

    $dbHost = 'sql206.infinityfree.com	';
    $dbName = 'if0_39518451_chestionar_db';
    $dbUser = 'if0_39518451';
    $dbPass = 'Dani2008Fotbal';

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
        error_log("Eroare conectare MySQL: " . $mysqli->connect_error);
    } else {
        $columns = [];
        $placeholders = [];
        $types = '';
        $values = [];

        for ($i = 1; $i <= 21; $i++) {
            $columns[] = 'q' . $i;
            $placeholders[] = '?';
            $types .= 'i';
            $values[] = $raspunsuri[$i];
        }
        
        $columns[] = 'user_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = $_SESSION['user_id'];
        
        $columns[] = 'tip';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = $message;

        $cols_str = implode(',', $columns);
        $ph_str = implode(',', $placeholders);
        $sql = "INSERT INTO `formular_raspunsuri` ($cols_str) VALUES ($ph_str)";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            $bind_names = [];
            $bind_names[] = $types;
            foreach ($values as $idx => $val) {
                $bind_names[] = &$values[$idx];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
            $stmt->execute();
            $stmt->close();
        }
        $mysqli->close();
    }

    $_SESSION['test_results'] = [
        'message' => $message,
        'results' => $results,
        'dominantTypes' => $dominantTypes
    ];

    header('Location: results.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chestionar "Gândire în mod pozitiv"</title>
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
      --form-bg: rgba(255, 255, 255, 0.1);
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
      box-shadow:  Ț0 5px 15px rgba(0,0,0,0.1);
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
      width: 100%;
      height: 100%;
      background: var(--primary-accent);
    }
    
    /* Form styling */
    .form-container {
      background: var(--form-bg);
      border-radius: 16px;
      padding: 25px;
      backdrop-filter: blur(5px);
      margin-top: 20px;
    }
    
    .question-item {
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .question-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .question-text {
      font-size: 18px;
      margin-bottom: 15px;
      font-weight: 500;
    }
    
    .options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }
    
    .option {
      background: rgba(255,255,255,0.2);
      padding: 15px;
      border-radius: 15px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    
    .option:hover {
      background: rgba(255,255,255,0.3);
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .option input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .option.selected {
      background: rgba(255, 157, 0, 0.4);
      border: 2px solid var(--primary-accent);
      transform: scale(1.05);
    }
    
    .option-label {
      margin-top: 10px;
      font-weight: 600;
      font-size: 16px;
    }
    
    /* Butoane de control */
    .controls {
      display: flex;
      justify-content: center;
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
      min-width: 200px;
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn:active {
      transform: translateY(1px);
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
      
      .options {
        grid-template-columns: 1fr;
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
    }
  </style>
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
      <div class="question-container">
        <h2><i class="fas fa-question-circle"></i> Chestionar: "Gândire în mod pozitiv?"</h2>
        <div class="progress-container">
          <div class="progress-bar"></div>
        </div>
        
        <p>Vă rugăm să răspundeți la următoarele întrebări selectând opțiunea care vă descrie cel mai bine.</p>
        
        <form method="post" action="" class="form-container">
          <?php foreach ($questions as $num => $q): ?>
            <div class="question-item">
              <div class="question-text">
                <strong>Întrebarea <?= $num ?>:</strong> <?= htmlspecialchars($q['text']) ?>
              </div>
              <div class="options">
                <label class="option">
                  <input type="radio" name="q<?= $num ?>" value="1" required>
                  <div class="option-label">Nu corespunde</div>
                </label>
                <label class="option">
                  <input type="radio" name="q<?= $num ?>" value="2">
                  <div class="option-label">Corespunde uneori</div>
                </label>
                <label class="option">
                  <input type="radio" name="q<?= $num ?>" value="3">
                  <div class="option-label">Corespunde deseori</div>
                </label>
                <label class="option">
                  <input type="radio" name="q<?= $num ?>" value="4">
                  <div class="option-label">Corespunde pe deplin</div>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
          
          <div class="controls">
            <button type="submit" class="btn">
              <i class="fas fa-paper-plane"></i> Trimite răspunsurile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
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
    document.addEventListener('DOMContentLoaded', function() {
      const options = document.querySelectorAll('.option');
      options.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        radio.addEventListener('change', function() {
          const parentOptions = option.closest('.options');
          parentOptions.querySelectorAll('.option').forEach(opt => {
            opt.classList.remove('selected');
          });
          
          if (radio.checked) {
            option.classList.add('selected');
          }
        });
      });
      
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
      
      const langBtn = document.getElementById('lang-btn');
      let currentLang = 'ro';
      
      langBtn.addEventListener('click', function() {
        currentLang = currentLang === 'ro' ? 'en' : 'ro';
        const langText = currentLang === 'ro' ? 'Limba schimbată în Română' : 'Language changed to English';
        showNotification(langText);
      });
      
      function showNotification(message) {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.bottom = '100px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.background = 'linear-gradient(135deg, #00b09b, #96c93d)';
        notification.style.color = 'white';
        notification.style.borderRadius = '50px';
        notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.3)';
        notification.style.zIndex = '1000';
        notification.style.animation = 'fadeIn 0.3s, slideUp 0.5s';
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
          notification.style.animation = 'fadeOut 0.5s';
          setTimeout(() => {
            document.body.removeChild(notification);
          }, 500);
        }, 3000);
      }
      
      const styleEl = document.createElement('style');
      styleEl.textContent = `
        @keyframes fadeOut {
          from { opacity: 1; transform: translateY(0); }
          to { opacity: 0; transform: translateY(20px); }
        }
      `;
      document.head.appendChild(styleEl);
      
      document.body.style.opacity = 0;
      setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease-in';
        document.body.style.opacity = 1;
      }, 100);
    });
  </script>
</body>
</html>
