<?php
require __DIR__."/../includes/db.php";
require __DIR__."/../includes/functions.php";
require_login();
include __DIR__."/../includes/header.php";


$uid = $_SESSION['user']['idusuario'];
$user_nome = $_SESSION['user']['nome'] ?? '';
$user_email = $_SESSION['user']['email'] ?? '';

// Troca de senha
$senhaMsg = '';
if(isset($_POST['trocar_senha'])){
  $senha_atual = $_POST['senha_atual'] ?? '';
  $nova_senha = $_POST['nova_senha'] ?? '';
  $nova_senha2 = $_POST['nova_senha2'] ?? '';
  if(strlen($nova_senha)<4){
    $senhaMsg = '<span style="color:#ff6a6a">A nova senha deve ter pelo menos 4 caracteres.</span>';
  } elseif($nova_senha!==$nova_senha2){
    $senhaMsg = '<span style="color:#ff6a6a">As senhas não coincidem.</span>';
  } else {
    // Verifica senha atual
    $st = $pdo->prepare("SELECT senhahash FROM usuario WHERE idusuario=?");
    $st->execute([$uid]);
    $row = $st->fetch();
    if(!$row || !password_verify($senha_atual, $row['senhahash'])){
      $senhaMsg = '<span style="color:#ff6a6a">Senha atual incorreta.</span>';
    } else {
      $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
      $upd = $pdo->prepare("UPDATE usuario SET senhahash=? WHERE idusuario=?");
      $upd->execute([$hash, $uid]);
      $senhaMsg = '<span style="color:#7cc9ff">Senha alterada com sucesso!</span>';
    }
  }
}

// Pega todas as compras do usuário
$st = $pdo->prepare("SELECT g.nome, g.imagem, g.preco, c.datacompra 
                     FROM compras c
                     JOIN games g ON g.idgames = c.idgames
                     WHERE c.idusuario=? 
                     ORDER BY c.datacompra DESC");
$st->execute([$uid]);
$compras = $st->fetchAll();
?>




<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 32px 16px 24px 16px; text-align: center;">
  <div style="display:flex;align-items:flex-start;gap:38px;justify-content:center;flex-wrap:wrap;">
    <span style="font-size:3.2em;background:#2b8ef9;border-radius:50%;width:90px;height:90px;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 2px 12px #2b8ef980;">👤</span>
    <div style="text-align:left;min-width:220px;max-width:340px;background:#181c25;padding:22px 24px 18px 24px;border-radius:14px;box-shadow:0 2px 16px #0002;">
      <h1 style="font-size: 2.1rem; font-weight: 800; color: #fff; margin: 0 0 6px 0; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Meu Perfil</h1>
      <div style="font-size:1.18em;color:#7cc9ff;font-weight:600;">Bem-vindo, <?= h($user_nome); ?>!</div>
      <div style="margin-top:10px;font-size:1.05em;color:#e7e9ee;">
        <b>Email:</b> <?= h($user_email) ?><br>
        <b>Jogos comprados:</b> <?= count($compras) ?>
      </div>
      <button class="btn" id="abrir-modal-senha" style="margin-top:18px;">Trocar senha</button>
      <?php if($senhaMsg): ?><div style="margin-top:10px; color:#7cc9ff; font-size:1.05em;"> <?= $senhaMsg ?> </div><?php endif; ?>
      <div id="modal-senha-bg" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(10,18,30,0.85);align-items:center;justify-content:center;">
        <div id="modal-senha" style="background:#181c25;padding:32px 28px 22px 28px;border-radius:18px;box-shadow:0 8px 32px #000a;min-width:320px;max-width:95vw;width:380px;position:relative;">
          <button id="close-modal-senha" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:1.5em;color:#7cc9ff;cursor:pointer;">&times;</button>
          <h2 style="color:#7cc9ff;text-align:center;margin-bottom:18px;">Trocar senha</h2>
          <form method="post" style="display:flex;flex-direction:column;gap:10px;">
            <input type="password" name="senha_atual" placeholder="Senha atual" required style="padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
            <input type="password" name="nova_senha" placeholder="Nova senha" required style="padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
            <input type="password" name="nova_senha2" placeholder="Repita a nova senha" required style="padding:8px 10px;border-radius:8px;border:1px solid #2b8ef9;background:#222;color:#7cc9ff;">
            <button class="btn" name="trocar_senha" style="margin-top:4px;">Salvar nova senha</button>
          </form>
        </div>
      </div>
</section>
<script>
  const btnAbrirSenha = document.getElementById('abrir-modal-senha');
  const modalSenhaBg = document.getElementById('modal-senha-bg');
  const closeModalSenha = document.getElementById('close-modal-senha');
  if(btnAbrirSenha && modalSenhaBg){
    btnAbrirSenha.onclick = ()=>{ modalSenhaBg.style.display='flex'; };
  }
  if(closeModalSenha && modalSenhaBg){
    closeModalSenha.onclick = ()=>{ modalSenhaBg.style.display='none'; };
  }
  if(modalSenhaBg){
    modalSenhaBg.addEventListener('click', function(e){
      if(e.target===modalSenhaBg) modalSenhaBg.style.display='none';
    });
  }
</script>
    </div>
  </div>
</section>



<section>
  <h2 style="font-size: 1.3rem; color: #7cc9ff; margin-bottom: 18px; text-align:center;">Jogos comprados</h2>
  <?php if($compras): ?>
  <div id="carousel-compras" class="grid" style="gap:28px;display:flex;flex-wrap:wrap;justify-content:center;"></div>
    <div id="carousel-compras-pagination" style="display:flex;justify-content:center;align-items:center;margin:18px 0 0 0;gap:2px;font-size:1.2em;"></div>
    <script>
      const compras = <?php echo json_encode($compras); ?>;
  const comprasPerPage = 8;
      const totalCompras = compras.length;
      const totalComprasPages = Math.max(1, Math.ceil(totalCompras / comprasPerPage));
      let currentComprasPage = 1;

      function renderCompras(page) {
        const grid = document.getElementById('carousel-compras');
        grid.innerHTML = '';
        const start = (page-1)*comprasPerPage;
        const end = Math.min(start+comprasPerPage, compras.length);
        let row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'center';
        row.style.gap = '28px';
        row.style.width = '100%';
        let count = 0;
        for(let i=start;i<end;i++) {
          if(count>0 && count%4===0) {
            grid.appendChild(row);
            row = document.createElement('div');
            row.style.display = 'flex';
            row.style.justifyContent = 'center';
            row.style.gap = '28px';
            row.style.width = '100%';
          }
          const j = compras[i];
          const img = j.imagem ? j.imagem : '../public/assets/img-placeholder.png';
          const card = document.createElement('div');
          card.className = 'card';
          card.style.display = 'flex';
          card.style.flexDirection = 'column';
          card.style.justifyContent = 'space-between';
          card.style.background = '#181c25';
          card.style.borderRadius = '14px';
          card.style.boxShadow = '0 2px 16px #0002';
          card.style.overflow = 'hidden';
          card.style.minWidth = '220px';
          card.style.maxWidth = '240px';
          card.innerHTML = `
            <img src="${img}" style="border-bottom:1px solid #1e2430;max-height:160px;object-fit:cover;width:100%;background:#222;">
            <div class="p" style="padding:14px 12px 10px 12px;">
              <b style="font-size:1.18rem;color:#2b8ef9;display:block;">${j.nome}</b>
              <span style="color:#e7e9ee;font-size:0.98em;">Adquirido em ${new Date(j.datacompra).toLocaleDateString('pt-BR')}</span><br>
              <span style="color:#7cc9ff;font-size:1.05em;">R$ ${parseFloat(j.preco).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
              <div style="margin-top:12px;display:flex;gap:8px;align-items:center;">
                <button class="btn download-btn" data-idx="${i}" style="min-width:110px;">Download</button>
                <button class="btn alt uninstall-btn" data-idx="${i}" style="min-width:110px;display:none;">Desinstalar</button>
              </div>
              <div class="install-status" id="status-${i}" style="margin-top:8px;font-size:0.98em;color:#7cc9ff;"></div>
            </div>
          `;
          row.appendChild(card);
          count++;
        }
        if(row.children.length>0) grid.appendChild(row);
        // reativa os botões
        setTimeout(ativarBotoes, 100);
      }

      function renderComprasPagination() {
        const pag = document.getElementById('carousel-compras-pagination');
        pag.innerHTML = '';
        let maxPages = Math.min(10, totalComprasPages);
        let start = Math.max(1, currentComprasPage-4);
        let end = Math.min(totalComprasPages, start+maxPages-1);
        if (end-start<maxPages-1) start = Math.max(1, end-maxPages+1);
        if(currentComprasPage>1) {
          const prev = document.createElement('a');
          prev.innerHTML = '&lt;';
          prev.href = '#';
          prev.style.margin = '0 8px';
          prev.onclick = (e)=>{e.preventDefault();goToComprasPage(currentComprasPage-1);};
          pag.appendChild(prev);
        }
        for(let i=start;i<=end;i++) {
          const a = document.createElement('a');
          a.innerText = i;
          a.href = '#';
          a.style.margin = '0 4px';
          a.style.fontWeight = (i===currentComprasPage)?'bold':'normal';
          a.style.color = (i===currentComprasPage)?'#2b8ef9':'#7cc9ff';
          a.onclick = (e)=>{e.preventDefault();goToComprasPage(i);};
          pag.appendChild(a);
        }
        if(currentComprasPage<totalComprasPages) {
          const next = document.createElement('a');
          next.innerHTML = '&gt;';
          next.href = '#';
          next.style.margin = '0 8px';
          next.onclick = (e)=>{e.preventDefault();goToComprasPage(currentComprasPage+1);};
          pag.appendChild(next);
        }
      }

      function goToComprasPage(p) {
        currentComprasPage = p;
        renderCompras(currentComprasPage);
        renderComprasPagination();
      }

      function ativarBotoes() {
        // Função para delay aleatório entre 2 e 7 segundos (ou até 2 minutos para desinstalar)
        function randomDelay(min, max) {
          return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        document.querySelectorAll('.download-btn').forEach(function(btn) {
          btn.onclick = function() {
            var idx = btn.getAttribute('data-idx');
            var status = document.getElementById('status-' + idx);
            // Se já está como Jogar, simula "abrindo o jogo"
            if(btn.classList.contains('play-btn')) {
              btn.disabled = true;
              btn.textContent = 'Abrindo...';
              status.textContent = 'Aguarde, abrindo o jogo...';
              var delay = randomDelay(2000, 12000); // 2 a 12 segundos
              setTimeout(function() {
                btn.textContent = 'Jogar';
                btn.disabled = false;
                status.textContent = 'Erro ao abrir o jogo. Tente novamente.';
                status.style.color = '#ff6a6a';
                setTimeout(function(){ status.textContent = ''; status.style.color = '#7cc9ff'; }, 4000);
              }, delay);
              return;
            }
            btn.disabled = true;
            btn.textContent = 'Instalando...';
            status.textContent = 'Aguarde, instalando o jogo...';
            var delay = randomDelay(2000, 7000); // 2 a 7 segundos
            setTimeout(function() {
              btn.textContent = 'Jogar';
              btn.classList.add('play-btn');
              btn.disabled = false;
              status.textContent = 'Instalado!';
              var unBtn = document.querySelector('.uninstall-btn[data-idx="'+idx+'"]');
              if(unBtn) unBtn.style.display = '';
            }, delay);
          };
        });
        document.querySelectorAll('.uninstall-btn').forEach(function(btn) {
          btn.onclick = function() {
            var idx = btn.getAttribute('data-idx');
            var dBtn = document.querySelector('.download-btn[data-idx="'+idx+'"]');
            var status = document.getElementById('status-' + idx);
            btn.disabled = true;
            btn.textContent = 'Desinstalando...';
            status.textContent = 'Aguarde, desinstalando o jogo...';
            var delay = randomDelay(2000, 120000); // 2s a 2min
            setTimeout(function() {
              dBtn.textContent = 'Download';
              dBtn.classList.remove('play-btn');
              dBtn.disabled = false;
              btn.style.display = 'none';
              btn.textContent = 'Desinstalar';
              status.textContent = 'Jogo desinstalado.';
              setTimeout(function(){ status.textContent = ''; }, 2000);
            }, delay);
          };
        });
      }

      // Inicializa carrossel
      renderCompras(currentComprasPage);
      renderComprasPagination();
    </script>
  <?php else: ?>
    <p style="color:#9aa4b2;">Você ainda não comprou nenhum jogo.</p>
  <?php endif; ?>
</section>

<?php include __DIR__."/../includes/footer.php"; ?>
