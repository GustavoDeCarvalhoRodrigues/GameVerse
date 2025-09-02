<?php
session_start();
require __DIR__."/../includes/db.php"; 
require __DIR__."/../includes/functions.php";  

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    $st = $pdo->prepare("SELECT idusuario, nome, email, senhahash, status, role 
                         FROM usuario 
                         WHERE email = ? 
                         LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($senha, $u['senhahash'])) {
        if ((int)$u['status'] === 0) {
            $erro = "Conta desativada.";
        } else {
            $_SESSION['user'] = $u;
            // se veio de outra página, volta; senão vai para index
            $next = $_GET['next'] ?? 'index.php';
            header("Location: $next");
            exit;
        }
    } else {
        $erro = "Credenciais inválidas.";
    }
}

include __DIR__."/../includes/header.php"; 
?>

<h2>Entrar</h2>

<?php if (!empty($erro)): ?>
    <p><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="post">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="senha" placeholder="Senha" required>
  <button class="btn">Entrar</button>
</form>

<?php include __DIR__."/../includes/footer.php"; ?>
