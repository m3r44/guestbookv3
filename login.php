<?php
#user login process, checks if user exists and password is correct
require_once __DIR__ . '/vendor/autoload.php';

if ($userInfo->getUserInfoByEmail($_POST['email'])->num_rows == 0){    #if email doesn't exist
    $session->flash('email_not_exist', 'Oops, the email does not exist!');
    $session->redirect('index.php');
}
else{       #if email exists
    $sqlPost->loginUser();
}
