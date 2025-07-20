<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize results
$results = ['M' => 0, 'N' => 0, 'O' => 0];
$msg = 'Nu faci parte dintr-o categorie, vizualizeaza informatii despre toate cele 3 categorii din care puteai face parte.';

// Process test results if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        if (preg_match('/^q(\d+)$/', $k, $m)) {
            $question_id = $m[1];
            $score = (int)$v;
            $cat = $_POST['cat' . $question_id];

            // Insert answer into database
            $stmt = $pdo->prepare("INSERT INTO answers (question_id, value) VALUES ( ?, ?)");
            $stmt->execute([$m[1],$score]);

            // Count scores for categories
            if ($score >= 3) {
                $results[$cat]++;
            }
        }
    }

    // Determine result message
    if ($results['M'] > $results['N'] && $results['M'] > $results['O']) {
        $msg = 'Mizantrop';
    } elseif ($results['N'] > $results['M'] && $results['N'] > $results['O']) {
        $msg = 'Naiv';
    } elseif ($results['O'] > $results['M'] && $results['O'] > $results['N']) {
        $msg = 'Optimist';
    }

    // Store results in session
    $_SESSION['test_results'] = [
      'message'       => $msg,
      'results'       => $results,
      'dominantTypes' => getDominantTypes($results)  // apel direct
  ];
}

// Function to determine dominant types
function getDominantTypes($results) {
    $max = max($results);
    $dominant = [];
    foreach ($results as $type => $score) {
        if ($score == $max) {
            $dominant[] = $type;
        }
    }
    return $dominant;
}
$mysqli = require __DIR__ . '/database.php';
// Get user data for display
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

// Use test results from session if available
if (isset($_SESSION['test_results'])) {
    $testResults = $_SESSION['test_results'];
    $message = $testResults['message'];
    $results = $testResults['results'];
    $dominantTypes = $testResults['dominantTypes'];
} else {
    // Default values if no test taken
    $message = 'Completează testul mai întâi';
    $results = ['M' => 0, 'N' => 0, 'O' => 0];
    $dominantTypes = [];
}

// Type information
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
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezultate Test - Gandire Pozitivă</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root {
            --gradient-1: #4e54c8;
            --gradient-2: #8f94fb;
            --btn-grad-start: #a1ffce;
            --btn-grad-end: #faffd1;
            --primary-accent: #ff9d00;
            --text-color: #fff;
            --btn-text: #333;
            --card-bg: rgba(255,255,255,0.2);
            --card-border: rgba(255,255,255,0.3);
        }
        body { background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2)); color: var(--text-color); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); padding: 15px 30px; border-radius: 16px; margin-bottom: 20px; backdrop-filter: blur(10px); border: 1px solid var(--card-border); }
        #user-info {
            display: flex; 
            align-items: center; 
            gap: 10px;
            background: rgba(255,255,255,0.7); 
            color: #333;
            padding: 10px 20px; 
            border-radius: 50px; 
            font-weight: 600;
            text-decoration: none;
        }
        #user-info img {
            width: 48px; 
            height: 48px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 2px solid white;
        }
        .nav-icons a { 
            background: rgba(255,255,255,0.3); 
            width: 44px; 
            height: 44px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 10px; 
            color: var(--text-color); 
            text-decoration: none; 
        }
        .content { display: flex; justify-content: center; }
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
        .question-container { 
            background: var(--card-bg); 
            backdrop-filter: blur(12px); 
            border-radius: 20px; 
            padding: 20px; 
            border: 1px solid var(--card-border); 
            max-width: 700px; 
            width: 100%; 
        }
        .results-message { text-align: center; font-size: 18px; margin-bottom: 30px; }
        .scores { display: flex; justify-content: center; gap: 40px; margin-bottom: 30px; }
        .score-item { display: flex; flex-direction: column; align-items: center; }
        .score-circle { 
            width: 80px; 
            height: 80px; 
            border-radius: 50%; 
            background: var(--btn-grad-start); 
            color: var(--btn-text); 
            font-size: 24px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 10px; 
        }
        .score-label { font-size: 16px; font-weight: 600; }
        .type-container { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
        .type-card { 
            background: var(--card-bg); 
            border: 1px solid var(--card-border); 
            border-radius: 18px; 
            padding: 20px; 
            flex: 1; 
            min-width: 250px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
        }
        .type-icon { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            background: var(--btn-grad-start); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 28px; 
            color: var(--btn-text); 
            margin-bottom: 15px; 
        }
        .type-title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .type-description { font-size: 14px; margin-bottom: 15px; line-height: 1.4; }
        .type-quote { font-style: italic; font-size: 13px; margin-bottom: 15px; line-height: 1.3; }
        .traits-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .trait { padding: 6px 12px; border-radius: 12px; font-size: 12px; }
        .strength { background: rgba(34,197,94,0.3); color: #22c55e; }
        .weakness { background: rgba(239,68,68,0.3); color: #ef4444; }
        .controls { display: flex; justify-content: center; gap: 15px; margin-top: 30px; }
        .btn { 
            padding: 15px 30px; 
            background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end)); 
            border: none; 
            border-radius: 50px; 
            color: var(--btn-text); 
            font-weight: bold; 
            cursor: pointer; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        @media (max-width:768px) { 
            .scores { flex-direction: column; gap: 20px; } 
            .type-container { flex-direction: column; } 
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
                <h2><i class="fas fa-chart-bar"></i> Rezultatele Testului</h2>
                <div class="results-message"><?= htmlspecialchars($message) ?></div>
                <div class="scores">
                    <div class="score-item">
                        <div class="score-circle">M</div>
                        <div class="score-label"><?= $results['M'] ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-circle">N</div>
                        <div class="score-label"><?= $results['N'] ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-circle">O</div>
                        <div class="score-label"><?= $results['O'] ?></div>
                    </div>
                </div>
                <div class="type-container">
                    <?php foreach ($dominantTypes as $type): ?>
                        <div class="type-card">
                            <div class="type-icon"><?= $type ?></div>
                            <div class="type-title"><?= $typeInfo[$type]['title'] ?></div>
                            <div class="type-description"><?= nl2br(htmlspecialchars($typeInfo[$type]['description'])) ?></div>
                            <div class="type-quote">"<?= htmlspecialchars($typeInfo[$type]['quote']) ?>"</div>
                            <div class="traits-container">
                                <?php foreach ($typeInfo[$type]['strengths'] as $s): ?>
                                    <div class="trait strength"><?= htmlspecialchars($s) ?></div>
                                <?php endforeach; ?>
                            </div>
                            <div class="traits-container">
                                <?php foreach ($typeInfo[$type]['weaknesses'] as $w): ?>
                                    <div class="trait weakness"><?= htmlspecialchars($w) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="controls">
                    <a href="gandire_pozitiva.php" class="btn"><i class="fas fa-redo"></i> Refă testul</a>
                    <a href="home.php" class="btn"><i class="fas fa-home"></i> Pagina principală</a>
                </div>
            </div>
        </div>
    </div>
    <button id="theme-btn">🎨</button>
    <div id="theme-selector">
        <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
        <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
        <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
        <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
    </div>
    <script>
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
    </script>
</body>
</html>