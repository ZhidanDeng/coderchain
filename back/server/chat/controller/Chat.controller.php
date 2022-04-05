<?php
/**
 * Created by PhpStorm.
 * @Author: dzd
 * @DATE: 2022/3/28
 * @TIME: 15:42
 **/
namespace chat\controller;
header('Access-Control-Allow-Origin:*');
class Chat{
    private $log_path = "/ex/chat/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'chat');


    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }

    public function save_message(){
        /*
         * 操作数据库，保存聊天记录
         * */
    }

}