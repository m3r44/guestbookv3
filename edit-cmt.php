<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guestbook</title>

    <link href="https://fonts.googleapis.com/css?family=Dancing+Script" rel="stylesheet">
    <style>
        h1{
            font-family: 'Dancing Script', cursive;
            font-size: 50px;
            text-align: center;
            margin: 20px;
        }
    </style>
    <h1>Guestbook</h1>

    <link href="style.css" rel="stylesheet" type="text/css"/>
</head>
<body>
    <div class="edit-container">
        <h2>Edit your post</h2>
        <?php
        $id = $_POST['id'];
        $name = $_POST['name'];
        $time = $_POST['time'];
        $date = $_POST['date'];
        $message = $_POST['message'];

        echo "<form method='POST' action='index.php'>
        <input type='hidden' name='id' value='".$id."'>
        <input type='hidden' name='time' value='".$time."'>
        <input type='hidden' name='date' value='".$date."'>
        
        <table style='align-self: center'>
        <tr>
            <td><textarea name='message'style='width: 700px; height: 100px;'>$message</textarea></td>
        </tr>
        <tr>
            <td><button name='editbtn'>Edit</button></td>
        </tr>
          
        </table></form>"

        ?>
    </div>

</body>