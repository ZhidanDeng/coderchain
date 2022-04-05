<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/1
 * @Time: 20:18
 */

namespace dynamic\controller;



class Dynamic
{
    private $log_path = "/ex/dynamic/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'dynamic');


    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }


    /**
     * 获取项目列表
     */
    public function getLatest()
    {
        $project_module = new \project\module\Project();
        $arrProject = $project_module->getLatestProject();
        $arrMerge = array();
        if ($arrProject)
        {
            // 遍历
            foreach ($arrProject as $item) {
                $arrMerge[] = array(
                    'sUserName'=>$item["userName"],
                    "sAvatar"=>$item["avatar"],
                    "sDisplayName"=>$item["userName"],
                    'sUserId'=>$item["address"],
                    "createAt"=>$item["createTime"],
                    "sCategoryName"=>$item["projectType"],
                    "sDescription" =>$item["description"],
                    "sProjectName"=>$item["projectName"],
                    "sId" =>$item["userName"]."/".$item["projectName"]
                );
            }
        }
        $this->return_json['oRet'] = $arrMerge;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }
    
    /**
     * 
     */
    public function getLatestUser()
    {
        $module = new \user\module\User();
        $result = $module->getLatestUser();
        $this->return_json['oRet'] = $result;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }
}