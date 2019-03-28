<?php
#registration process, inserts user info into the DB

#set session variables
$session->set('email', $_POST['email']);
$session->set('first_name', $_POST['first_name']);
$session->set('last_name', $_POST['last_name']);

#escape all $_POST variables to protect against SQL injections
$first_name = $userInfo->getPostObject($_POST['first_name']);
$last_name = $userInfo->getPostObject($_POST['last_name']);
$email = $userInfo->getPostObject($_POST['email']);
$password = $userInfo->getPostObject(password_hash($_POST['password'], PASSWORD_BCRYPT));
$hash = $userInfo->getPostObject(md5(rand(0,1000)));

#check if user with that email already exists
$result = $userInfo->getUserInfoByEmail($email);

#we know user email exists if the rows returned are > 0
if ($result->num_rows > 0){
    $session->flash('email_exist', 'User with that email already exists!');
    $session->redirect('index.php');
}
else{   #email doesn't already exist in DB, proceed
    #active is 0 by default
    $sql = "INSERT INTO users (first_name, last_name, email, password, hash) " .
        "VALUES ('$first_name', '$last_name', '$email', '$password', '$hash')";

    #add user to the DB
    if ($sqlController->post($sql)){
        $session->set('active', 1);

        $session->flash('reg_success', 'Your account has been created! Please sign in.');
        $session->redirect('index.php');
    }
    else{
        $session->flash('reg_fail', 'Error, registration failed!');
        $session->redirect('index.php');
    }
}

