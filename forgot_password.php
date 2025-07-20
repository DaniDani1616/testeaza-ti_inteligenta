<?php


session_start();


$mysqli = require __DIR__ . '/database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Te rog introdu un e-mail valid.';
    } else {
      
        $token   = bin2hex(random_bytes(16)); 
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $mysqli->prepare("
            UPDATE registration
            SET reset_token   = ?,
                reset_expires = ?
            WHERE email = ?
        ");
        if (! $stmt) {
            $error = 'Eroare internă la salvarea token-ului.';
        } else {
            $stmt->bind_param('sss', $token, $expires, $email);
            $stmt->execute();
            $stmt->close();

          
            $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host       = $_SERVER['HTTP_HOST'];
            $base       = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
            $resetLink  = "{$protocol}://{$host}{$base}/reset_password.php?token={$token}";

           
            $success = "Link-ul de resetare (valabil până la <strong>{$expires}</strong>) este:<br>"
                     . "<a href=\"{$resetLink}\">{$resetLink}</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ai uitat parola?</title>
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --card-bg: rgba(255,255,255,0.25);
      --card-blur: 10px;
      --text-color: #fff;
      --btn-text: #333;
      --card-border: rgba(255, 255, 255, 0.5);
      --input-border: rgba(255, 255, 255, 0.7);
      --input-focus-border: var(--btn-grad-start);
      --error-color: #ff6b6b;
    }
    * { box-sizing: border-box; margin:0; padding:0; }
    html, body { height:100%; }
    body {
      background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
      font-family: 'Segoe UI', 'Poppins', sans-serif;
      color: var(--text-color);
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      line-height:1.6;
    }
 
    #flash-message {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: #e6ffed;
      color: #2e7d32;
      padding: 12px 24px;
      border: 1px solid #a5d6a7;
      border-radius: 4px;
      font-family: Arial, sans-serif;
      font-size: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      z-index: 1000;
    }
    #flash-message.show {
      opacity: 1;
    }
   
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

  
    .box {
      margin:auto; background: rgba(255,255,255,0.25);
      backdrop-filter: blur(var(--card-blur));
      border-radius:25px; padding:30px 25px;
      width:100%; max-width:400px;
      box-shadow:0 15px 40px rgba(0,0,0,0.25);
      border:1px solid var(--card-border);
    }
    .box h2 {
      text-align:center; font-size:2rem; margin-bottom:20px;
      text-shadow:0 2px 4px rgba(0,0,0,0.2);
    }
    .user-box { position:relative; margin:20px 0; }
    .user-box input {
      width:100%; padding:10px;
      background:transparent; border:none;
      border-bottom:2px solid var(--input-border);
      color:var(--text-color); font-size:1rem;
      outline:none; transition:border-color 0.3s;
    }
    .user-box input:focus { border-bottom-color:var(--input-focus-border); }
    .user-box label {
      position:absolute; top:50%; left:10px;
      transform:translateY(-50%);
      pointer-events:none; transition:0.3s;
      color:var(--text-color); opacity:0.8;
    }
    .user-box input:focus + label,
    .user-box input:not(:placeholder-shown) + label {
      top:-8px; font-size:0.85rem;
      opacity:1; color:var(--btn-grad-start);
    }

    .error-message {
      color: var(--error-color); 
      font-size:0.85rem;
      margin-top:5px; 
      text-align:center;
      display: block;
    }
    
    .success-message {
      color: #e6ffe6;
      font-size:0.85rem;
      margin-top:5px; 
      text-align:center;
      display: block;
    }

    button[type="submit"] {
      width:100%; margin-top:15px; padding:12px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border:none; border-radius:50px;
      color:var(--btn-text); font-weight:bold;
      font-size:1rem; cursor:pointer;
      box-shadow:0 4px 10px rgba(0,0,0,0.2);
      transition:all 0.3s;
    }
    button[type="submit"]:hover {
      transform:translateY(-2px);
      box-shadow:0 6px 15px rgba(0,0,0,0.25);
    }

    .signup-redirect {
      text-align:center; margin-top:20px; font-size:0.9rem;
    }
    .signup-redirect a {
      color: var(--btn-grad-start); text-decoration:none;
      font-weight:600; transition:color 0.3s;
    }
    .signup-redirect a:hover {
      color: var(--btn-grad-end);
    }

    @media (max-width:480px) {
      .box { padding:20px 15px; margin:0 10px; }
      .box h2 { font-size:1.6rem; }
      .user-box input { padding:8px; font-size:0.9rem; }
      #theme-btn { width:45px; height:45px; bottom:20px; left:20px; }
      #theme-selector { left:20px; bottom:80px; }
      .lang-toggle { bottom:20px; left:80px; }
    }
  </style>
</head>
<body>
 
  <button id="theme-btn" title="Schimbă tema">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
  </div>

  <div class="box">
    <h2 id="forgot-title">Ai uitat parola?</h2>

    <?php if ($error): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (! $success): ?>
      <form method="post" action="forgot_password.php" novalidate>
        <div class="user-box">
          <input
            type="email"
            id="email"
            name="email"
            placeholder=" "
            required
          >
          <label id="label-email">Adresa ta de e-mail</label>
        </div>
        <button type="submit" id="submit-btn">Trimite link de reset</button>
      </form>
    <?php endif; ?>

    <div class="signup-redirect">
      <a href="login.php" id="back-link">← Înapoi la autentificare</a>
    </div>
  </div>

  <script>
   
    const translations = {
      ro: {
        forgotTitle: "Ai uitat parola?",
        labels: {
          email: "Adresa ta de e-mail"
        },
        submitBtn: "Trimite link de reset",
        backLink: "← Înapoi la autentificare"
      },
      en: {
        forgotTitle: "Forgot Password?",
        labels: {
          email: "Your email address"
        },
        submitBtn: "Send reset link",
        backLink: "← Back to login"
      }
    };
    
    let currentLang = 'ro';
    const langBtns = document.querySelectorAll('.lang-btn');
    
    function updateLang() {
      const t = translations[currentLang];
      document.getElementById('forgot-title').textContent = t.forgotTitle;
      document.getElementById('label-email').textContent = t.labels.email;
      document.getElementById('submit-btn').textContent = t.submitBtn;
      document.getElementById('back-link').textContent = t.backLink;
    }
    
    langBtns.forEach(b => {
      b.addEventListener('click', () => {
        langBtns.forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        currentLang = b.dataset.lang;
        updateLang();
      });
    });
    
    updateLang();

   
    const themeBtn = document.getElementById('theme-btn');
    const themeSelector = document.getElementById('theme-selector');
    
    themeBtn.addEventListener('click', () => {
      themeSelector.style.display = themeSelector.style.display === 'block' ? 'none' : 'block';
    });
    
    themeSelector.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        ['g1','g2','b1','b2'].forEach((d, i) => {
          const prop = ['--gradient-1','--gradient-2','--btn-grad-start','--btn-grad-end'][i];
          document.documentElement.style.setProperty(prop, btn.dataset[['g1','g2','b1','b2'][i]]);
        });
        themeSelector.style.display = 'none';
      });
    });
    
    document.addEventListener('click', e => {
      if (!themeSelector.contains(e.target) && e.target !== themeBtn) {
        themeSelector.style.display = 'none';
      }
    });
  </script>
</body>
</html>
