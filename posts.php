<?php

use utility\Session;
error_reporting(E_ALL^E_NOTICE);
require_once __DIR__ . '/vendor/autoload.php';

include('Session.php');
include('Pagination.php');
include('GetUser.php');
include('SQL_Controller.php');
$session = new Session();
$pagination = new Pagination();
$userInfo = new GetUser();
$sqlController = new SQL_Controller();

$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true], ['post' => $_POST]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
date_default_timezone_set('Asia/Kuala_Lumpur');

if($session->check('logged_in') == false) {
    header("location: index.php");
    exit;
}

$num_rows = $pagination->getNumRows();
$userID = $session->get('id');
$name = $userInfo->getUserFName($userID) . " " . $userInfo->getUserLName($userID);

$img_ext = array("png", "jpeg", "jpg", "gif");
$video_ext = array("mp4", "mp4","avi","flv","mov","mpeg");
$audio_ext = array("mp3", "flac", "wav", "alac");

if ($_POST) {
    # Condition to add post
    if (isset($_POST['postbtn'])) {
        $first_name = $userInfo->getUserFName($userID);
        $email = $userInfo->getUserEmail($userID);
        $message = strip_tags($_POST['message']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false){  #email is valid

            if($name && $email && $message) {
                $time = date("h:i A");
                $date = date("F d, Y");

                #break long strings
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
                            $sqlController->post($sql_add_post);
                            $session->flash('add_file', 'File successfully uploaded!');
                            $session->redirect($_SERVER['REQUEST_URI']);
                        }
                        elseif (in_array($file_type, $video_ext)) {     #if file is a video
                            $sql_type = "video";
                            $sql_add_post = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                            ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
                            $sqlController->post($sql_add_post);
                            $session->flash('add_file', 'File successfully uploaded!');
                            $session->redirect($_SERVER['REQUEST_URI']);
                        }
                        elseif (in_array($file_type, $audio_ext)){      #if file is audio
                            $sql_type = "audio";
                            $sql_add_post = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                            ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
                            $sqlController->post($sql_add_post);
                            $session->flash('add_file', 'File successfully uploaded!');
                            $session->redirect($_SERVER['REQUEST_URI']);
                        }
                        else{       #if file has other unsupported extensions
                            $session->flash('add_file_fail_ext', 'File not supported, try again!');
                            $session->redirect($_SERVER['REQUEST_URI']);
                        }
                    }
                    else{       #if file upload failed
                        $session->flash('add_file_fail', 'File upload failed, try again!');
                        $session->redirect($_SERVER['REQUEST_URI']);
                    }
                }
                else{       #if no file is uploaded, post only message
                    $sql_add_post = "INSERT into guestbook (name, email, message, time, date) VALUES 
                          ('$name', '$email', '$message', '$time', '$date')";
                    $sqlController->post($sql_add_post);
                    $session->flash('add', 'Post successfully added!');
                    $session->redirect($_SERVER['REQUEST_URI']);
                }
            }
            else{       #if required entries are missing
                $session->flash('add_fail_info', 'You did not enter in all the required information!');
                $session->redirect($_SERVER['REQUEST_URI']);
            }
        }
        else{   #email is invalid
            //
        }
    }

    # Condition to delete post
    if (isset($_POST['deletebtn'])) {
        $id = $_POST['id'];
        $sql_delete_post = "DELETE FROM guestbook WHERE id = $id";

        if ($sqlController->post($sql_delete_post)){
            $session->flash('delete', 'Post successfully deleted!');
            $session->redirect($_SERVER['REQUEST_URI']);
        }
        else{
            $session->flash('delete_fail', 'Post not deleted, please try again');
            $session->redirect($_SERVER['REQUEST_URI']);
        }
    }

    # Condition to edit post
    if (isset($_POST['editbtn'])) {
        $id = $_POST['id'];
        $message = strip_tags($_POST['message']);

        if ($message){
            $sql_edit_post = "UPDATE guestbook SET message = '$message' WHERE id = $id";
            $sqlController->post($sql_edit_post);
            $session->flash('edit', 'Post successfully edited!');
            $session->redirect($_SERVER['REQUEST_URI']);
        }
        else{
            $session->flash('edit_fail', 'Post not edited, please fill in the message');
            $session->redirect($_SERVER['REQUEST_URI']);
        }
    }
}

#pagination
$num_of_pages = $pagination->totalNoOfPages();
$row = $pagination->getPagination();
$row_limit_pagination = $pagination->getRowLimitPagination();
$page = $pagination->getCurrent();

echo $twig->render('posts.html.twig',
    array(
        'row' => $row, 'num_rows' => $num_rows, 'results_per_page' => $results_per_page, 'postbtn' => 'postbtn',
        'row_limit_pagination' => $row_limit_pagination, 'fName' => $first_name, 'name' => $name,
        'page' => $page, 'num_of_pages' => $num_of_pages, 'session' => $_SESSION,

        'delete' => $session->get('delete'), 'add' => $session->get('add'), 'edit' => $session->get('edit'),
        'add_file' => $session->get('add_file'), 'add_file_fail' => $session->get('add_file_fail'),
        'edit_fail' => $session->get('edit_fail'), 'add_fail' => $session->get('add_fail'),
        'delete_fail' => $session->get('delete_fail'), 'add_fail_info' => $session->get('add_fail_info'),
        'add_file_fail_ext' => $session->get('add_file_fail_ext')

    )
);