<?php 
session_start();
// Dacă utilizatorul este deja logat, redirecționăm spre dashboard.php
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
// Preluăm și ștergem flash-ul (astfel apare o singură dată)
$flash = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
// Procesăm formularul doar la POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input    = trim($_POST['identifier'] ?? '');
    $password = $_POST['Parola'] ?? '';

    if ($input === '') {
        $error = 'Introdu username-ul sau email-ul.';
    } elseif ($password === '') {
        $error = 'Introdu parola.';
    } else {
        $mysqli = require __DIR__ . '/database.php';
        if (!$mysqli || $mysqli->connect_errno) {
            die('Conexiune BD eșuată.');
        }

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT id, Numereal, Prenume, Nume, email, password_hash 
                    FROM registration 
                    WHERE email = ? LIMIT 1";
        } else {
            $sql = "SELECT id, Numereal, Prenume, Nume, email, password_hash 
                    FROM registration 
                    WHERE Nume = ? LIMIT 1";
        }
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $row['id'];
                $_SESSION['username']  = $row['Nume'];
                $_SESSION['fullname']  = $row['Numereal'] . ' ' . $row['Prenume'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username/email sau parolă incorectă.';
            }
        } else {
            $error = 'Username/email sau parolă incorectă.';
        }

        $stmt->close();
        $mysqli->close();
    }
}
if (isset($_GET['account_deleted'])) {
  echo '<div id="alert-deleted">Contul tău a fost șters cu succes. Ne pare rău să te vedem plecând!</div>';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Autentificare</title>
  <style>
    :root {
      --gradient-1: #4e54c8;
      --gradient-2: #8f94fb;
      --btn-grad-start: #a1ffce;
      --btn-grad-end: #faffd1;
      --card-bg: rgba(255,255,255,0.3);
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
    #alert-deleted {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #f8d7da;            /* roșu deschis */
  color: #721c24;                 /* text roșu închis */
  padding: 12px 24px;
  border: 1px solid #f5c6cb;      /* bordură roșu-pudră */
  border-radius: 4px;
  font-family: Arial, sans-serif;
  font-size: 15px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
  z-index: 1000;
}
#alert-deleted.show {
  opacity: 1;
}
    /* Stil pentru mesaj flash */
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
    /* Limbă și temă toggle */
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

    /* Container login */
    .login-box {
      margin:auto; background: rgba(255,255,255,0.25);
      backdrop-filter: blur(var(--card-blur));
      border-radius:25px; padding:30px 25px;
      width:100%; max-width:400px;
      box-shadow:0 15px 40px rgba(0,0,0,0.25);
      border:1px solid var(--card-border);
    }
    .login-box h2 {
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
    .toggle-password {
      position:absolute; right:10px; top:50%;
      transform:translateY(-50%); font-size:1.2rem;
      cursor:pointer; opacity:0.7; transition:opacity 0.3s;
    }
    .toggle-password:hover { opacity:1; }

    .error-message {
      color: var(--error-color); font-size:0.85rem;
      margin-top:5px; display:none;
    }

    .login-box button[type="submit"] {
      width:100%; margin-top:15px; padding:12px;
      background: linear-gradient(135deg, var(--btn-grad-start), var(--btn-grad-end));
      border:none; border-radius:50px;
      color:var(--btn-text); font-weight:bold;
      font-size:1rem; cursor:pointer;
      box-shadow:0 4px 10px rgba(0,0,0,0.2);
      transition:all 0.3s;
    }
    .login-box button[type="submit"]:hover {
      transform:translateY(-2px);
      box-shadow:0 6px 15px rgba(0,0,0,0.25);
    }

    .login-box .signup-redirect {
      text-align:center; margin-top:20px; font-size:0.9rem;
    }
    .login-box .signup-redirect a {
      color: var(--btn-grad-start); text-decoration:none;
      font-weight:600; transition:color 0.3s;
    }
    .login-box .signup-redirect a:hover {
      color: var(--btn-grad-end);
    }

    @media (max-width:480px) {
      .login-box { padding:20px 15px; margin:0 10px; }
      .login-box h2 { font-size:1.6rem; }
      .user-box input { padding:8px; font-size:0.9rem; }
      .toggle-password { font-size:1rem; }
      #theme-btn { width:45px; height:45px; bottom:20px; left:20px; }
      #theme-selector { left:20px; bottom:80px; }
      .lang-toggle { bottom:20px; left:80px; }
    }
  </style>
</head>
<body>
  <!-- Temă -->
  <button id="theme-btn" title="Schimbă tema">🎨</button>
  <div id="theme-selector">
    <button data-g1="#4e54c8" data-g2="#8f94fb" data-b1="#a1ffce" data-b2="#faffd1">Albastru</button>
    <button data-g1="#ff6a00" data-g2="#ee0979" data-b1="#ffe29f" data-b2="#ffa99f">Portocaliu</button>
    <button data-g1="#11998e" data-g2="#38ef7d" data-b1="#e0c3fc" data-b2="#8ec5fc">Verde</button>
    <button data-g1="#fc4a1a" data-g2="#f7b733" data-b1="#ff9d00" data-b2="#ffcc70">Auriu</button>
  </div>

  <div class="login-box">
    <h2 id="login-title">Autentificare</h2>
    <?php if ($error): ?>
      <div class="error-message" style="display:block; text-align:center; margin-bottom:10px;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
      
  <?php if ($flash): ?>
    <!-- Mesajul e creat ascuns; JS îl afișează -->
    <div id="flash-message"><?php echo htmlspecialchars($flash); ?></div>
  <?php endif; ?>
    <form id="login-form" action="login.php" method="post" novalidate>
      <div class="user-box">
        <input type="text" id="field-identifier" name="identifier" placeholder=" " required
               value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>"/>
        <label for="field-identifier" id="label-identifier">Username sau Email</label>
        <div class="error-message" id="error-identifier"></div>
      </div>
      <div class="user-box">
        <input type="password" id="field-parola" name="Parola" placeholder=" " required/>
        <label for="field-parola" id="label-parola">Parolă</label>
        <span class="toggle-password" id="toggle-parola" title="Arată/ascunde parola">👁️</span>
        <div class="error-message" id="error-parola"></div>
      </div>
      <button type="submit" id="submit-btn">Login</button>
      <div class="signup-redirect">
        <span id="text-no-account">Nu ai cont?</span>
        <a href="index.html" id="link-signup">Creează unul</a><br>
        <a href="forgot_password.php" id="link-forgotpassword">Am uitat parola</a>
      </div>
    </form>
  </div>

  <script>
    
    (function(){
      // Cât timp să stea mesajul (ms)
      const DURATION = 3000;
      // Luăm elementul, dacă există
      const msg = document.getElementById('flash-message');
      if (!msg) return;

      // Forțăm aplicarea stilurilor inițiale
      requestAnimationFrame(() => {
        msg.classList.add('show');
      });

      // După cei DURATION milisecunde, îl ascundem și apoi îl scoatem
      setTimeout(() => {
        msg.classList.remove('show');
        setTimeout(() => msg.remove(), 300);
      }, DURATION);
    })();
    (function(){
  const DURATION = 3000;
  const msg = document.getElementById('alert-deleted');
  if (!msg) return;
  requestAnimationFrame(() => msg.classList.add('show'));
  setTimeout(() => {
    msg.classList.remove('show');
    setTimeout(() => msg.remove(), 300);
  }, DURATION);
})();
    const translations = {
      ro: {
        loginTitle: "Autentificare",
        labels: {
          identifier: "Username sau Email",
          parola: "Parolă"
        },
        submitBtn: "Login",
        noAccount: "Nu ai cont?",
        signupLink: "Creează unul",
        forgotpasswordLink: "Am uitat parola",
      },
      en: {
        loginTitle: "Login",
        labels: {
          identifier: "Username or Email",
          parola: "Password"
        },
        submitBtn: "Login",
        noAccount: "Don't have an account?",
        signupLink: "Sign up",
        forgotpasswordLink: "Forgot password",
      }
    };
    let currentLang = 'ro';
    const langBtns = document.querySelectorAll('.lang-btn');
    function updateLang(){
      const t = translations[currentLang];
      document.getElementById('login-title').textContent = t.loginTitle;
      document.getElementById('label-identifier').textContent = t.labels.identifier;
      document.getElementById('label-parola').textContent = t.labels.parola;
      document.getElementById('submit-btn').textContent = t.submitBtn;
      document.getElementById('text-no-account').textContent = t.noAccount;
      document.getElementById('link-signup').textContent = t.signupLink;
      document.getElementById('link-forgotpassword').textContent=t.forgotpasswordLink;
    }
    langBtns.forEach(b=>{
      b.addEventListener('click',()=>{
        langBtns.forEach(x=>x.classList.remove('active'));
        b.classList.add('active');
        currentLang = b.dataset.lang;
        updateLang();
      });
    });
    updateLang();

    // Tema toggle
    const themeBtn = document.getElementById('theme-btn');
    const themeSelector = document.getElementById('theme-selector');
    themeBtn.addEventListener('click',()=> {
      themeSelector.style.display = themeSelector.style.display==='block'?'none':'block';
    });
    themeSelector.querySelectorAll('button').forEach(btn=>{
      btn.addEventListener('click',()=>{
        ['g1','g2','b1','b2'].forEach((d,i)=>{
          const prop = ['--gradient-1','--gradient-2','--btn-grad-start','--btn-grad-end'][i];
          document.documentElement.style.setProperty(prop, btn.dataset[['g1','g2','b1','b2'][i]]);
        });
        themeSelector.style.display='none';
      });
    });
    document.addEventListener('click',e=>{
      if (!themeSelector.contains(e.target) && e.target!==themeBtn)
        themeSelector.style.display='none';
    });

    // Emoji toggle parolă
    let pwdVis = false;
    document.getElementById('toggle-parola').addEventListener('click',()=>{
      pwdVis = !pwdVis;
      const f = document.getElementById('field-parola');
      f.type = pwdVis ? 'text':'password';
    });

    // Validare client-side
    document.getElementById('login-form').addEventListener('submit', e=>{
      document.querySelectorAll('.error-message').forEach(el=>el.style.display='none');
      let valid = true;
      const valId = document.getElementById('field-identifier').value.trim();
      if (!valId) {
        const em = document.getElementById('error-identifier');
        em.textContent = currentLang==='ro' ? 'Introduceți username/email.' : 'Please enter username/email.';
        em.style.display='block';
        valid=false;
      }
      const valP = document.getElementById('field-parola').value;
      if (!valP) {
        const em = document.getElementById('error-parola');
        em.textContent = currentLang==='ro' ? 'Introduceți parola.' : 'Please enter password.';
        em.style.display='block';
        valid=false;
      }
      if (!valid) e.preventDefault();
    });
  </script>
</body>
</html>
