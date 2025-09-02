<?php
require __DIR__."/../includes/db.php";
require __DIR__."/../includes/functions.php";
include __DIR__."/../includes/header.php";
?>


<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Notificações</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Fique por dentro das novidades e promoções do Gameverse.</p>
</section>

<?php
$st = $pdo->query("SELECT idnotificacoes, nome, descricao, dataenvio 
                   FROM notificacoes 
                   ORDER BY dataenvio DESC");
$notifs = $st->fetchAll();

echo '<div class="grid" style="gap:24px;">';
if (!$notifs) {
    echo "<p style='color:#9aa4b2;'>Nenhuma notificação encontrada.</p>";
}

foreach ($notifs as $n) {
    $data = date("d/m/Y H:i", strtotime($n['dataenvio']));
    echo "<div class='card' style='min-height:120px;display:flex;align-items:center;'>
            <div class='p'>
              <b style='font-size:1.1rem;color:#2b8ef9;'>".h($n['nome'])."</b><br>
              <span style='color:#e7e9ee;'>".h($n['descricao'])."</span><br>
              <small style='color:#7cc9ff;'>$data</small>
            </div>
          </div>";
}
echo '</div>';
?>

<?php include __DIR__."/../includes/footer.php"; ?>
