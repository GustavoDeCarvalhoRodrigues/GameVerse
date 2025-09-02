<?php
function is_logged(){
    return isset($_SESSION['user']);
}

function is_admin(){
    return is_logged() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(){
    if(!is_logged()){
        header("Location: ../public/login.php?next=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(){
    if(!is_admin()){
        header("Location: ../public/login.php?next=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function flash($key, $msg=null){
    if($msg!==null){
        $_SESSION['flash'][$key] = $msg;
        return;
    }
    if(isset($_SESSION['flash'][$key])){
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

function h($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>
