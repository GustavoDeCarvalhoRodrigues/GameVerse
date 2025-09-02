
<?php 
require __DIR__."/../includes/db.php"; 
require __DIR__."/../includes/functions.php"; 
include __DIR__."/../includes/header.php"; 
?>

<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 320px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 48px 16px 36px 16px; text-align: center;">
  <h1 style="font-size: 2.8rem; font-weight: 800; color: #fff; margin-bottom: 12px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Bem-vindo ao <span style='color:#7cc9ff;'>Gameverse</span></h1>
  <p style="font-size: 1.3rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 18px auto;">Explore os melhores jogos, promoções e novidades do universo gamer em um só lugar.</p>
  <a href="../public/loja.php" class="btn" style="font-size:1.1rem; padding: 14px 32px; margin-top: 10px;">Ver Loja</a>
</section>

<section style="margin-bottom: 40px;">
  <h2 style="font-size: 2rem; font-weight: 700; color: #7cc9ff; margin-bottom: 18px;">Últimas notificações</h2>
  <div class="grid" style="gap: 24px;">
  <?php
    $notifs = $pdo->query("SELECT idnotificacoes, nome, descricao, dataenvio 
                           FROM notificacoes 
                           ORDER BY dataenvio DESC 
                           LIMIT 5")->fetchAll();
    if (!$notifs) {
        echo "<p style='color:#9aa4b2;'>Nenhuma notificação.</p>";
    } else {
        foreach ($notifs as $n) {
            echo "<div class='card' style='min-height:120px;display:flex;align-items:center;'>
                    <div class='p'>
                      <b style='font-size:1.1rem;color:#2b8ef9;'>".h($n['nome'])."</b><br>
                      <span style='color:#e7e9ee;'>".h($n['descricao'])."</span><br>
                      <small style='color:#7cc9ff;'>".date('d/m/Y H:i', strtotime($n['dataenvio']))."</small>
                    </div>
                  </div>";
        }
    }
  ?>
  </div>
</section>


<section>
  <h2 style="font-size: 2rem; font-weight: 700; color: #7cc9ff; margin-bottom: 18px;">Jogos em destaque</h2>
  <?php
    $allGames = $pdo->query("SELECT idgames, nome, descricao, preco, imagem FROM games WHERE ativo = 1 ORDER BY idgames DESC")->fetchAll();
    $gamesPerPage = 6;
    $totalGames = count($allGames);
    $totalPages = max(1, ceil($totalGames / $gamesPerPage));
  ?>
  <div style="display:flex;align-items:center;justify-content:center;gap:0;position:relative;">
    <button id="carousel-prev" style="background:none;border:none;cursor:pointer;font-size:3em;color:#e74c3c;position:relative;z-index:2;left:0;outline:none;user-select:none;line-height:1;">&#60;</button>
    <div id="carousel-games" class="grid" style="gap: 24px;"></div>
    <button id="carousel-next" style="background:none;border:none;cursor:pointer;font-size:3em;color:#e74c3c;position:relative;z-index:2;right:0;outline:none;user-select:none;line-height:1;">&#62;</button>
  </div>
  <div id="carousel-pagination" style="display:flex;justify-content:center;align-items:center;margin:18px 0 0 0;gap:2px;font-size:1.2em;"></div>
  <script>
    const allGames = <?php echo json_encode($allGames); ?>;
    const gamesPerPage = <?php echo $gamesPerPage; ?>;
    const totalPages = <?php echo $totalPages; ?>;
    let currentPage = 1;

    function renderGames(page) {
      const grid = document.getElementById('carousel-games');
      grid.innerHTML = '';
      const start = (page-1)*gamesPerPage;
      const end = Math.min(start+gamesPerPage, allGames.length);
      for(let i=start;i<end;i++) {
        const g = allGames[i];
        const img = g.imagem ? g.imagem : '../public/assets/img/placeholder.jpg';
        const card = document.createElement('div');
        card.className = 'card';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.justifyContent = 'space-between';
        card.innerHTML = `
          <img src="${img}" alt="${g.nome}" style="border-bottom:1px solid #1e2430;max-height:160px;object-fit:cover;">
          <div class="p">
            <b style="font-size:1.15rem;color:#2b8ef9;">${g.nome}</b>
            <p style="color:#e7e9ee;min-height:48px;">${g.descricao}</p>
            <p style="font-size:1.1rem;"><b style="color:#7cc9ff;">R$ ${parseFloat(g.preco).toLocaleString('pt-BR', {minimumFractionDigits:2})}</b></p>
            <a class='btn' href='../public/jogo.php?id=${g.idgames}' style='width:100%;margin-top:8px;'>Ver</a>
          </div>
        `;
        grid.appendChild(card);
      }
    }

    function renderPagination() {
      const pag = document.getElementById('carousel-pagination');
      pag.innerHTML = '';
      let maxPages = Math.min(10, totalPages);
      let start = Math.max(1, currentPage-4);
      let end = Math.min(totalPages, start+maxPages-1);
      if (end-start<maxPages-1) start = Math.max(1, end-maxPages+1);
      // seta para trás
      if(currentPage>1) {
        const prev = document.createElement('a');
        prev.innerHTML = '&lt;';
        prev.href = '#';
        prev.style.margin = '0 8px';
        prev.onclick = (e)=>{e.preventDefault();goToPage(currentPage-1);};
        pag.appendChild(prev);
      }
      // números
      for(let i=start;i<=end;i++) {
        const a = document.createElement('a');
        a.innerText = i;
        a.href = '#';
        a.style.margin = '0 4px';
        a.style.fontWeight = (i===currentPage)?'bold':'normal';
        a.style.color = (i===currentPage)?'#2b8ef9':'#7cc9ff';
        a.onclick = (e)=>{e.preventDefault();goToPage(i);};
        pag.appendChild(a);
      }
      // seta para frente
      if(currentPage<totalPages) {
        const next = document.createElement('a');
        next.innerHTML = '&gt;';
        next.href = '#';
        next.style.margin = '0 8px';
        next.onclick = (e)=>{e.preventDefault();goToPage(currentPage+1);};
        pag.appendChild(next);
      }
    }

    function goToPage(p) {
      currentPage = p;
      renderGames(currentPage);
      renderPagination();
    }
    // Inicializa
    renderGames(currentPage);
    renderPagination();
    // Setas
    const btnPrev = document.getElementById('carousel-prev');
    const btnNext = document.getElementById('carousel-next');
    if(btnPrev) btnPrev.onclick = ()=>{ if(currentPage>1) goToPage(currentPage-1); };
    if(btnNext) btnNext.onclick = ()=>{ if(currentPage<totalPages) goToPage(currentPage+1); };
    function updateArrowState() {
      if(btnPrev) btnPrev.disabled = (currentPage<=1);
      if(btnNext) btnNext.disabled = (currentPage>=totalPages);
    }
    // Atualiza setas ao mudar página
    const oldGoToPage = goToPage;
    goToPage = function(p){ oldGoToPage(p); updateArrowState(); };
    updateArrowState();
  </script>
</section>

<?php include __DIR__."/../includes/footer.php"; ?>
