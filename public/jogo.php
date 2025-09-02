<?php 
require __DIR__."/../includes/db.php"; 
require __DIR__."/../includes/functions.php"; 
include __DIR__."/../includes/header.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM games WHERE idgames=? AND ativo=1");
$stmt->execute([$id]);
$g = $stmt->fetch();

if (!$g) { 
    echo "<p>Jogo não encontrado.</p>"; 
    include __DIR__."/../includes/footer.php"; 
    exit; 
}

// Verifica se usuário já tem o jogo
$jaTem = false;
if (isset($_SESSION['user'])) {
    $st = $pdo->prepare("SELECT 1 FROM compras WHERE idusuario=? AND idgames=?");
    $st->execute([$_SESSION['user']['idusuario'], $g['idgames']]);
    $jaTem = (bool)$st->fetch();
}
?>

<div class="admin-grid">
  <div>
    <img src="<?= h($g['imagem'] ?: '../public/assets/img/placeholder.jpg') ?>" 
         alt="<?= h($g['nome']) ?>" 
         style="width:100%;border-radius:12px;max-height:360px;object-fit:cover">
  </div>
  <div>
    <h2><?= h($g['nome']) ?></h2>
    <p><?= h($g['descricao']) ?></p>
    <p><b>Preço:</b> R$ <?= number_format($g['preco'],2,',','.') ?></p>

  <?php if ($jaTem): ?>
    <p><b>✔ Você já possui este jogo na sua conta.</b></p>
  <?php else: ?>
    <?php if (!is_logged()): ?>
      <a href="login.php?next=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn">Faça login para comprar</a>
    <?php else: ?>
      <form method="post" action="carrinho.php">
        <input type="hidden" name="acao" value="add">
        <input type="hidden" name="idgames" value="<?= (int)$g['idgames'] ?>">
        <!-- Quantidade removida -->
        </label>
        <button class="btn" type="submit">Adicionar ao carrinho</button>
      </form>
    <?php endif; ?>
  <?php endif; ?>
  </div>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
