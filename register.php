<?php
#registration process, inserts user info into the DB

#set session variables
$session->set('email', $_POST['email']);
$session->set('first_name', $_POST['first_name']);
$session->set('last_name', $_POST['last_name']);
$email = $userInfo->getPostObject($_POST['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {  #email is valid
    #check if user with that email already exists
    $result = $userInfo->getUserInfoByEmail($email);

    #we know user email exists if the rows returned are > 0
    if ($result->num_rows > 0){
        $session->flash('email_exist', 'User with that email already exists!');
        $session->redirect('index.php');
    }
    else{   #email doesn't already exist in DB, proceed
        $sqlPost->registerUser();
    }
}
else{
    $session->flash('email_invalid', 'Invalid email!');
    $session->redirect($_SERVER['REQUEST_URI']);
}
