<?php
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/functions.php";
require_admin();
include __DIR__ . "/../includes/header.php";

// Criar / Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int) ($_POST['idgames'] ?? 0);
  $nome = trim($_POST['nome'] ?? '');
  $descricao = trim($_POST['descricao'] ?? '');
  $preco = (float) ($_POST['preco'] ?? 0);
  $idcategoria = (int) ($_POST['idcategoria'] ?? 0);
  $ativo = isset($_POST['ativo']) ? 1 : 0;

  // Upload de imagem
  $imgPath = $_POST['imagem_atual'] ?? '';
  if (!empty($_FILES['imagem']['name'])) {
    $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
      $fname = 'uploads/' . uniqid('game_') . '.' . $ext;
      $dest = __DIR__ . '/../public/' . $fname;
      if (move_uploaded_file($_FILES['imagem']['tmp_name'], $dest)) {
        $imgPath = $fname; // salva caminho relativo acessível via navegador
      }
    }
  }

  if ($id > 0) {
    $st = $pdo->prepare("UPDATE games SET nome=?, descricao=?, preco=?, idcategoria=?, imagem=?, ativo=? WHERE idgames=?");
    $st->execute([$nome, $descricao, $preco, $idcategoria, $imgPath, $ativo, $id]);
    flash('ok', 'Jogo atualizado.');
  } else {
    // Criar jogo
    $st = $pdo->prepare("INSERT INTO games (nome,descricao,preco,idcategoria,imagem,ativo) VALUES (?,?,?,?,?,?)");
    $st->execute([$nome, $descricao, $preco, $idcategoria, $imgPath, 1]);
    flash('ok', 'Jogo criado.');

    // Criar notificação
    $tituloNotif = "Novo jogo adicionado.";
    $descNotif = $nome . ". " . $descricao;
    $insNotif = $pdo->prepare("INSERT INTO notificacoes (nome, descricao) VALUES (?, ?)");
    $insNotif->execute([$tituloNotif, $descNotif]);
  }

  header("Location: ../admin/jogos.php");
  exit;
}

// Remover
if (isset($_GET['del'])) {
  $id = (int) $_GET['del'];
  $pdo->prepare("DELETE FROM games WHERE idgames=?")->execute([$id]);
  flash('ok', 'Jogo removido.');
  header("Location: ../admin/jogos.php");
  exit;
}

// Listar categorias e jogos
$cats = $pdo->query("SELECT idcategoria,nome FROM categoria ORDER BY nome")->fetchAll();
$games = $pdo->query("SELECT * FROM games ORDER BY idgames DESC")->fetchAll();
$edit = ['idgames' => 0, 'nome' => '', 'descricao' => '', 'preco' => 0, 'idcategoria' => 0, 'imagem' => '', 'ativo' => 1];

if (isset($_GET['edit'])) {
  $st = $pdo->prepare("SELECT * FROM games WHERE idgames=?");
  $st->execute([(int) $_GET['edit']]);
  $edit = $st->fetch() ?: $edit;
}
?>
<section class="hero"
  style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1
    style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">
    Administração de Jogos</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Gerencie os jogos
    cadastrados, edite, crie ou remova títulos do catálogo.</p>
</section>

<?php if ($m = flash('ok'))
  echo "<p style='color:#7cc9ff;font-weight:600;text-align:center'>{$m}</p>"; ?>
<div class="admin-grid">
  <div>
    <form method="post" enctype="multipart/form-data"
      style="background:#181c25;padding:18px 16px;border-radius:14px;box-shadow:0 2px 12px 0 rgba(20,30,50,0.10);">
      <input type="hidden" name="idgames" value="<?= (int) $edit['idgames'] ?>">
      <label style="font-weight:600;color:#7cc9ff;">Nome</label><input name="nome" value="<?= h($edit['nome']) ?>"
        required>
      <label style="font-weight:600;color:#7cc9ff;">Descrição</label><textarea name="descricao"
        rows="3"><?= h($edit['descricao']) ?></textarea>
      <label style="font-weight:600;color:#7cc9ff;">Preço</label><input name="preco" type="number" step="0.01"
        value="<?= h($edit['preco']) ?>" required>
      <label style="font-weight:600;color:#7cc9ff;">Categoria</label>
      <select name="idcategoria">
        <?php foreach ($cats as $c) {
          $sel = ($edit['idcategoria'] == $c['idcategoria']) ? 'selected' : '';
          echo "<option value='{$c['idcategoria']}' {$sel}>" . h($c['nome']) . "</option>";
        } ?>
      </select>
      <label style="font-weight:600;color:#7cc9ff;">Imagem</label>
      <?php if (!empty($edit['imagem'])): ?>
        <img src="/<?= h($edit['imagem']) ?>"
          style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
      <?php endif; ?>
      <input type="hidden" name="imagem_atual" value="<?= h($edit['imagem']) ?>">
      <input type="file" name="imagem" accept="image/*">
      <label style="font-weight:600;color:#7cc9ff;"><input type="checkbox" name="ativo" <?= $edit['ativo'] ? 'checked' : ''; ?>> Ativo</label>
      <button class="btn" type="submit"
        style="margin-top:10px;min-width:120px;"><?= $edit['idgames'] ? 'Salvar alterações' : 'Criar jogo' ?></button>
    </form>
  </div>
  <div>
    <input type="text" id="search-jogos" placeholder="Pesquisar jogos..."
      style="width:100%;margin-bottom:10px;padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
    <table id="tabela-jogos">
      <tr style="background:#181c25;color:#7cc9ff;font-size:1.08em;">
        <th>ID</th>
        <th>Nome</th>
        <th>Preço</th>
        <th>Ações</th>
      </tr>
      <?php foreach ($games as $g) { ?>
        <tr>
          <td><?= $g['idgames'] ?></td>
          <td><?= h($g['nome']) ?></td>
          <td>R$ <?= number_format($g['preco'], 2, ',', '.') ?></td>
          <td>
            <a class='btn alt' href='?edit=<?= $g['idgames'] ?>'>Editar</a>
            <a class='btn alt' href='?del=<?= $g['idgames'] ?>' onclick="return confirm('Excluir?')">Excluir</a>
          </td>
        </tr>
      <?php } ?>
    </table>
    <script>
      document.getElementById('search-jogos').addEventListener('input', function () {
        const termo = this.value.toLowerCase();
        const linhas = document.querySelectorAll('#tabela-jogos tr');
        linhas.forEach((linha, i) => {
          if (i === 0) return; // cabeçalho
          const texto = linha.textContent.toLowerCase();
          linha.style.display = texto.includes(termo) ? '' : 'none';
        });
      });
    </script>
  </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>