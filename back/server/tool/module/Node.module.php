<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 20:10
 */

namespace tool\module;


class Node
{

    /*for test*/
    public function hi(){
        $response = 'success';
        return json_decode($response,true);
    }

    private function post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, NULS_API . $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response,true);
    }

    /**
     * 用户模块
     */

    /**
     * 获取用户列表
     * @return mixed
     */
    public function getUserList()
    {
        return json_decode(file_get_contents(NULS_API."/coder/user/getUserList"),true);
    }
    /**
     * 登录
     * @param $userName
     * @param $password
     * @return mixed
     */
    public function login($userName, $password)
    {
        $url = "/coder/user/login";
        $params = array(
            "userName"=>$userName,
            "password"=>$password
        );
        return $this->post($url,$params);
    }

    /**
     * 注册
     * @param $userName
     * @param $password
     * @return mixed
     */
    public function register($userName, $password)
    {
        $url = "/coder/user/register";
        $params = array(
            "userName"=>$userName,
            "password"=>$password
        );
        return $this->post($url,$params);
    }

    /**
     * 判断用户名是否可用
     * @param $userName
     * @return mixed
     */
    public function isUserNameUsable($userName)
    {
        $url = "/coder/user/isUserNameUsable";
        $params = array(
            "userName"=>$userName
        );
        $result =  $this->post($url,$params);
        if ($result["success"])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取用户私钥
     * @param $userName
     * @param $password
     * @return mixed
     */
    public function getUserPriKey($userName, $password)
    {
        $url = "/coder/user/getUserPriKey";
        $params = array(
            "userName"=>$userName,
            "password"=>$password
        );
        return $this->post($url,$params);
    }

    /**
     * 获取用户余额
     * @param $userName
     * @return mixed
     */
    public function getUserBalance($userName)
    {
        $url = "/coder/user/getUserBalance";
        $params = array(
            "userName"=>$userName
        );
        return $this->post($url,$params);
    }

    public function getUserBalanceByAddress($address)
    {
        $url = "/coder/user/getUserBalanceByAddress";
        $params = array(
            "address"=>$address
        );
        return $this->post($url,$params);
    }

    /**
     * 更新用户密码
     * @param $userName
     * @param $oldPassword
     * @param $newPassword
     * @return mixed
     */
    public function updatePassword($userName,$oldPassword,$newPassword)
    {
        $url = "/coder/user/updatePassword";
        $params = array(
            "userName"=>$userName,
            "oldPassword"=>$oldPassword,
            "newPassword"=>$newPassword
        );
        return $this->post($url,$params);
    }

    /**
     * 头像路径更新
     * @param $userName
     * @param $password
     * @param $imgName
     * @return mixed
     */
    public function updateUserImage($userName,$password,$imgName)
    {
        $url = "/coder/user/updateUserImage";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "imgName"=>$imgName
        );
        return $this->post($url,$params);
    }

    /**
     * 更新用户信息
     * @param $userName
     * @param $password
     * @param $sex
     * @param $avatar
     * @param $description
     * @return mixed
     */
    public function updateUserInfo($userName,$password,$sex,$avatar,$description)
    {
        $url = "/coder/user/updateUserInfo";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "sex"=>$sex,
            "avatar"=>$avatar,
            "description"=>$description
        );
        return $this->post($url,$params);
    }

    /**
     * 获取用户信息
     * @param $userName
     * @return mixed
     */
    public function getUserInfoByName($userName)
    {
        $url = "/coder/user/getUserInfoByName";
        $params = array(
            "userName"=>$userName
        );
        return $this->post($url,$params);
    }

    /**
     * 获取用户信息
     * @param $address
     * @return mixed
     */
    public function getUserInfoByAddress($address)
    {
        $url = "/coder/user/getUserInfoByAddress";
        $params = array(
            "address"=>$address
        );
        return $this->post($url,$params);
    }

    public function checkPassword($userName, $password)
    {
        $url = "/coder/user/checkPassword";
        $params = array(
            "userName"=>$userName,
            "password"=>$password
        );
        return $this->post($url,$params);
    }

    public function getUserTx($userName, $type)
    {
        $url = "/coder/user/getUserTx";
        $params = array(
            "userName"=>$userName,
            "type"=>$type
        );
        return $this->post($url,$params);
    }

    public function getUserTxDetail($hash)
    {
        $url = "/coder/tx/getTx";
        $params = array(
            "tx"=>$hash
        );
        return $this->post($url,$params);
    }

    /**
     * 交易模块
     */
    public function getTxDetail($hash)
    {
        $url = "/coder/tx/getTx";
        $params = array(
            "tx"=>$hash
        );
        return $this->post($url,$params);
    }

    public function getTransferList()
    {
        return json_decode(file_get_contents(NULS_API."/coder/tx/getTransferList"),true);
    }

    public function getTransferDetail($hash)
    {
        $url = "/coder/tx/getTransferDetail";
        $params = array(
            "tx"=>$hash
        );
        return $this->post($url,$params);
    }




    /**
     * 项目模块
     */

    /**
     * 项目创建
     * @param $userName
     * @param $password
     * @param $description
     * @param $projectName
     * @param $projectType
     * @return mixed
     */
    public function createProject($userName,$password,$description,$projectName,$projectType)
    {
        $url = "/coder/project/createProject";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "projectName"=>$projectName,
            "projectType"=>$projectType,
            "description"=>$description
        );
        return $this->post($url,$params);
    }

    /**
     * 更新项目
     * @param $userName
     * @param $password
     * @param $description
     * @param $projectName
     * @param $projectType
     * @return mixed
     */
    public function updateProject($userName,$password,$description,$projectName,$projectType)
    {
        $url = "/coder/project/updateProject";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "projectName"=>$projectName,
            "projectType"=>$projectType,
            "description"=>$description
        );
        return $this->post($url,$params);
    }

    /**
     * 删除项目
     * @param $userName
     * @param $password
     * @param $projectName
     * @return mixed
     */
    public function deleteProject($userName,$password,$projectName)
    {
        $url = "/coder/project/deleteProject";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "projectName"=>$projectName
        );
        return $this->post($url,$params);
    }

    /**
     * 获取用户信息
     * @param $userName
     * @param $projectName
     * @return mixed
     */
    public function getProjectInfo($userName,$projectName)
    {
        $url = "/coder/project/getProjectInfo";
        $params = array(
            "userName"=>$userName,
            "projectName"=>$projectName
        );
        return $this->post($url,$params);
    }

    /**
     * 支持项目
     * @param $userName
     * @param $password
     * @param $projectAuthor
     * @param $projectName
     * @param $voteCount
     * @return mixed
     */
    public function voteProject($userName,$password,$projectAuthor,$projectName,$voteCount)
    {
        $url = "/coder/project/voteProject";
        $params = array(
            "userName"=>$userName,
            "password"=>$password,
            "projectAuthor"=>$projectAuthor,
            "projectName"=>$projectName,
            "voteCount"=>$voteCount
        );
        return $this->post($url,$params);
    }

    /**
     * 获取项目的支持信息
     * @param $userName
     * @param $projectName
     * @return mixed
     */
    public function getProjectSupport($userName,$projectName)
    {
        $url = "/coder/project/getProjectSupport";
        $params = array(
            "userName"=>$userName,
            "projectName"=>$projectName
        );
        return $this->post($url,$params);
    }

    /**
     * 项目是否存在
     * @param $userName
     * @param $projectName
     * @return mixed
     */
    public function isProjectExist($userName,$projectName)
    {
        $url = "/coder/project/isProjectExist";
        $params = array(
            "userName"=>$userName,
            "projectName"=>$projectName
        );
        return $this->post($url,$params);
    }

    public function isProjectExistByAddress($address,$projectName)
    {
        $url = "/coder/project/isProjectExistByAddress";
        $params = array(
            "address"=>$address,
            "projectName"=>$projectName
        );
        return $this->post($url,$params);
    }

    /**
     * 获取项目列表
     * @return mixed
     */

    // 获取最新项目需要
    public function getProjectList()
    {
        return json_decode(file_get_contents(NULS_API."/coder/project/getProjectList"),true);
    }

    // 发现项目需要
    public function getAllProject()
    {
        return json_decode(file_get_contents(NULS_API."/coder/project/getAllProject"),true);
    }

    // 项目排行需要
    public function getAllProjectSupport()
    {
        return json_decode(file_get_contents(NULS_API."/coder/project/getAllProjectSupport"),true);
    }

    // 交易详情需要
    public function getSupportDetailList()
    {
        return json_decode(file_get_contents(NULS_API."/coder/project/getSupportDetailList"),true);
    }

    public function getUserProject($list)
    {
        $url = "/coder/project/getUserProject";
        $params = array(
            "list"=>$list
        );
        return $this->post($url,$params);
    }

    // 投票详情需要
    public function getSupportDetail($tx)
    {
        $url = "/coder/project/getSupportDetail";
        $params = array(
            "tx"=>$tx
        );
        return $this->post($url,$params);
    }

    /**
     * 账户模块
     */
    public function transferByName($userName, $toUserName, $password, $amount, $remark)
    {
        $url = "/coder/account/transferByName";
        $params = array(
          "userName"=>$userName,
          "toUserName"=>$toUserName,
          "password"=>$password,
          "amount"=>$amount,
          "remark"=>$remark
        );
        return $this->post($url,$params);

    }

    public function transferByAddress($address, $toAddress, $password, $amount, $remark)
    {
        $url = "/coder/account/transferByAddress";
        $params = array(
            "address"=>$address,
            "toAddress"=>$toAddress,
            "password"=>$password,
            "amount"=>$amount,
            "remark"=>$remark
        );
        return $this->post($url,$params);
    }



}