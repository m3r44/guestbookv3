<?php

class SQL_post
{
    private $img_ext = array("png", "jpeg", "jpg", "gif");
    private $video_ext = array("mp4", "mp4","avi","flv","mov","mpeg");
    private $audio_ext = array("mp3", "flac", "wav", "alac");
    private $userInfo, $session;

    public function __construct(GetUser $userInfo, \utility\Session $session)
    {
        $this->userInfo = $userInfo;
        $this->session = $session;
    }

    private function getTime(){
        $time = date("h:i A");
        return $time;
    }

    private function getDate(){
        $date = date("F d, Y");
        return $date;
    }

    private function wrapMessage(){
        $message = strip_tags($_POST['message']);
        #break long strings
        $message = wordwrap($message, 50,"\n", true);
        //$message = nl2br($message);
        return $message;
    }

    public function post($sql){
        $conn = $this->userInfo->getConn();
        $post = mysqli_query($conn, $sql);
        return $post;
    }

    public function postMessage(){
        $name = $this->userInfo->getUserFName($this->session->get('id'));
        $email = $this->userInfo->getUserEmail($this->session->get('id'));
        $message = $this->wrapMessage();
        $time = $this->getTime();
        $date = $this->getDate();
        $sql_add_post = "INSERT into guestbook (name, email, message, time, date) VALUES 
                          ('$name', '$email', '$message', '$time', '$date')";
        $this->post($sql_add_post);
        $this->session->flash('add', 'Post successfully added!');
        $this->session->redirect($_SERVER['REQUEST_URI']);
    }

    public function postFile($file_name, $sql_type){
        $name = $this->userInfo->getUserFName($this->session->get('id'));
        $email = $this->userInfo->getUserEmail($this->session->get('id'));
        $message = $this->wrapMessage();
        $time = $this->getTime();
        $date = $this->getDate();
        $sql_add_image = "INSERT into guestbook (name, email, message, image, type, time, date) VALUES 
                        ('$name', '$email', '$message', '" . $file_name . "', '$sql_type', '$time', '$date')";
        $conn = $this->userInfo->getConn();
        mysqli_query($conn, $sql_add_image);
        $this->session->flash('add_file', 'File successfully uploaded!');
        $this->session->redirect($_SERVER['REQUEST_URI']);
    }

    public function deletePost($id){
        $sql_delete_post = "DELETE FROM guestbook WHERE id = $id";

        if ($this->post($sql_delete_post)){
            $this->session->flash('delete', 'Post successfully deleted!');
            $this->session->redirect($_SERVER['REQUEST_URI']);
        }
        else{
            $this->session->flash('delete_fail', 'Post not deleted, please try again');
            $this->session->redirect($_SERVER['REQUEST_URI']);
        }
    }

    public function editPost($id, $message){
        if ($message){
            $sql_edit_post = "UPDATE guestbook SET message = '$message' WHERE id = $id";
            $this->post($sql_edit_post);
            $this->session->flash('edit', 'Post successfully edited!');
            $this->session->redirect($_SERVER['REQUEST_URI']);
        }
        else{
            $this->session->flash('edit_fail', 'Post not edited, please fill in the message');
            $this->session->redirect($_SERVER['REQUEST_URI']);
        }
    }

    public function checkExtension($file_type, $file_name){
        if (in_array($file_type, $this->img_ext)){    #if file is an image
            $sql_type = "image";
            $this->postFile($file_name, $sql_type);
        }
        elseif (in_array($file_type, $this->video_ext)) {     #if file is a video
            $sql_type = "video";
            $this->postFile($file_name, $sql_type);
        }
        elseif (in_array($file_type, $this->audio_ext)){      #if file is audio
            $sql_type = "audio";
            $this->postFile($file_name, $sql_type);
        }
        else{       #if file has other unsupported extensions
            $this->session->flash('add_file_fail_ext', 'File not supported, try again!');
            $this->session->redirect($_SERVER['REQUEST_URI']);
        }
    }

    public function registerUser(){
        #escape all $_POST variables to protect against SQL injections
        $first_name = $this->userInfo->getPostObject($_POST['first_name']);
        $last_name = $this->userInfo->getPostObject($_POST['last_name']);
        $email = $this->userInfo->getPostObject($_POST['email']);
        $password = $this->userInfo->getPostObject(password_hash($_POST['password'], PASSWORD_BCRYPT));
        $hash = $this->userInfo->getPostObject(md5(rand(0,1000)));

        #active is 0 by default
        $sql = "INSERT INTO users (first_name, last_name, email, password, hash) " .
            "VALUES ('$first_name', '$last_name', '$email', '$password', '$hash')";

        #add user to the DB
        if ($this->post($sql)){
            $this->session->set('active', 1);
            $this->session->flash('reg_success', 'Your account has been created! Please sign in.');
            $this->session->redirect('index.php');
        }
        else{
            $this->session->flash('reg_fail', 'Error, registration failed!');
            $this->session->redirect('index.php');
        }
    }

    public function loginUser(){
        $user = $this->userInfo->getInfoAssoc($_POST['email']);

        if (password_verify($_POST['password'], $user['password'])){    #if password is correct
            $this->session->set('id', $user['id']);
            $this->session->set('email', $user['email']);
            $this->session->set('first_name', $user['first_name']);
            $this->session->set('last_name', $user['last_name']);
            $this->session->set('active', $user['active']);

            #this is how we'll know the user is logged in
            $this->session->set('logged_in', true);
            $this->session->redirect('posts.php');
        }
        else{    #if password is incorrect
            $this->session->flash('wrong_pw', 'Oops, your password is incorrect!');
            $this->session->redirect('index.php');
        }
    }
}