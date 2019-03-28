<?php
/**
 * Created by PhpStorm.
 * User: nurmahirah
 * Date: 27/3/2019
 * Time: 2:22 PM
 */

class SQL_Controller
{
    private function getConn()
    {
        $conn = new mysqli("localhost", "root", "root", "guestbook");
        return $conn;
    }

    public function post($sql){
        $conn = $this->getConn();
        $post = mysqli_query($conn, $sql);
        return $post;
    }
}