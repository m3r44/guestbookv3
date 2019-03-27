<?php

use utility\Session;
error_reporting(E_ALL^E_NOTICE);
require_once __DIR__ . '/vendor/autoload.php';
include_once('Session.php');

$session = new Session();
$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true], ['post' => $_POST]);
//$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new \Twig\Extension\DebugExtension());
date_default_timezone_set('Asia/Kuala_Lumpur');

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
$is_upload = false;

$img_ext = array("png", "jpeg", "jpg", "gif");
$video_ext = array("mp4", "mp4","avi","flv","mov","mpeg");
$audio_ext = array("mp3", "flac", "wav", "alac");

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

                //break long strings
                $message = wordwrap($message, 50,"\n", true);
                //$message = nl2br($message);

                #check for any uploaded files
                if (is_uploaded_file($_FILES['uploaded_file']['tmp_name'])) {
                    $target_dir = "uploads/";
                    $file_name = basename($_FILES['uploaded_file']['name']);
                    $path = $target_dir . $file_name;
                    $file_type = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                    #if successfully moved file to folder uploads/
                    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
                        if (in_array($file_type, $img_ext)){    #if file is an image
                            $sql_type = "image";
                            $sql_add_post = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                            ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
                            $result_add_post = mysqli_query($conn, $sql_add_post);
                            //$is_upload = true;
                            $session->flash('add_file', 'File successfully uploaded!');
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        }
                        elseif (in_array($file_type, $video_ext)) {     #if file is a video
                            $sql_type = "video";
                            $sql_add_post = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                            ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
                            $result_add_post = mysqli_query($conn, $sql_add_post);
                            //$is_upload = true;
                            $session->flash('add_file', 'File successfully uploaded!');
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        }
                        elseif (in_array($file_type, $audio_ext)){      #if file is audio
                            $sql_type = "audio";
                            $sql_add_post = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                            ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
                            $result_add_post = mysqli_query($conn, $sql_add_post);
                            //$is_upload = true;
                            $session->flash('add_file', 'File successfully uploaded!');
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        }
                        else{       #if file has other unsupported extensions
                            //$is_upload = false;
                            //$result_add_post = false;
                            $session->flash('add_file_fail_ext', 'File not supported, try again!');
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        }
                    }
                    else{       #if file upload failed
                        //$is_upload = false;
                        //$result_add_post = false;
                        $session->flash('add_file_fail', 'File upload failed, try again!');
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit();
                    }
                }
                else{       #if no file is uploaded, post only message
                    $sql_add_post = "INSERT into guestbook (name, email, message, time, date) VALUES 
                          ('$name', '$email', '$message', '$time', '$date')";
                    $result_add_post = mysqli_query($conn, $sql_add_post);
                    //$is_upload = false;
                    $session->flash('add', 'Post successfully added!');
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();
                }

            }
            else{       #if required entries are missing
                //$result_add_post = false;
                $session->flash('add_fail_info', 'You did not enter in all the required information!');
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
        else{   #email is invalid
            //$email_validation = false;
            //$result_add_post = false;
        }
    }

    # Condition to delete post
    if (isset($_POST['deletebtn'])) {
        //$is_post_delete = true;
        $id = $_POST['id'];
        $sql_delete_post = "DELETE FROM guestbook WHERE id = $id";
        $result_delete_post = mysqli_query($conn, $sql_delete_post);

        if ($result_delete_post == true){
            $session->flash('delete', 'Post successfully deleted!');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
        else{
            $session->flash('delete_fail', 'Post not deleted, please try again');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }

    }

    # Condition to edit post
    if (isset($_POST['editbtn'])) {
        //$is_post_edit = true;
        $id = $_POST['id'];
        $message = strip_tags($_POST['message']);

        if ($message){
            $sql_edit_post = "UPDATE guestbook SET message = '$message' WHERE id = $id";
            $result_edit_post = mysqli_query($conn, $sql_edit_post);
            $session->flash('edit', 'Post successfully edited!');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
        else{
            //$_SESSION['result_edit_post'] = false;
            $session->flash('edit_fail', 'Post not edited, please fill in the message');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
        'num_rows' => $num_rows, 'results_per_page' => $results_per_page, 'postbtn' => 'postbtn',
        'row_limit_pagination' => $row_limit_pagination, 'fName' => $fName, 'lName' => $lName,
        'page' => $page, 'num_of_pages' => $num_of_pages, 'session' => $_SESSION,

        'delete' => $session->get('delete'), 'add' => $session->get('add'), 'edit' => $session->get('edit'),
        'add_file' => $session->get('add_file'), 'add_file_fail' => $session->get('add_file_fail'),
        'edit_fail' => $session->get('edit_fail'), 'add_fail' => $session->get('add_fail'),
        'delete_fail' => $session->get('delete_fail'), 'add_fail_info' => $session->get('add_fail_info'),
        'add_file_fail_ext' => $session->get('add_file_fail_ext'),

    )
);