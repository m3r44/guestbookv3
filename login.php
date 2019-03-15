<?php
#user login process, checks if user exists and password is correct

require_once __DIR__ . '/vendor/autoload.php';
$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['session' => $_SESSION]);    //, ['debug' => true]
$twig->addExtension(new \Twig\Extension\DebugExtension());

#escape email to protect against SQL injections
$email = $mysqli->escape_string($_POST['email']);
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'");
$_SESSION['is_login'] = true;

if(!$_SESSION['logged_in']){
    header("location: index.php");
}

if ($result->num_rows == 0){    #if email doesn't exist
    $_SESSION['email_exist'] = false;
    //$email_exist = $_SESSION['email_exist'];
}
else{       #if email exists
    $user = $result->fetch_assoc();
    //print_r($user);
    if (password_verify($_POST['password'], $user['password'])){    #if password is correct
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['active'] = $user['active'];

        $_SESSION['email_exist'] = true;
        //$email_exist = $_SESSION['email_exist'];
        $_SESSION['correct_pw'] = true;

        #this is how we'll know the user is logged in
        $_SESSION['logged_in'] = true;
        header("location: posts.php");
    }
    else{    #if password is incorrect
        $_SESSION['correct_pw'] = false;    #user inputs wrong password
        $_SESSION['email_exist'] = true;
        header("location: index.php");
        //$correct_pw = $_SESSION['correct_pw'];
    }
}


