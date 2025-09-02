<?php
require __DIR__."/../includes/db.php"; 
require __DIR__."/../includes/functions.php"; 
include __DIR__."/../includes/header.php"; 
?>

<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 32px 16px 24px 16px; text-align: center;">
  <h1 style="font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 10px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Loja Gameverse</h1>
  <p style="font-size: 1.1rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Busque, descubra e compre os melhores jogos digitais.</p>
</section>


<?php
  // Buscar categorias para o select
  $categorias = $pdo->query("SELECT idcategoria, nome FROM categoria ORDER BY nome")->fetchAll();
  $cat_selected = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
?>
<form method="get" style="max-width:600px;margin:0 auto 24px auto;display:flex;gap:8px;align-items:center;">
  <input type="text" name="q" placeholder="Buscar jogos..." value="<?= h($_GET['q'] ?? '') ?>" style="flex:2;">
  <select name="cat" style="flex:1;min-width:120px;">
    <option value="0">Todas os jogos</option>
    <option value="-1" <?= isset($_GET['cat']) && $_GET['cat']==='-1' ? 'selected' : '' ?>>Jogos que não possuo</option>
    <?php foreach($categorias as $cat): ?>
      <option value="<?= (int)$cat['idcategoria'] ?>" <?= $cat_selected===(int)$cat['idcategoria']?'selected':'' ?>><?= h($cat['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn">Buscar</button>
</form>

<section>
  <h2 style="font-size: 1.5rem; color: #7cc9ff; margin-bottom: 18px;">Jogos disponíveis</h2>
  <div class="grid" style="gap: 24px;">
  <?php
  $q = trim($_GET['q'] ?? '');
  $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
    $uid = $_SESSION['user']['idusuario'] ?? 0;
    $sql = '';
    $params = [];
    if ($cat == -1 && $uid) {
      $sql = "SELECT g.idgames, g.nome, g.preco, g.imagem, g.descricao FROM games g ";
      $sql .= "WHERE g.ativo = 1 AND g.idgames NOT IN (SELECT idgames FROM compras WHERE idusuario = :uid) ";
      $params[':uid'] = $uid;
      if ($q !== '') {
        $sql .= "AND (g.nome LIKE :q1 OR g.descricao LIKE :q2) ";
        $params[':q1'] = "%$q%";
        $params[':q2'] = "%$q%";
      }
    } else if ($cat > 0) {
      $sql = "SELECT g.idgames, g.nome, g.preco, g.imagem, g.descricao FROM games g WHERE g.ativo = 1 AND g.idcategoria = :cat ";
      $params[':cat'] = $cat;
      if ($q !== '') {
        $sql .= "AND (g.nome LIKE :q1 OR g.descricao LIKE :q2) ";
        $params[':q1'] = "%$q%";
        $params[':q2'] = "%$q%";
      }
    } else {
      $sql = "SELECT g.idgames, g.nome, g.preco, g.imagem, g.descricao FROM games g WHERE g.ativo = 1 ";
      if ($q !== '') {
        $sql .= "AND (g.nome LIKE :q1 OR g.descricao LIKE :q2) ";
        $params[':q1'] = "%$q%";
        $params[':q2'] = "%$q%";
      }
    }
    $sql .= "ORDER BY g.idgames DESC LIMIT 50";
    // Remove parâmetros não utilizados
  if (strpos($sql, ':uid') === false) unset($params[':uid']);
  if (strpos($sql, ':cat') === false) unset($params[':cat']);
  if (strpos($sql, ':q1') === false) unset($params[':q1']);
  if (strpos($sql, ':q2') === false) unset($params[':q2']);
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $games = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo '<pre style="color:red">ERRO SQL: ' . htmlspecialchars($sql) . "\nPARAMS: " . print_r($params, true) . "\n" . $e->getMessage() . '</pre>';
        $games = [];
    }

    if (!$games) {
      echo "<p style='color:#9aa4b2;'>Nenhum jogo encontrado.</p>";
    }

    foreach ($games as $g) {
      $img = h($g['imagem'] ?: '../public/assets/img/placeholder.jpg');
      $nome = h($g['nome']);
      echo "<a href='../public/jogo.php?id=".(int)$g['idgames']."' class='card' style='display:flex;flex-direction:column;justify-content:space-between;cursor:pointer;text-decoration:none;'>
              <img src='{$img}' alt='{$nome}' style='border-bottom:1px solid #1e2430;'>
              <div class='p'>
                <b style='font-size:1.15rem;color:#2b8ef9;'>{$nome}</b>
                <p style='color:#e7e9ee;min-height:48px;'>".h($g['descricao'])."</p>
                <p style='font-size:1.1rem;'><b style='color:#7cc9ff;'>R$ ".number_format($g['preco'], 2, ',', '.')."</b></p>
              </div>
            </a>";
    }
  ?>
  </div>
</section>

<?php include __DIR__."/../includes/footer.php"; ?>
