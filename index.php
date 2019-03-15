<?php
error_reporting(E_ALL^E_NOTICE);
$mysqli = new mysqli("localhost", "root", "root", "guestbook");
session_start();

require_once __DIR__ . '/vendor/autoload.php';
$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['session' => $_SESSION]);    //, ['post' => $_POST]
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addGlobal('session', $_SESSION);

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['login'])){
        require 'login.php';
    }
    elseif (isset($_POST['register'])){
        require 'register.php';
    }
}
//var_dump($_SESSION);
echo $twig->render('index.twig', array('correct_pw' => $_SESSION['correct_pw'], 'email_exist'=>$_SESSION['email_exist'],
    'is_login'=>$_SESSION['is_login'], 'register_success'=>$_SESSION['register_success'], 'is_register'=>$_SESSION['is_register']));

//session_unset();

