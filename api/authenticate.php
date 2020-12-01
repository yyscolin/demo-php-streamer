<?php

session_start();

$_SESSION['login_attempts'] += 1;
if ($_SESSION['login_attempts'] >= 3) {
    header("HTTP/1.0 403 Exceed Attempts");
    exit('Max Attempts Exceeded');
}

if (!isset($_POST['password']) || $_POST['password'] == '') {
    header("HTTP/1.0 400 Missing Password");
    exit('Please Specify Password');
}

switch ($_POST['password']) {
    case 'test_password':
        $_SESSION['auth'] = 1;
        exit("Welcome");
    default:
        header('HTTP/1.0 400 Invalid Password');
        exit((string)$_SESSION['login_attempts']." Unsuccessful Attempt".($_SESSION['login_attempts'] > 1 ? "s" : ""));
}

?>