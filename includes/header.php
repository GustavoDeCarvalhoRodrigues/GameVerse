<?php if(session_status()===PHP_SESSION_NONE){ session_start(); } ?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gameverse</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
<header style="background: linear-gradient(90deg, #111419 60%, #1a2230 100%); border-bottom: 1px solid #1e222b; box-shadow: 0 2px 12px 0 rgba(0,0,0,0.12);">
  <div class="wrap" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 10px 18px; position:relative;">
    <div style="display:flex;align-items:center;gap:10px;">
      <a href="../index.php" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
        <span style="display:inline-block;width:36px;height:36px;background:linear-gradient(135deg,#2b8ef9 60%,#7cc9ff 100%);border-radius:50%;box-shadow:0 2px 8px #2b8ef980;display:flex;align-items:center;justify-content:center;font-size:1.5em;color:#fff;font-weight:900;">🎮</span>
        <span style="font-size:1.5em;font-weight:900;color:#7cc9ff;letter-spacing:0.04em;text-shadow:0 2px 8px #2b8ef980;">Gameverse</span>
      </a>
    </div>
    <nav id="main-nav" style="display:flex;gap:10px;align-items:center;">
      <a href="../public/loja.php" style="padding:8px 14px;border-radius:8px;color:#7cc9ff;font-weight:600;transition:background 0.2s;">Loja</a>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="../public/carrinho.php" style="padding:8px 14px;border-radius:8px;color:#7cc9ff;font-weight:600;transition:background 0.2s;">Carrinho</a>
      <?php endif; ?>
      <a href="../public/notificacoes.php" style="padding:8px 14px;border-radius:8px;color:#7cc9ff;font-weight:600;transition:background 0.2s;">Notificações</a>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="../public/perfil.php" style="padding:8px 14px;border-radius:8px;color:#7cc9ff;font-weight:600;transition:background 0.2s;">Olá, <?= h($_SESSION['user']['nome']); ?></a>
        <?php if($_SESSION['user']['role']==='admin'): ?>
          <a href="../admin/index.php" style="padding:8px 14px;border-radius:8px;color:#fff;background:#2b8ef9;font-weight:600;">Admin</a>
        <?php endif; ?>
        <a href="../public/logout.php" style="padding:8px 14px;border-radius:8px;color:#fff;background:#353b48;font-weight:600;">Sair</a>
      <?php else: ?>
        <button id="btn-login-modal" class="btn" style="min-width:120px;">Entrar / Criar conta</button>
      <?php endif; ?>
    </nav>
    <button id="menu-toggle" style="display:none;flex-direction:column;gap:4px;background:none;border:none;cursor:pointer;padding:8px 10px;z-index:1001;">
      <span style="width:28px;height:4px;background:#7cc9ff;border-radius:2px;"></span>
      <span style="width:28px;height:4px;background:#7cc9ff;border-radius:2px;"></span>
      <span style="width:28px;height:4px;background:#7cc9ff;border-radius:2px;"></span>
    </button>
    <div id="side-menu-bg" style="display:none;position:fixed;z-index:1000;top:0;left:0;width:100vw;height:100vh;background:rgba(10,18,30,0.85);"></div>
    <nav id="side-menu" style="display:none;flex-direction:column;gap:18px;position:fixed;top:0;right:0;height:100vh;width:220px;background:#181c25;z-index:1001;padding:38px 18px 18px 18px;box-shadow:-2px 0 16px #000a;">
      <a href="../public/loja.php">Loja</a>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="../public/carrinho.php">Carrinho</a>
      <?php endif; ?>
      <a href="../public/notificacoes.php">Notificações</a>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="../public/perfil.php">Olá, <?= h($_SESSION['user']['nome']); ?></a>
        <?php if($_SESSION['user']['role']==='admin'): ?>
          <a href="../admin/index.php">Admin</a>
        <?php endif; ?>
        <a href="../public/logout.php">Sair</a>
      <?php else: ?>
        <button id="btn-login-modal-side" class="btn" style="min-width:120px;">Entrar / Criar conta</button>
      <?php endif; ?>
    </nav>
  </div>
</header>
<style>
  @media (max-width: 900px) {
    #main-nav { display: none !important; }
    #menu-toggle { display: flex !important; }
  }
  @media (min-width: 901px) {
    #side-menu, #side-menu-bg, #menu-toggle { display: none !important; }
    #main-nav { display: flex !important; }
  }
</style>
<script>
  const menuToggle = document.getElementById('menu-toggle');
  const sideMenu = document.getElementById('side-menu');
  const sideMenuBg = document.getElementById('side-menu-bg');
  if(menuToggle && sideMenu && sideMenuBg){
    menuToggle.onclick = ()=>{
      sideMenu.style.display = 'flex';
      sideMenuBg.style.display = 'block';
    };
    sideMenuBg.onclick = ()=>{
      sideMenu.style.display = 'none';
      sideMenuBg.style.display = 'none';
    };
  }
  // Login pelo menu lateral
  const btnLoginSide = document.getElementById('btn-login-modal-side');
  if(btnLoginSide && typeof btnLoginModal !== 'undefined' && modalBg){
    btnLoginSide.onclick = ()=>{ sideMenu.style.display='none'; sideMenuBg.style.display='none'; modalBg.style.display='flex'; loginArea.style.display='block'; registerArea.style.display='none'; };
  }
</script>

<!-- Modal Login/Cadastro -->
<div id="login-modal-bg" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(10,18,30,0.85);align-items:center;justify-content:center;">
  <div id="login-modal" style="background:#181c25;padding:32px 28px 22px 28px;border-radius:18px;box-shadow:0 8px 32px #000a;min-width:320px;max-width:95vw;width:360px;position:relative;">
    <button id="close-login-modal" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:1.5em;color:#7cc9ff;cursor:pointer;">&times;</button>
    <div id="login-form-area">
      <h2 style="color:#7cc9ff;text-align:center;margin-bottom:18px;">Entrar</h2>
      <form id="form-login" method="post" action="../public/login.php" autocomplete="on">
        <input name="email" type="email" placeholder="E-mail" required style="width:100%;margin-bottom:12px;">
        <input name="senha" type="password" placeholder="Senha" required style="width:100%;margin-bottom:18px;">
        <button class="btn" style="width:100%;">Entrar</button>
      </form>
      <div style="text-align:center;margin-top:14px;">
        <span style="color:#9aa4b2;">Não tem uma conta?</span>
        <button id="show-register" style="background:none;border:none;color:#2b8ef9;font-weight:600;cursor:pointer;">Criar conta</button>
      </div>
    </div>
    <div id="register-form-area" style="display:none;">
      <h2 style="color:#7cc9ff;text-align:center;margin-bottom:18px;">Criar conta</h2>
      <form id="form-register" method="post" action="../public/registro.php" autocomplete="on">
        <input name="nome" type="text" placeholder="Nome completo" required style="width:100%;margin-bottom:12px;">
        <input name="email" type="email" placeholder="E-mail" required style="width:100%;margin-bottom:12px;">
        <input name="senha" type="password" placeholder="Senha" required style="width:100%;margin-bottom:18px;">
        <button class="btn" style="width:100%;">Cadastrar</button>
      </form>
      <div style="text-align:center;margin-top:14px;">
        <span style="color:#9aa4b2;">Já tem uma conta?</span>
        <button id="show-login" style="background:none;border:none;color:#2b8ef9;font-weight:600;cursor:pointer;">Entrar</button>
      </div>
    </div>
  </div>
</div>
<script>
// Modal login/cadastro
const btnLoginModal = document.getElementById('btn-login-modal');
const modalBg = document.getElementById('login-modal-bg');
const closeModal = document.getElementById('close-login-modal');
const loginArea = document.getElementById('login-form-area');
const registerArea = document.getElementById('register-form-area');
const showRegister = document.getElementById('show-register');
const showLogin = document.getElementById('show-login');

if(btnLoginModal){
  btnLoginModal.onclick = ()=>{ modalBg.style.display='flex'; loginArea.style.display='block'; registerArea.style.display='none'; };
}
if(closeModal){
  closeModal.onclick = ()=>{ modalBg.style.display='none'; };
}
if(showRegister){
  showRegister.onclick = ()=>{ loginArea.style.display='none'; registerArea.style.display='block'; };
}
if(showLogin){
  showLogin.onclick = ()=>{ registerArea.style.display='none'; loginArea.style.display='block'; };
}
// Fecha modal ao clicar fora
modalBg.addEventListener('click', function(e){
  if(e.target===modalBg) modalBg.style.display='none';
});
</script>
<div class="container">
