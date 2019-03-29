<?php
error_reporting(E_ALL^E_NOTICE);

use utility\Session;
include "Session.php";
include "SQL_post.php";
include "GetUser.php";
$session = new Session();
$userInfo = new getUser();
$sqlPost = new SQL_post($userInfo, $session);

require_once __DIR__ . '/vendor/autoload.php';
$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

if($session->check('logged_in') == true) {
    header("location: posts.php");
    exit;
}
else{
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (isset($_POST['login'])){
            require 'login.php';
        }
        elseif (isset($_POST['register'])){
            require 'register.php';
        }
    }
}

echo $twig->render('index.twig',
    array('email_not_exist' => $session->get('email_not_exist'), 'wrong_pw' => $session->get('wrong_pw'),
        'email_exist' => $session->get('email_exist'), 'reg_fail' => $session->get('reg_fail'),
        'reg_success' => $session->get('reg_success'), 'email' => $session->get('email'),
        'f_name' => $session->get('first_name'), 'l_name' => $session->get('last_name'),
        'email_invalid' => $session->get('email_invalid')
    )
);