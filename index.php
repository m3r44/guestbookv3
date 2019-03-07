<?php

require_once __DIR__ . '/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true], ['post' => $_POST]); #
//$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new \Twig\Extension\DebugExtension());
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
$conn = new mysqli("localhost", "root", "root", "guestbook");
//mysqli_select_db($conn, "guestbook") or die (mysqli_error($conn));
$sql_display = mysqli_query($conn, "SELECT * FROM guestbook ORDER BY id DESC");
$num_rows = mysqli_num_rows($sql_display);
$sql_pagination = mysqli_query($conn, "SELECT * FROM guestbook");

$is_post_add = false;
$is_post_delete = false;
$result_add_post = false;
$result_delete_post = false;
$row_limit_pagination = 0;
$results_per_page = 5;

/*$paginator = new Paginator($conn, $query);
$row = $paginator->getData($limit, $page, $twig);
$page = $paginator->createLinks($links, 'pagination pagination-sm');*/

function redirect_to( $location ) {
    if ($location != NULL) {
        header("Location: {$location}");
        return true;
    }
    else{
        return false;
    }
}

if ($_POST) {
    //echo "<meta http-equiv='refresh' content='0'>";
    # Condition to add post
    if (isset($_POST['postbtn'])) {
        $is_post_add = true;
        $name = strip_tags($_POST['name']);
        $email = strip_tags($_POST['email']);
        $message = strip_tags($_POST['message']);

        if ($name && $email && $message) {
            $time = date("h:i A");
            $date = date("F d, Y");

            $result_add_post = mysqli_query($conn,
                "INSERT INTO guestbook (name, email, message, time, date) VALUES 
                            ('$name', '$email', '$message', '$time', '$date')");

        }
    }

    # Condition to delete post
    if (isset($_POST['deletebtn'])) {
        $is_post_delete = true;
        $id = $_POST['id'];
        $result_delete_post = mysqli_query($conn, "DELETE FROM guestbook WHERE id = $id");


    }

    # Condition to edit post
    if (isset($_POST['editbtn'])) {
        $id = $_POST['id'];
        $message = strip_tags($_POST['message']);
        mysqli_query($conn, "UPDATE guestbook SET message = '$message' WHERE id = $id");

    }
    $if_redirect = redirect_to('index.php');
}

//calculate total number of pages
$num_of_pages = ceil($num_rows/$results_per_page);

//determine current page
if (!isset($_GET['page'])){
    $page = 1;
}
else{
    $page = $_GET['page'];
}

//calculate LIMIT starting number for sql query
$first_result = ($page - 1) * $results_per_page;

//calculate LIMIT final number for sql query

//retrieve results from DB
$sql_pagination = "SELECT * FROM guestbook ORDER BY id DESC LIMIT ".$first_result.','.$results_per_page;
$results_pagination = mysqli_query($conn, $sql_pagination);

while ($results = mysqli_fetch_assoc($results_pagination) ) {

    $row[] = $results;
    $row_limit_pagination++;
}


echo $twig->render(
    'index.html.twig', array(
        'sql_display' => $sql_display, 'row' => $row,
        'num_rows' => $num_rows, 'results_per_page' => $results_per_page,
        'postbtn' => 'postbtn', 'row_limit_pagination' => $row_limit_pagination,
        'result_delete_post' => $result_delete_post,
        'result_add_post' => $result_add_post,
        'page' => $page, 'num_of_pages' => $num_of_pages
    )
);
