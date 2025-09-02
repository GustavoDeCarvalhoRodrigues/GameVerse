<?php
require __DIR__."/../includes/db.php";
require __DIR__."/../includes/functions.php";
require_admin();
include __DIR__."/../includes/header.php";
?>
<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Administração</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Gerencie jogos, categorias, cupons, usuários e notificações do Gameverse.</p>
</section>

<div class="grid" style="gap:24px;">
  <a class="btn" style="font-size:1.1em;padding:22px 0;text-align:center;" href="../admin/jogos.php">🎮<br>Gerenciar Jogos</a>
  <a class="btn" style="font-size:1.1em;padding:22px 0;text-align:center;" href="../admin/categorias.php">🏷️<br>Categorias</a>
  <a class="btn" style="font-size:1.1em;padding:22px 0;text-align:center;" href="../admin/cupons.php">💸<br>Cupons</a>
  <a class="btn" style="font-size:1.1em;padding:22px 0;text-align:center;" href="../admin/usuarios.php">👤<br>Usuários</a>
  <a class="btn" style="font-size:1.1em;padding:22px 0;text-align:center;" href="../admin/notificacoes.php">🔔<br>Notificações</a>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
