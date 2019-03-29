<?php

use utility\Session;
error_reporting(E_ALL^E_NOTICE);
require_once __DIR__ . '/vendor/autoload.php';

include('Session.php');
include('Pagination.php');
include('GetUser.php');
include('SQL_post.php');
$session = new Session();
$pagination = new Pagination();
$userInfo = new GetUser();
$sqlPost = new SQL_post($userInfo, $session);

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
$full_name = $userInfo->getUserFName($userID) . " " . $userInfo->getUserLName($userID);

if ($_POST) {
    # Condition to add post
    if (isset($_POST['postbtn'])) {
        $name = $userInfo->getUserFName($userID);
        $email = $userInfo->getUserEmail($userID);
        $message = strip_tags($_POST['message']);

        if($name && $email && $message) {
            #check for any uploaded files
            if (is_uploaded_file($_FILES['uploaded_file']['tmp_name'])) {
                $target_dir = "uploads/";
                $file_name = basename($_FILES['uploaded_file']['name']);
                $path = $target_dir . $file_name;
                $file_type = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                #if successfully moved file to folder uploads/
                if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
                    $sqlPost->checkExtension
                    ($file_type, $file_name);
                }
                else{       #if file upload failed
                    $session->flash('add_file_fail', 'File upload failed, try again!');
                    $session->redirect($_SERVER['REQUEST_URI']);
                }
            }
            else{       #if no file is uploaded, post only message
                $sqlPost->postMessage();
            }
        }
        else{       #if required entries are missing
            $session->flash('add_fail_info', 'Required information is missing!');
            $session->redirect($_SERVER['REQUEST_URI']);
        }
    }

    # Condition to delete post
    if (isset($_POST['deletebtn'])) {
        $sqlPost->deletePost($_POST['id']);
    }

    # Condition to edit post
    if (isset($_POST['editbtn'])) {
        $sqlPost->editPost($_POST['id'], $message);
    }
}

#pagination
$num_of_pages = $pagination->totalNoOfPages();
$row = $pagination->getPagination();
$row_limit_pagination = $pagination->getRowLimitPagination();
$page = $pagination->getCurrent();

echo $twig->render('posts.html.twig',
    array(
        'row' => $row, 'num_rows' => $num_rows, 'postbtn' => 'postbtn',
        'row_limit_pagination' => $row_limit_pagination, 'name' => $full_name,
        'page' => $page, 'num_of_pages' => $num_of_pages, 'session' => $_SESSION,

        'delete' => $session->get('delete'), 'add' => $session->get('add'), 'edit' => $session->get('edit'),
        'add_file' => $session->get('add_file'), 'add_file_fail' => $session->get('add_file_fail'),
        'edit_fail' => $session->get('edit_fail'), 'add_fail' => $session->get('add_fail'),
        'delete_fail' => $session->get('delete_fail'), 'add_fail_info' => $session->get('add_fail_info'),
        'add_file_fail_ext' => $session->get('add_file_fail_ext')

    )
);