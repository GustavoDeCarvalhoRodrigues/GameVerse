<?php
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/functions.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int) ($_POST['idcupom'] ?? 0);
  $nome = trim($_POST['nome'] ?? '');
  $descricao = trim($_POST['descricao'] ?? '');
  $tipo = ($_POST['tipo'] === 'valor') ? 'valor' : 'percent';
  $valor = (float) ($_POST['valor'] ?? 0);
  $ativo = isset($_POST['ativo']) ? 1 : 0;

  if ($id > 0) {
    $pdo->prepare("UPDATE cupom SET nome=?,descricao=?,tipo=?,valor=?,ativo=? WHERE idcupom=?")
      ->execute([$nome, $descricao, $tipo, $valor, $ativo, $id]);
    flash('ok', 'Cupom atualizado.');
  } else {
    $pdo->prepare("INSERT INTO cupom (nome,descricao,tipo,valor,ativo) VALUES (?,?,?,?,?)")
      ->execute([$nome, $descricao, $tipo, $valor, $ativo]);
    // Notificação padrão para cupom criado
    $notMsg = "Novo cupom disponível: " . h($nome) . "! \n" . h($descricao);
    $pdo->prepare("INSERT INTO notificacoes (nome,descricao,dataenvio) VALUES (?,?,NOW())")
      ->execute(["Novo Cupom!", $notMsg]);
    flash('ok', 'Cupom criado.');
  }
  header("Location: ../admin/cupons.php");
  exit;
}

if (isset($_GET['del'])) {
  $pdo->prepare("DELETE FROM cupom WHERE idcupom=?")->execute([(int) $_GET['del']]);
  flash('ok', 'Cupom removido.');
  header("Location: ../admin/cupons.php");
  exit;
}

// Somente após todos os headers e redirecionamentos
$cupons = $pdo->query("SELECT * FROM cupom ORDER BY datacriacao DESC")->fetchAll();
$edit = ['idcupom' => 0, 'nome' => '', 'descricao' => '', 'tipo' => 'percent', 'valor' => 0, 'ativo' => 1];
if (isset($_GET['edit'])) {
  $st = $pdo->prepare("SELECT * FROM cupom WHERE idcupom=?");
  $st->execute([(int) $_GET['edit']]);
  $edit = $st->fetch() ?: $edit;
}
include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/header.php";
?>
<section class="hero"
  style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1
    style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">
    Administração de Cupons</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Gerencie cupons de
    desconto, crie, edite ou remova cupons promocionais.</p>
</section>

<?php if ($m = flash('ok'))
  echo "<p style='color:#7cc9ff;font-weight:600;text-align:center'>{$m}</p>"; ?>
<div class="admin-grid">
  <div>
    <form method="post"
      style="background:#181c25;padding:18px 16px;border-radius:14px;box-shadow:0 2px 12px 0 rgba(20,30,50,0.10);">
      <input type="hidden" name="idcupom" value="<?= (int) $edit['idcupom'] ?>">
      <label style="font-weight:600;color:#7cc9ff;">Nome</label><input name="nome" value="<?= h($edit['nome']) ?>"
        required>
      <label style="font-weight:600;color:#7cc9ff;">Descrição</label><textarea name="descricao"
        rows="3"><?= h($edit['descricao']) ?></textarea>
      <label style="font-weight:600;color:#7cc9ff;">Tipo</label>
      <select name="tipo">
        <option value="percent" <?= $edit['tipo'] === 'percent' ? 'selected' : '' ?>>Percentual</option>
        <option value="valor" <?= $edit['tipo'] === 'valor' ? 'selected' : '' ?>>Valor fixo</option>
      </select>
      <label style="font-weight:600;color:#7cc9ff;">Valor</label><input name="valor" type="number" step="0.01"
        value="<?= h($edit['valor']) ?>">
      <div style="display:flex;align-items:center;gap:8px;margin-top:8px;">
        <label for="ativo" style="font-weight:600;color:#7cc9ff;margin:0;">Ativo</label>
        <input type="checkbox" id="ativo" name="ativo" <?= $edit['ativo'] ? 'checked' : '' ?>>
      </div>
      <div style="display:flex;gap:10px;margin-top:10px;">
        <button class="btn" style="min-width:120px;"><?= $edit['idcupom'] ? 'Salvar' : 'Criar' ?></button>
        <a href="index.php" class="btn alt" style="min-width:120px;text-align:center;display:inline-block;">Voltar</a>
      </div>
    </form>
  </div>
  <div>
    <input type="text" id="search-cupons" placeholder="Pesquisar cupons..."
      style="width:100%;margin-bottom:10px;padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
    <table id="tabela-cupons">
      <tr style="background:#181c25;color:#7cc9ff;font-size:1.08em;">
        <th>ID</th>
        <th>Nome</th>
        <th>Tipo</th>
        <th>Valor</th>
        <th>Ativo</th>
        <th>Ações</th>
      </tr>
      <?php foreach ($cupons as $c) { ?>
        <tr>
          <td><?= $c['idcupom'] ?></td>
          <td><?= h($c['nome']) ?></td>
          <td><?= h($c['tipo']) ?></td>
          <td><?= h($c['valor']) ?></td>
          <td><?= $c['ativo'] ? 'Sim' : 'Não' ?></td>
          <td>
            <a class='btn alt' href='?edit=<?= $c['idcupom'] ?>'>Editar</a>
            <a class='btn alt' href='?del=<?= $c['idcupom'] ?>' onclick="return confirm('Excluir?')">Excluir</a>
          </td>
        </tr>
      <?php } ?>
    </table>
    <script>
      document.getElementById('search-cupons').addEventListener('input', function () {
        const termo = this.value.toLowerCase();
        const linhas = document.querySelectorAll('#tabela-cupons tr');
        linhas.forEach((linha, i) => {
          if (i === 0) return;
          const texto = linha.textContent.toLowerCase();
          linha.style.display = texto.includes(termo) ? '' : 'none';
        });
      });
    </script>
  </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>