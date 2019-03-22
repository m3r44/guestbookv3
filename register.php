<?php
#registration process, inserts user info into the DB

#set session variables
$_SESSION['email'] = $_POST['email'];
$_SESSION['first_name'] = $_POST['first_name'];
$_SESSION['last_name'] = $_POST['last_name'];
$_SESSION['is_register'] = true;

#escape all $_POST variables to protect against SQL injections
$first_name = $mysqli->escape_string($_POST['first_name']);
$last_name = $mysqli->escape_string($_POST['last_name']);
$email = $mysqli->escape_string($_POST['email']);
$password = $mysqli->escape_string(password_hash($_POST['password'], PASSWORD_BCRYPT));
$hash = $mysqli->escape_string(md5(rand(0,1000)));

#check if user with that email already exists
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'") or die($mysqli->error());

#we know user email exists if the rows returned are > 0
if ($result->num_rows > 0){
    $_SESSION['email_exist'] = true;
    $_SESSION['register_success'] = false;
    header("location: index.php");
}
else{   #email doesn't already exist in DB, proceed

    #active is 0 by default
    $sql = "INSERT INTO users (first_name, last_name, email, password, hash) " .
        "VALUES ('$first_name', '$last_name', '$email', '$password', '$hash')";

    #add user to the DB
    if ($mysqli->query($sql)){
        $_SESSION['active'] = 1;
        //$_SESSION['logged_in'] = true;
        $_SESSION['register_success'] = true;
        header("location: index.php");
    }
    else{
        $_SESSION['register_success'] = false;
        header("location: index.php");
    }
}

//$twig->render('index.php', array('email_exist'=>$email_exist, 'register_success'=>$register_success));
