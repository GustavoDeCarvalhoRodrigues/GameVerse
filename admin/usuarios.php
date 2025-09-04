<?php
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/functions.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int) ($_POST['idusuario'] ?? 0);
  $status = isset($_POST['status']) ? 1 : 0;
  $role = trim($_POST['role'] ?? 'cliente');

  if ($id > 0) {
    $pdo->prepare("UPDATE usuario SET status=?, role=? WHERE idusuario=?")
      ->execute([$status, $role, $id]);
    flash('ok', 'Usuário atualizado.');
  } else {
    flash('erro', 'ID de usuário inválido.');
  }
  header("Location: ../admin/usuarios.php");
  exit;
}

$users = $pdo->query("SELECT idusuario,nome,email,status,role FROM usuario ORDER BY idusuario DESC")->fetchAll();
include __DIR__ . "/../includes/header.php";
?>
<section class="hero"
  style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1
    style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">
    Administração de Usuários</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Gerencie permissões, status
    e informações dos usuários cadastrados.</p>
</section>

<?php if ($m = flash('ok'))
  echo "<p style='color:#7cc9ff;font-weight:600;text-align:center'>{$m}</p>"; ?>
<div style="overflow-x:auto;">
  <input type="text" id="search-usuarios" placeholder="Pesquisar usuários..."
    style="width:100%;margin-bottom:10px;padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
  <table id="tabela-usuarios" style="margin-bottom:18px;min-width:340px;">
    <tr style="background:#181c25;color:#7cc9ff;font-size:1.08em;">
      <th>ID</th>
      <th>Nome</th>
      <th>Email</th>
      <th>Status</th>
      <th>Role</th>
      <th>Ações</th>
    </tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= (int) $u['idusuario'] ?></td>
        <td><?= h($u['nome']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= $u['status'] ? 'Ativo' : 'Inativo' ?></td>
        <td><?= h($u['role']) ?></td>
        <td>
          <form method="post" style="display:flex;gap:6px;align-items:center">
            <input type="hidden" name="idusuario" value="<?= (int) $u['idusuario'] ?>">
            <div style="display:flex;align-items:center;gap:8px;">
              <label for="status" style="font-weight:600;color:#7cc9ff;margin:0;">Ativo</label>
              <input type="checkbox" id="status" name="status" <?= $u['status'] ? 'checked' : '' ?>>
            </div>
            <select name="role">
              <option value="cliente" <?= $u['role'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <div style="display:flex;gap:10px;">
              <button class="btn alt">Salvar</button>
              <a href="index.php" class="btn alt"
                style="min-width:80px;text-align:center;display:inline-block;">Voltar</a>
            </div>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <script>
    document.getElementById('search-usuarios').addEventListener('input', function () {
      const termo = this.value.toLowerCase();
      const linhas = document.querySelectorAll('#tabela-usuarios tr');
      linhas.forEach((linha, i) => {
        if (i === 0) return;
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
      });
    });
  </script>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>