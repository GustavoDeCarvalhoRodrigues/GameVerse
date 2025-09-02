<?php
require __DIR__."/../includes/db.php";
require __DIR__."/../includes/functions.php";
require_admin();
include __DIR__."/../includes/header.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = (int)($_POST['idnotificacoes'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if($id > 0){
        $pdo->prepare("UPDATE notificacoes SET nome=?,descricao=? WHERE idnotificacoes=?")
            ->execute([$nome,$descricao,$id]);
        flash('ok','Notificação atualizada.');
    } else {
        $pdo->prepare("INSERT INTO notificacoes (nome,descricao,dataenvio,idAdmin) VALUES (?,?,NOW(),?)")
            ->execute([$nome,$descricao,$_SESSION['user']['idusuario']]);
        flash('ok','Notificação criada.');
    }
    header("Location: ../admin/notificacoes.php"); exit;
}

if(isset($_GET['del'])){
    $pdo->prepare("DELETE FROM notificacoes WHERE idnotificacoes=?")->execute([(int)$_GET['del']]);
    flash('ok','Notificação removida.');
    header("Location: ../admin/notificacoes.php"); exit;
}

$notifs = $pdo->query("SELECT * FROM notificacoes ORDER BY dataenvio DESC")->fetchAll();
$edit = ['idnotificacoes'=>0,'nome'=>'','descricao'=>''];

if(isset($_GET['edit'])){
    $st=$pdo->prepare("SELECT * FROM notificacoes WHERE idnotificacoes=?");
    $st->execute([(int)$_GET['edit']]);
    $edit=$st->fetch() ?: $edit;
}
?>
<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
  <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Administração de Notificações</h1>
  <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Crie, edite ou remova notificações para os usuários do Gameverse.</p>
</section>

<?php if($m=flash('ok')) echo "<p style='color:#7cc9ff;font-weight:600;text-align:center'>{$m}</p>"; ?>
<div class="admin-grid">
  <div>
    <form method="post" style="background:#181c25;padding:18px 16px;border-radius:14px;box-shadow:0 2px 12px 0 rgba(20,30,50,0.10);">
      <input type="hidden" name="idnotificacoes" value="<?= (int)$edit['idnotificacoes'] ?>">
      <label style="font-weight:600;color:#7cc9ff;">Título</label><input name="nome" value="<?= h($edit['nome']) ?>" required>
      <label style="font-weight:600;color:#7cc9ff;">Descrição</label><textarea name="descricao" rows="3"><?= h($edit['descricao']) ?></textarea>
      <button class="btn" style="margin-top:10px;min-width:120px;"><?= $edit['idnotificacoes']? 'Salvar':'Criar' ?></button>
    </form>
  </div>
  <div>
    <input type="text" id="search-notificacoes" placeholder="Pesquisar notificações..." style="width:100%;margin-bottom:10px;padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
    <table id="tabela-notificacoes">
      <tr style="background:#181c25;color:#7cc9ff;font-size:1.08em;">
        <th>ID</th><th>Título</th><th>Data</th><th>Ações</th>
      </tr>
      <?php foreach($notifs as $n){ ?>
        <tr>
          <td><?= $n['idnotificacoes'] ?></td>
          <td><?= h($n['nome']) ?></td>
          <td><?= h($n['dataenvio']) ?></td>
          <td>
            <a class='btn alt' href='?edit=<?= $n['idnotificacoes'] ?>'>Editar</a>
            <a class='btn alt' href='?del=<?= $n['idnotificacoes'] ?>' onclick="return confirm('Excluir?')">Excluir</a>
          </td>
        </tr>
      <?php } ?>
    </table>
    <script>
      document.getElementById('search-notificacoes').addEventListener('input', function() {
        const termo = this.value.toLowerCase();
        const linhas = document.querySelectorAll('#tabela-notificacoes tr');
        linhas.forEach((linha, i) => {
          if(i === 0) return;
          const texto = linha.textContent.toLowerCase();
          linha.style.display = texto.includes(termo) ? '' : 'none';
        });
      });
    </script>
  </div>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
