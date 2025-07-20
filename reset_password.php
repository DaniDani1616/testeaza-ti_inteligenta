<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mysqli = require __DIR__ . '/database.php';

$error  = '';
$flash  = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$token = $_GET['token'] ?? '';
if (! preg_match('/^[0-9a-f]{32}$/', $token)) {
    $error = 'Link invalid sau corupt.';
}

if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password         = $_POST['password']         ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error = 'Parolele nu coincid.';
    } elseif (strlen($password) < 8) {
        $error = 'Parola trebuie să aibă minim 8 caractere.';
    } elseif (
        !preg_match('/[A-Za-z]/', $password) ||
        !preg_match('/\d/',     $password) ||
        !preg_match('/[!@#$%^&*\-_\+=]/', $password)
    ) {
        $error = 'Parola trebuie să conțină literă, cifră și caracter special.';
    } else {
        $stmt = $mysqli->prepare("
            SELECT id, reset_expires
              FROM registration
             WHERE reset_token = ?
             LIMIT 1
        ");
        if (! $stmt) {
            $error = 'Eroare internă de server.';
        } else {
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $stmt->bind_result($user_id, $reset_expires);
            if (! $stmt->fetch()) {
                $error = 'Token invalid.';
            }
            $stmt->close();

            if (! $error && $reset_expires < date('Y-m-d H:i:s')) {
                $error = 'Link-ul a expirat.';
            }
        }
    }

    if (! $error) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("
            UPDATE registration
               SET password_hash = ?, 
                   reset_token   = NULL, 
                   reset_expires = NULL
             WHERE id = ?
        ");
        if (! $stmt) {
            $error = 'Eroare la actualizarea parolei.';
        } else {
            $stmt->bind_param('si', $new_hash, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_success'] = 'Parolă schimbată cu succes! Te poți autentifica acum.';
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Resetare parolă</title>
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --text-color: #fff;
      --card-bg: rgba(255,255,255,0.25);
      --card-blur: 10px;
      --input-border: rgba(255,255,255,0.7);
      --input-focus-border: var(--btn-grad-start);
      --error-color: #ff6b6b;
      --btn-text: #333;
      --card-border: rgba(255, 255, 255, 0.5);
    }
    * { box-sizing:border-box; margin:0; padding:0; }
    html,body{height:100%;}
    body {
      background: linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
      font-family:'Segoe UI','Poppins',sans-serif;
      color:var(--text-color);
      display:flex;align-items:center;justify-content:center;
      padding:1rem;
      line-height:1.6;
    }
    .box {
      background: var(--card-bg);
      backdrop-filter: blur(var(--card-blur));
      border-radius: 20px;
      padding: 2rem;
      width:100%;
      max-width:400px;
      box-shadow:0 15px 40px rgba(0,0,0,0.25);
      border:1px solid rgba(255,255,255,0.3);
    }
    .box h2 {
      text-align:center; margin-bottom:1.5rem; font-size:1.8rem;
    }
    .user-box { position:relative; margin:1.2rem 0; }
    .user-box input {
      width:100%; padding:10px;
      background:transparent; border:none;
      border-bottom:2px solid var(--input-border);
      color:var(--text-color);
      font-size:1rem; outline:none;
      transition:border-color .3s;
    }
    .user-box input:focus { border-bottom-color:var(--input-focus-border); }
    .user-box label {
      position:absolute; top:50%; left:10px;
      transform:translateY(-50%);
      pointer-events:none; transition:.3s;
      color:var(--text-color); opacity:.8;
    }
    .user-box input:focus + label,
    .user-box input:not(:placeholder-shown) + label {
      top:-8px; font-size:.85rem; opacity:1; color:var(--btn-grad-start);
    }
    .error-message {
      color: var(--error-color);
      font-size:.85rem;
      margin-top:.5rem;
      text-align:center;
    }
    .flash {
      position:fixed; top:20px; left:50%;
      transform:translateX(-50%);
      background:#e6ffed; color:#2e7d32;
      padding:12px 24px; border:1px solid #a5d6a7;
      border-radius:4px; font-size:15px;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
      opacity:0; transition:opacity .3s;
      z-index:1000;
    }
    .flash.show { opacity:1; }
    button[type="submit"] {
      width:100%; padding:12px; margin-top:1rem;
      background:linear-gradient(135deg,var(--btn-grad-start),var(--btn-grad-end));
      border:none; border-radius:50px;
      color:var(--btn-text); font-weight:bold; font-size:1rem;
      cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.2);
      transition:all .3s;
    }
    button[type="submit"]:hover {
      transform:translateY(-2px);
      box-shadow:0 6px 15px rgba(0,0,0,0.25);
    }
    .toggle-password {
      position:absolute; right:10px; top:50%;
      transform:translateY(-50%); font-size:1.2rem;
      cursor:pointer; opacity:0.7; transition:opacity 0.3s;
    }
    .toggle-password:hover { opacity:1; }
    
    /* Theme and language toggles */
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
    
    @media (max-width:480px) {
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

<?php if ($flash): ?>
  <div id="flash-message" class="flash"><?php echo htmlspecialchars($flash); ?></div>
<?php endif; ?>

<div class="box">
  <h2 id="reset-title">Resetează parola</h2>

  <?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="user-box">
      <input type="password" id="password" name="password" placeholder=" " required>
      <label id="label-password">Parolă nouă</label>
      <span class="toggle-password" id="toggle-password1" title="Arată/ascunde parola">👁️</span>
    </div>
    <div class="user-box">
      <input type="password" id="password-confirm" name="password_confirm" placeholder=" " required>
      <label id="label-password-confirm">Confirmă parola</label>
      <span class="toggle-password" id="toggle-password2" title="Arată/ascunde parola">👁️</span>
    </div>
    <button type="submit" id="submit-btn">Schimbă parola</button>
  </form>
</div>

<script>
  const flash = document.getElementById('flash-message');
  if (flash) {
    requestAnimationFrame(()=>flash.classList.add('show'));
    setTimeout(()=>flash.classList.remove('show'), 3000);
  }

  document.getElementById('toggle-password1').addEventListener('click', function() {
    togglePasswordVisibility('password');
  });

  document.getElementById('toggle-password2').addEventListener('click', function() {
    togglePasswordVisibility('password-confirm');
  });

  function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
  }
  
  const translations = {
    ro: {
      resetTitle: "Resetează parola",
      labels: {
        password: "Parolă nouă",
        passwordConfirm: "Confirmă parola"
      },
      submitBtn: "Schimbă parola"
    },
    en: {
      resetTitle: "Reset Password",
      labels: {
        password: "New Password",
        passwordConfirm: "Confirm Password"
      },
      submitBtn: "Change Password"
    }
  };
  
  let currentLang = 'ro';
  const langBtns = document.querySelectorAll('.lang-btn');
  
  function updateLang() {
    const t = translations[currentLang];
    document.getElementById('reset-title').textContent = t.resetTitle;
    document.getElementById('label-password').textContent = t.labels.password;
    document.getElementById('label-password-confirm').textContent = t.labels.passwordConfirm;
    document.getElementById('submit-btn').textContent = t.submitBtn;
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
