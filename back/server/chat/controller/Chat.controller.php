<?php
/**
 * Created by PhpStorm.
 * @Author: dzd
 * @DATE: 2022/3/28
 * @TIME: 15:42
 **/
namespace chat\controller;
use chat\dao;
use GatewayWorker\Gateway;

header('content-type:application/json;charset=utf8');

header("Access-Control-Allow-Origin:*");

header("Access-Control-Allow-Methods:GET,POST");

class Chat{
    private $log_path = "/ex/chat/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'chat');


    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }

    public function saveMessage(){

        $data['content'] = $_POST['text'];
        $data['fromid'] = $_POST['fromid'];
        $data['toid'] = $_POST['toid'];
        $data['time'] = $_POST['time'];
        $data['fromname'] = $this->getName($_POST['fromid']);
        $data['toname'] = $this->getName($_POST['toid']);
        $data['type'] = $_POST['type'];
        $data['isread'] = $_POST['isread'];
        $dao = new dao\Chat();
        $dao->insertData($data);

    }


    /*
     * 根据用户ID返回用户姓名
     * */
    public function getName($id){
        $dao = new dao\Chat();
        return $dao->getName($id);
    }
    /*
     * 获取头像
     * */
    public function getAvatar(){
        $fromid = $_POST['fromid'];
        $toid = $_POST['toid'];
        $dao = new dao\Chat();
        echo json_encode($dao->getAvatar($fromid,$toid));
        return json_encode($dao->getAvatar($fromid,$toid));
    }



    public function loadMessage(){
        $fromid = $_POST['fromid'];
        $toid = $_POST['toid'];
        $dao = new dao\Chat();
        echo json_encode($dao->loadData($fromid,$toid));
        return json_encode($dao->loadData($fromid,$toid));
    }

    public function uploadImg(){
        $dao = new dao\Chat();
        echo json_encode($dao->uploadImg());
        return json_encode($dao->uploadImg());

    }

}