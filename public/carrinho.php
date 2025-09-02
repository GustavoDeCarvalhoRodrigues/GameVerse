<?php 
require __DIR__."/../includes/db.php"; 
require __DIR__."/../includes/functions.php"; 

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!is_logged()){
        $next = urlencode($_SERVER['REQUEST_URI']);
        header("Location: ../public/login.php?next=$next");
        exit;
    }
    $acao = $_POST['acao'] ?? '';
    if($acao==='add'){
        $idg = (int)$_POST['idgames']; 
        $qtd = max(1,(int)($_POST['quantidade'] ?? 1));

        // Verifica se usuário já possui o jogo
        $chkCompra = $pdo->prepare("SELECT 1 FROM compras WHERE idusuario=? AND idgames=?");
        $chkCompra->execute([$_SESSION['user']['idusuario'], $idg]);

        if($chkCompra->fetch()){
            flash('ok','Você já possui este jogo na sua conta.');
            header("Location: ../public/carrinho.php"); 
            exit;
        }

        // Insere/atualiza na tabela carrinho
        $chk = $pdo->prepare("SELECT idcarrinho,quantidade FROM carrinho WHERE idusuario=? AND idgames=? AND compradoBool=0");
        $chk->execute([$_SESSION['user']['idusuario'],$idg]);
        $row = $chk->fetch();
        if($row){
            $upd = $pdo->prepare("UPDATE carrinho SET quantidade=quantidade+? WHERE idcarrinho=?");
            $upd->execute([$qtd,$row['idcarrinho']]);
        } else {
            $ins = $pdo->prepare("INSERT INTO carrinho (idusuario,idgames,compradoBool,datacompra,quantidade) VALUES (?,?,0,NULL,?)");
            $ins->execute([$_SESSION['user']['idusuario'],$idg,$qtd]);
        }
        flash('ok','Item adicionado ao carrinho.');
        header("Location: ../public/carrinho.php"); 
        exit;
    } elseif($acao==='del'){
        $idc = (int)$_POST['idcarrinho'];
        $del = $pdo->prepare("DELETE FROM carrinho WHERE idcarrinho=? AND idusuario=? AND compradoBool=0");
        $del->execute([$idc,$_SESSION['user']['idusuario']]);
    } elseif($acao==='checkout'){
        // Finaliza compra com cupom
        $itens = $pdo->prepare("SELECT c.idcarrinho,c.idgames,c.quantidade,g.preco 
                                FROM carrinho c 
                                JOIN games g ON g.idgames=c.idgames 
                                WHERE c.idusuario=? AND c.compradoBool=0");
        $itens->execute([$_SESSION['user']['idusuario']]);
        $rows = $itens->fetchAll();
        $total = 0;
        foreach($rows as $r){
            $total += $r['preco'] * $r['quantidade'];
        }
        // Aplica desconto do cupom se enviado
        $desconto = 0;
        $cupom = null;
        if(isset($_POST['cupom']) && $_POST['cupom']){
            $cupomSt = $pdo->prepare("SELECT * FROM cupom WHERE nome=? AND ativo=1 LIMIT 1");
            $cupomSt->execute([$_POST['cupom']]);
            $cupom = $cupomSt->fetch();
            if($cupom && $total>0){
                if($cupom['tipo']==='percent'){
                    $desconto = $total * ($cupom['valor']/100);
                } else {
                    $desconto = $cupom['valor'];
                }
                if($desconto>$total) $desconto = $total;
            }
        }
        $totalFinal = $total - $desconto;
        if($totalFinal<0) $totalFinal = 0;
        $pdo->beginTransaction();
        foreach($rows as $r){
            // Verifica se já existe compra antes de inserir
            $jaTem = $pdo->prepare("SELECT 1 FROM compras WHERE idusuario=? AND idgames=?");
            $jaTem->execute([$_SESSION['user']['idusuario'], $r['idgames']]);
            if(!$jaTem->fetch()){
                $ins = $pdo->prepare("INSERT INTO compras (idusuario,idgames,datacompra,alugado) VALUES (?,?,NOW(),0)");
                $ins->execute([$_SESSION['user']['idusuario'],$r['idgames']]);
            }
            $upd = $pdo->prepare("UPDATE carrinho SET compradoBool=1, datacompra=NOW() WHERE idcarrinho=?");
            $upd->execute([$r['idcarrinho']]);
        }
        // Limpa cupom da sessão após uso
        $_SESSION['cupom'] = null;
        $pdo->commit();
        flash('ok','Compra concluída.');
        header("Location: ../public/carrinho.php"); 
        exit;
    }
}

// Sistema de cupom
$cupomMsg = '';
$cupom = null;
if(!isset($_SESSION['cupom'])) $_SESSION['cupom'] = null;
if(isset($_POST['aplicar_cupom'])){
    $codigo = trim($_POST['cupom'] ?? '');
    $cupomSt = $pdo->prepare("SELECT * FROM cupom WHERE nome=? AND ativo=1 LIMIT 1");
    $cupomSt->execute([$codigo]);
    $cupom = $cupomSt->fetch();
    if($cupom){
        $_SESSION['cupom'] = $cupom;
        $cupomMsg = "Cupom aplicado: ".h($cupom['nome']);
    } else {
        $_SESSION['cupom'] = null;
        $cupomMsg = "Cupom inválido ou inativo.";
    }
}
if(isset($_POST['remover_cupom'])){
    $_SESSION['cupom'] = null;
    $cupomMsg = "Cupom removido.";
}
$cupom = $_SESSION['cupom'];

$uid = $_SESSION['user']['idusuario'] ?? 0;
if(!$uid && !is_logged()){
    $next = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../public/login.php?next=$next");
    exit;
}
$cart = [];
if($uid){
    $st = $pdo->prepare("SELECT c.idcarrinho,c.quantidade,g.nome,g.preco 
                         FROM carrinho c 
                         JOIN games g ON g.idgames=c.idgames 
                         WHERE c.idusuario=? AND c.compradoBool=0");
    $st->execute([$uid]);
    $cart = $st->fetchAll();
}

include __DIR__."/../includes/header.php";
?>

<section class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; background: linear-gradient(120deg, #1a2230 60%, #2b8ef9 100%); border-radius: 18px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); margin-bottom: 36px; padding: 24px 16px 18px 16px; text-align: center;">
    <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: 0.03em; text-shadow: 0 2px 12px #2b8ef9a0;">Carrinho</h1>
    <p style="font-size: 1.05rem; color: #e7e9ee; max-width: 600px; margin: 0 auto 10px auto;">Veja e finalize suas compras de jogos digitais.</p>
</section>

<?php if($m = flash('ok')){ echo "<p style='color:#7cc9ff;font-weight:600;text-align:center'>{$m}</p>"; } ?>
<?php if(!$uid){ echo "<p style='color:#9aa4b2;text-align:center'>Entre para ver seu carrinho.</p>"; include __DIR__."/../includes/footer.php"; exit; } ?>


<div style="display: flex; flex-wrap: wrap; gap: 32px; justify-content: center; align-items: flex-start; margin-bottom: 32px;">
    <div style="flex: 2; min-width: 340px; max-width: 520px; background: #181c25; border-radius: 16px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); padding: 24px 18px 18px 18px;">
        <table style="width:100%; margin-bottom: 0; border-collapse: separate; border-spacing: 0 8px;">
            <tr style="background:#23293a;color:#7cc9ff;font-size:1.08em;">
                <th style="padding:10px 8px; border-radius: 8px 0 0 8px;">Item</th>
                <th style="padding:10px 8px;">Qtd</th>
                <th style="padding:10px 8px;">Preço</th>
                <th style="padding:10px 8px; border-radius: 0 8px 8px 0;"></th>
            </tr>
            <?php 
            $total=0; 
            foreach($cart as $it){ 
                $total += $it['preco']*$it['quantidade'];
                echo "<tr style='background:#202534;'>
                                <td style='padding:10px 8px;border-radius:8px 0 0 8px;color:#e7e9ee;'>".h($it['nome'])."</td>
                                <td style='padding:10px 8px;color:#e7e9ee;'>".(int)$it['quantidade']."</td>
                                <td style='padding:10px 8px;color:#7cc9ff;'>R$ ".number_format($it['preco'],2,',','.')."</td>
                                <td style='padding:10px 8px;border-radius:0 8px 8px 0;'>
                                    <form method='post' style='display:inline'>
                                        <input type='hidden' name='acao' value='del'>
                                        <input type='hidden' name='idcarrinho' value='{$it['idcarrinho']}'>
                                        <button class='btn alt'>Remover</button>
                                    </form>
                                </td>
                            </tr>";
            } ?>
        </table>
    </div>

        <?php
            // Calcula desconto do cupom na visualização do carrinho
            if (!isset($desconto)) {
                $desconto = 0;
                if ($cupom && $total > 0) {
                    if ($cupom['tipo'] === 'percent') {
                        $desconto = $total * ($cupom['valor'] / 100);
                    } else {
                        $desconto = $cupom['valor'];
                    }
                    if ($desconto > $total) $desconto = $total;
                }
            }
        ?>
        <div style="flex: 1; min-width: 320px; max-width: 340px; background: #181c25; border-radius: 16px; box-shadow: 0 4px 32px 0 rgba(43,142,249,0.10); padding: 24px 18px 18px 18px; display: flex; flex-direction: column; gap: 18px; align-items: stretch;">
            <form method="post" style="display:flex;gap:8px;align-items:center;">
                <input type="text" name="cupom" placeholder="Cupom de desconto" value="<?= h($cupom['nome'] ?? '') ?>" <?= $cupom ? 'readonly' : '' ?> style="flex:2; background:#222; color:#7cc9ff; border:1px solid #2b8ef9; border-radius:8px; padding:10px;">
                <?php if(!$cupom): ?>
                    <button class="btn" name="aplicar_cupom" style="flex:1;">Aplicar</button>
                <?php else: ?>
                    <button class="btn alt" name="remover_cupom" style="flex:1;">Remover</button>
                <?php endif; ?>
            </form>
            <?php if($cupomMsg): ?><p style="color:#7cc9ff;text-align:right;margin:0;\"><?= $cupomMsg ?></p><?php endif; ?>
            <div style="background:#23293a;padding:16px 12px 20px 12px;border-radius:12px;box-shadow:0 2px 12px #0002; margin-bottom: 12px;">
                <p style="font-size:1.08em;margin:0 0 6px 0;">Valor dos jogos: <span style="color:#7cc9ff">R$ <?= number_format($total,2,',','.') ?></span></p>
                <p style="font-size:1.08em;margin:0 0 6px 0;">Valor retirado pelo cupom: <span style="color:#7cc9ff">-R$ <?= number_format($desconto,2,',','.') ?></span></p>
                <p style="font-size:1.15em;margin:0 0 0 0;"><b>Total:</b> <span style="color:#7cc9ff">R$ <?= number_format($total-$desconto,2,',','.') ?></span></p>
            </div>
            <?php if($cart): ?>
                <button class="btn" id="btn-finalizar-compra" style="min-width:180px;font-size:1.1em;align-self:center;">Finalizar compra</button>
            <?php endif; ?>
        </div>
</div>



<?php if($cart): ?>
    <!-- Modal Pagamento -->
        <div id="modal-pagamento-bg" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(10,18,30,0.85);align-items:center;justify-content:center;">
            <div id="modal-pagamento" style="background:#181c25;padding:32px 28px 22px 28px;border-radius:18px;box-shadow:0 8px 32px #000a;min-width:320px;max-width:95vw;width:380px;position:relative;">
                <button id="close-modal-pagamento" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:1.5em;color:#7cc9ff;cursor:pointer;">&times;</button>
                <h2 style="color:#7cc9ff;text-align:center;margin-bottom:18px;">Pagamento</h2>
                <div style="display:flex;gap:10px;justify-content:center;margin-bottom:18px;">
                    <button id="btn-pix" class="btn" style="flex:1;background:#2b8ef9;">Pix</button>
                    <button id="btn-cartao" class="btn alt" style="flex:1;">Cartão</button>
                </div>
                <div id="pagamento-pix" style="display:block;">
                    <p style="text-align:center;">Escaneie o QR Code ou copie a chave Pix:</p>
                    <div style="display:flex;justify-content:center;margin:12px 0;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=gameverse-pix-exemplo" alt="QR Pix" style="background:#fff;padding:8px;border-radius:12px;">
                    </div>
                    <div style="text-align:center;margin-bottom:10px;">
                        <input type="text" value="pix@gameverse.com" readonly style="width:80%;text-align:center;background:#222;border:none;color:#7cc9ff;font-size:1.1em;">
                    </div>
                    <form method="post" style="text-align:center;">
                        <input type="hidden" name="acao" value="checkout">
                        <input type="hidden" name="pagamento" value="pix">
                        <?php if($cupom): ?><input type="hidden" name="cupom" value="<?= h($cupom['nome']) ?>"><?php endif; ?>
                        <button class="btn" style="width:100%;">Confirmar pagamento Pix</button>
                    </form>
                </div>
                <div id="pagamento-cartao" style="display:none;">
                    <form method="post">
                        <input type="hidden" name="acao" value="checkout">
                        <input type="hidden" name="pagamento" value="cartao">
                        <?php if($cupom): ?><input type="hidden" name="cupom" value="<?= h($cupom['nome']) ?>"><?php endif; ?>
                        <input type="text" name="nome" placeholder="Nome no cartão" required style="width:100%;margin-bottom:10px;">
                        <input type="text" name="numero" placeholder="Número do cartão" maxlength="19" required style="width:100%;margin-bottom:10px;">
                        <div style="display:flex;gap:8px;">
                            <input type="text" name="validade" placeholder="MM/AA" maxlength="5" required style="flex:1;">
                            <input type="text" name="cvv" placeholder="CVV" maxlength="4" required style="flex:1;">
                        </div>
                        <button class="btn" style="width:100%;margin-top:14px;">Pagar com cartão</button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            const btnFinalizar = document.getElementById('btn-finalizar-compra');
            const modalPagamentoBg = document.getElementById('modal-pagamento-bg');
            const closeModalPagamento = document.getElementById('close-modal-pagamento');
            const btnPix = document.getElementById('btn-pix');
            const btnCartao = document.getElementById('btn-cartao');
            const areaPix = document.getElementById('pagamento-pix');
            const areaCartao = document.getElementById('pagamento-cartao');
            if(btnFinalizar){
                btnFinalizar.onclick = ()=>{ modalPagamentoBg.style.display='flex'; areaPix.style.display='block'; areaCartao.style.display='none'; };
            }
            if(closeModalPagamento){
                closeModalPagamento.onclick = ()=>{ modalPagamentoBg.style.display='none'; };
            }
            if(btnPix){
                btnPix.onclick = ()=>{ btnPix.classList.add('btn'); btnPix.classList.remove('alt'); btnCartao.classList.add('alt'); btnCartao.classList.remove('btn'); areaPix.style.display='block'; areaCartao.style.display='none'; };
            }
            if(btnCartao){
                btnCartao.onclick = ()=>{ btnCartao.classList.add('btn'); btnCartao.classList.remove('alt'); btnPix.classList.add('alt'); btnPix.classList.remove('btn'); areaPix.style.display='none'; areaCartao.style.display='block'; };
            }
            modalPagamentoBg.addEventListener('click', function(e){
                if(e.target===modalPagamentoBg) modalPagamentoBg.style.display='none';
            });
        </script>
<?php endif; ?>
<?php include __DIR__."/../includes/footer.php"; ?>
