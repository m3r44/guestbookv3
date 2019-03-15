<?php

require_once __DIR__ . '/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true], ['post' => $_POST]);
//$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new \Twig\Extension\DebugExtension());
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if(!$_SESSION['logged_in']){
    header("location: index.php");
}
$conn = new mysqli("localhost", "root", "root", "guestbook");
$sql_display = mysqli_query($conn, "SELECT * FROM guestbook ORDER BY id DESC");
$num_rows = mysqli_num_rows($sql_display);
$sql_pagination = mysqli_query($conn, "SELECT * FROM guestbook");

$connAcc = new mysqli("localhost", "root", "root", "guestbook");
$userID = $_SESSION['id'];
$sql_get_info = "SELECT first_name, last_name, email FROM users WHERE id = $userID";
$getUserInfo = mysqli_query($connAcc, $sql_get_info);
$userInfo = mysqli_fetch_object($getUserInfo);
$fName = $userInfo->first_name;
$lName = $userInfo->last_name;
$email_DB = $userInfo->email;

$is_post_add = false;
$is_post_edit = false;
$is_post_delete = false;
$result_add_post = null;
$result_edit_post = false;
$result_delete_post = false;
$row_limit_pagination = 0;
$results_per_page = 4;
$email_validation = null;

if ($_POST) {
    # Condition to add post
    if (isset($_POST['postbtn'])) {
        $is_post_add = true;
        //$name = strip_tags($_POST['name']);
        $name = $fName;
        $email = $email_DB;
        $message = strip_tags($_POST['message']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false){  #email is valid
            $email_validation = true;

            if($name && $email && $message) {
                $time = date("h:i A");
                $date = date("F d, Y");
                $sql_add_post = "INSERT INTO guestbook (name, email, message, time, date) VALUES 
                            ('$name', '$email', '$message', '$time', '$date')";
                $result_add_post = mysqli_query($conn, $sql_add_post);
                //break long strings
                $message = wordwrap($message, 50,"\n", true);
            }
            else{
                $result_add_post = false;
            }
        }
        else{   #email is invalid
            $email_validation = false;
            $result_add_post = false;
        }
    }

    # Condition to delete post
    if (isset($_POST['deletebtn'])) {
        $is_post_delete = true;
        $id = $_POST['id'];
        $sql_delete_post = "DELETE FROM guestbook WHERE id = $id";
        $result_delete_post = mysqli_query($conn, $sql_delete_post);
    }

    # Condition to edit post
    if (isset($_POST['editbtn'])) {
        $is_post_edit = true;
        $id = $_POST['id'];
        $message = strip_tags($_POST['message']);
        //$message = $_POST['message'];

        if ($message){
            $sql_edit_post = "UPDATE guestbook SET message = '$message' WHERE id = $id";
            $result_edit_post = mysqli_query($conn, $sql_edit_post);
        }
        else{
            $result_edit_post = false;
        }
    }
}

#calculate total number of pages
$num_of_pages = ceil($num_rows/$results_per_page);

#determine current page
if (!isset($_GET['page'])){
    $page = 1;
}
else{
    $page = $_GET['page'];
}

#calculate LIMIT starting number for sql query
$first_result = ($page - 1) * $results_per_page;

#retrieve results from DB
$sql_pagination = "SELECT * FROM guestbook ORDER BY id DESC LIMIT ".$first_result.','.$results_per_page;
$results_pagination = mysqli_query($conn, $sql_pagination);
while ($results = mysqli_fetch_assoc($results_pagination) ) {
    $row[] = $results;
    $row_limit_pagination++;    #sets last limit
}

echo $twig->render(
    'posts.html.twig', array(
        'sql_display' => $sql_display, 'row' => $row,
        'num_rows' => $num_rows, 'results_per_page' => $results_per_page,
        'postbtn' => 'postbtn', 'row_limit_pagination' => $row_limit_pagination,
        'page' => $page, 'num_of_pages' => $num_of_pages,
        'result_add_post' => $result_add_post, 'is_post_add' => $is_post_add,
        'result_edit_post' => $result_edit_post, 'is_post_edit' => $is_post_edit,
        'result_delete_post' => $result_delete_post, 'is_post_delete' => $is_post_delete,
        'email_validation' => $email_validation, 'fName' => $fName, 'lName' => $lName,
        'email' => $email_DB
    )
);
