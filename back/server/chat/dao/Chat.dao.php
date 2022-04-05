<?php
/**
 * Created by PhpStorm.
 * @Author: dzd
 * @DATE: 2022/4/4
 * @TIME: 23:17
 **/
namespace chat\dao;
class Chat{

    /*连接数据库*/
    public function dbConnect(){
        $dbhost = 'localhost:3306';
        $dbuser = 'root';
        $dbpass = '1149691788';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
        if(! $conn ){
            die('Could not connect: ' . mysqli_error($conn));
        }
        mysqli_select_db($conn,'chat');
        mysqli_set_charset($conn,'utf8');
        return $conn;
    }

    /*根据用户ID查询用户名*/
    public function getName($id){
        $sql = 'select username from chat.user where user.id = '.$id;
        //查询到的结果集
        $res = mysqli_query($this->dbConnect(),$sql);
        $name = mysqli_fetch_object($res)->username;
        return $name;
    }


}