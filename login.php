<?php
#user login process, checks if user exists and password is correct
require_once __DIR__ . '/vendor/autoload.php';

if ($userInfo->getUserInfoByEmail($_POST['email'])->num_rows == 0){    #if email doesn't exist
    $session->flash('email_not_exist', 'Oops, the email does not exist!');
    $session->redirect('index.php');
}
else{       #if email exists
    $user = $userInfo->getInfoAssoc($_POST['email']);
    if (password_verify($_POST['password'], $user['password'])){    #if password is correct
        $session->set('id', $user['id']);
        $session->set('email', $user['email']);
        $session->set('first_name', $user['first_name']);
        $session->set('last_name', $user['last_name']);
        $session->set('active', $user['active']);

        #this is how we'll know the user is logged in
        $session->set('logged_in', true);
        $session->redirect('posts.php');
    }
    else{    #if password is incorrect
        $session->flash('wrong_pw', 'Oops, your password is incorrect!');
        $session->redirect('index.php');
    }
}





