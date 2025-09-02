<?php
require __DIR__ . "../../includes/db.php";
require __DIR__ . "../../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $hash = password_hash($senha, PASSWORD_BCRYPT);

    $st = $pdo->prepare("INSERT INTO usuario (nome,email,senhaHash,status,role) VALUES (?,?,?,1,'cliente')");
    try {
        $st->execute([$nome, $email, $hash]);
        header("Location: ../public/login.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $erro = "Email já está em uso. Tente outro.";
        } else {
            $erro = "Erro: " . $e->getMessage();
        }
    }
}
include __DIR__ . "../../includes/header.php";
?>
<h2>Criar conta</h2>
<?php if (!empty($erro))
    echo "<p style='color:red'>{$erro}</p>"; ?>
<form method="post">
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button class="btn">Registrar</button>
</form>
<?php include __DIR__ . "../../includes/footer.php"; ?>