<?php

require_once __DIR__ . '/vendor/autoload.php';
$loader = new Twig_Loader_Filesystem(__DIR__);
$twig = new Twig_Environment($loader, ['debug' => true], ['post' => $_POST]); #
//$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new \Twig\Extension\DebugExtension());
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
$conn = new mysqli("localhost", "root", "root");
mysqli_select_db($conn, "guestbook") or die (mysqli_error($conn));
$sql_display = mysqli_query($conn, "SELECT * FROM guestbook ORDER BY id DESC");
$num_rows = mysqli_num_rows($sql_display);

$is_post_add = false;
$is_post_delete = false;
$result_add_post = false;
$result_delete_post = false;

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

while ($results = mysqli_fetch_assoc($sql_display)) {
    $row[] = $results;
}

echo $twig->render(
    'index.html.twig', array(
        'sql_display' => $sql_display,
        'num_rows' => $num_rows,
        'row' => $row, 'postbtn' => 'postbtn',
        'result_delete_post' => $result_delete_post,
        'result_add_post' => $result_add_post

    )
);
