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
        if (!$id || is_numeric($id)){
            return false;
        }
        $sql = "select username from chat.user where user.id = '$id'";
        //查询到的结果集
        $res = mysqli_query($this->dbConnect(),$sql);
        $name = mysqli_fetch_object($res)->username;
        return $name;
    }

    /*获取用户头像*/
    public function getAvatar($fromid,$toid){
        $conn = $this->dbConnect();
        $sql = "select avatar from chat.user where user.id = '$fromid'";
        $sql .= ";select avatar from chat.user where user.id = '$toid'";
        if(!mysqli_multi_query($conn,$sql)){
            printf('error:',mysqli_error($conn));
        }
        //获取结果集
        $res = mysqli_store_result($conn,MYSQLI_STORE_RESULT);
        //mysqli_fetch_row()用数字索引取值
        $fromAvatar = $res->fetch_row()[0];
        $res->free();
        if (mysqli_next_result($conn)){
            $res = mysqli_store_result($conn);
        }
        $toAvatar = $res->fetch_row()[0];
        return [
            "fromAvatar"=>$fromAvatar,
            "toAvatar"=>$toAvatar
        ];
    }

    public function insertData($data)
    {
        $fromid = $data['fromid'];
        $toid = $data['toid'];
        $fromname = $data['fromname'];
        $toname = $data['toname'];
        $content = $data['content'];
        $time = $data['time'];
        $isread = (int)$data['isread'];
        $type = $data['$type'];
        $sql = "insert into communication(fromid, toid, fromname, toname, content, time,isread) values ('{$fromid}','{$toid}','{$fromname}','{$toname}','{$content}','{$time}','{$isread}')";
        mysqli_query($this->dbConnect(),$sql) or die("保存数据失败: ".mysqli_error($this->dbConnect()));
    }

    public function loadData($fromid,$toid){
        $conn = $this->dbConnect();
        $con1 = " fromid = '$fromid' and toid = '$toid'";
        $con2 = " fromid = '$toid' and toid = '$fromid'";

        $sql = "select * from communication where {$con1} or {$con2}";
        if(!mysqli_query($conn,$sql)){
            printf('error: ',mysqli_error($conn));
            return;
        }
        $res = mysqli_query($conn,$sql);
        $rows = mysqli_num_rows($res);
        if ($rows < 10){
            //mysqli_fetch_assoc()用关键字索引取值
            $data = $res->fetch_all(MYSQLI_ASSOC);
            $res->close();
            return $data;
        }else{
            $start = $rows - 10;
            //查询最近10条记录
            $sql = "select * from communication where {$con1} or {$con2}  order by  time limit $start,10 ";
            $res2 = mysqli_query($conn,$sql);
            $data = $res2->fetch_all(MYSQLI_ASSOC);
            return $data;
        }
    }

    public function uploadImg(){
        $file = $_FILES["file"];
        $fromid = $_POST['fromid'];
        $toid = $_POST['toid'];
        var_dump($file);
        //查找字符在指定字符串中从右面开始的第一次出现的位置，
        //如果成功，返回该字符以及其后面的字符，如果失败，则返回 NULL
        $suffix = strtolower(strrchr($file['name'],'.'));
        $type = ['.jpg','.jpeg','.gif','.png'];
        if (!in_array($suffix,$type)){
            return [
                'status'=>'IMG TYPE ERROR'
            ];
        }
        //size->byte
        if ($file['size']/1024 > 5120 ){
            return [
                'status'=>'IMG TOO LARGE'
            ];
        }

        $filename = uniqid('chat_img_',false);

        $uploadpath = BASE_PATH.'\\data\\uploads\\';
        $file_upload = $uploadpath.$filename.$suffix;
        //上传图片
        $file_remove = move_uploaded_file($file['tmp_name'],$file_upload);
        if ($file_remove){
            $name = $filename.$suffix;
            $data['content'] = $name;
            $data['fromid'] = $fromid;
            $data['toid'] = $toid;
            $data['fromname'] = $this->getName($data['fromid']);
            $data['toname'] = $this->getName($data['toid']);
            $now = new \DateTime();
            $data['time'] = $now->format('Y-m-d H-i-s');
            //文本为1，图片为2
            $data['type'] = 2;

            $conn = $this->dbConnect();
            $sql = "insert into communication(fromid, toid, fromname, toname, content, time, type) values
                    ('{$data['fromid']}','{$data['toid']}','{$data['fromname']}','{$data['toname']}','{$data['content']}','{$data['time']}','{$data['type']}')";
            $res = mysqli_query($conn,$sql);
            if ($res){
                return [
                    'status'=>'OK',
                    'img_name'=>$name
                ];
            }else{
                return [
                    'status'=>'ERROR'
                ];
            }

        }
    }

}