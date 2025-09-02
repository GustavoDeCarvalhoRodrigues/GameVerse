<?php
session_start();
session_unset(); // remove todas as variáveis da sessão
session_destroy(); // destrói a sessão
header("Location: ../public/index.php");
exit;
