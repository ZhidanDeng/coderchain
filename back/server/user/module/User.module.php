<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 20:34
 */

namespace user\module;


class User
{

    public function logout()
    {
        unset($_SESSION["user_id"]);
        return true;
    }

    public function getLoginUserId()
    {
        return isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;
    }

    /**
     * 20200814
     */

    /**
     * 登录
     * @param $user_name
     * @param $pwd
     * @return mixed
     */
    public function login( $user_name, $pwd)
    {
        $node = new \tool\module\Node();
        $result = $node->login($user_name, $pwd);
        if ($result["success"])
        {
            $this->saveLoginState( $result["data"]["address"]);
        }
        return $result;
    }

    /**
     * 判断用户名是否可用
     * @param $user_name
     * @return mixed
     */
    public function isUserNameUsable($user_name)
    {
        $node = new \tool\module\Node();
        return $node->isUserNameUsable($user_name);
    }


    public function saveLoginState($sUserId)
    {
        $_SESSION["user_id"] = $sUserId;
    }

    public function getToken($sAddress)
    {
        $node = new \tool\module\Node();
        return $node->getUserBalanceByAddress($sAddress);
    }

    public function checkLogin () {
        return $this->isLogin();
    }

    public function isLogin () {

        if (isset($_SESSION['user_id'])) {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function register($sUserName, $sPwd)
    {
        $node = new \tool\module\Node();
        return $node->register($sUserName, $sPwd);
        // 这里根据文字生成图片
//        $sText = mb_substr($sUserName, 0, 1, 'utf-8');
//        $img = $tool->drawImage($sText);
//        $sUploadDir = STATIC_AVATARS;
//        $sImgName = time() . '-' . \tool\module\Tool::uuid() . '.jpg';
//        $sImgPath =  $sUploadDir . '/' . $sImgName;
//        $bRet = $tool->saveImage($img, $sImgPath);
//        if (!$bRet) {
//            $sImgName = '';
//        }
    }
    public function getUserInfoByAddress($address)
    {
        $node = new \tool\module\Node();
        return $node->getUserInfoByAddress($address);
    }

    public function getUserInfoByName($userName)
    {
        $node = new \tool\module\Node();
        return $node->getUserInfoByName($userName);
    }
    public function updateUserInfo($userName,$password,$sex,$avatar,$description)
    {
        $node = new \tool\module\Node();
        return $node->updateUserInfo($userName,$password,$sex,$avatar,$description);

    }

    public function checkUser( $user_name,$pwd)
    {
        $node = new \tool\module\Node();
        $result =  $node->checkPassword($user_name,$pwd);
        if ($result["success"])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getUserTx($userName, $type)
    {
        $node = new \tool\module\Node();
        return $node->getUserTx($userName, $type);
    }
    public function getUserPriKey($userName, $password)
    {
        $node = new \tool\module\Node($userName, $password);
        return $node->getUserPriKey($userName, $password);
    }

    public function getUserTxDetail($hash)
    {
        $node = new \tool\module\Node();
        return $node->getUserTxDetail($hash);
    }

    public function updatePassword($userName,$oldPassword,$newPassword)
    {
        $node = new \tool\module\Node();
        return $node->updatePassword($userName,$oldPassword,$newPassword);
    }

    public function openLogin( $user_name, $pwd)
    {
        $node = new \tool\module\Node();
        $result = $node->login($user_name, $pwd);
        if ($result["success"])
        {
            $data = $result["data"];
            return array(
                'sId' => $data['address'],
                'sUserName' => $data['userName'],
                'sDisplayName' => $data['userName'],
                'sAvatar' => $data['avatar'],
                'sWalletAddress' => $data['address']
            );
        }
        else
        {
            return false;
        }
    }

    public function updateUserImage($user_name, $pwd,$imgName)
    {
        $node = new \tool\module\Node();
        return $node->updateUserImage($user_name, $pwd,$imgName);
    }

    public function checkUserIsExistInIpfs($address)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->checkUserIsExistInIpfs($address);
    }
    public function createUser($address)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->createUser($address);
    }
    public function getAllUser(){
        $node = new \tool\module\Node();
        return $node->getUserList();
    }

    public function transferByName($userName, $toUserName, $password, $amount, $remark)
    {
        $node = new \tool\module\Node();
        return $node->transferByName($userName, $toUserName, $password, $amount, $remark);
    }

    public function getTxDetail($hash)
    {
        $node = new \tool\module\Node();
        return $node->getTxDetail($hash);
    }


    public function getTransferList()
    {
        $node = new \tool\module\Node();
        return $node->getTransferList();
    }

    public function getTransferDetail($hash)
    {
        $node = new \tool\module\Node();
        return $node->getTransferDetail($hash);
    }











    function getUserPK( $sUserId)
    {
        $where = array(
            'sId'
        );
        $value = array(
            $sUserId
        );
        $dao = new \user\dao\User();
        $user = $dao->getUser($where,$value, array());
        if ($user)
        {
            return $user['sPrivateKey'];
        }
        else
        {
            return false;
        }
    }

    public function getUserAddressByUserName($sUserName)
    {
        $where = array(
            'sUserName'
        );
        $value = array(
            $sUserName
        );
        $dao = new \user\dao\User();
        $user = $dao->getUser($where,$value, array());
        if ($user)
        {
            return $user['sWalletAddress'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 通过用户名获取用户ID

     * @param $sUserName
     * @return bool
     */
    public function getUserIdByUserName($sUserName)
    {
        $where = array(
            'sUserName'
        );
        $value = array(
            $sUserName
        );
        $dao = new \user\dao\User();
        $user = $dao->getUser($where,$value, array());
        if ($user)
        {
            return $user['sId'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 通过用户ID获取用户名

     * @param $sUserId
     * @return bool
     */
    public function getUserNameByUserId($sUserId)
    {
        $where = array(
            'sId'
        );
        $value = array(
            $sUserId
        );
        $dao = new \user\dao\User();
        $user = $dao->getUser($where,$value, array());
        if ($user)
        {
            return $user['sUserName'];
        }
        else
        {
            return false;
        }
    }

    public function getUser($where, $value,$order = array())
    {
        $dao = new \user\dao\User();
        return $dao->getUser($where,$value, $order);
    }



    function hashPassword($sPassword, $sSalt)
    {
        return sha1($sSalt . $sPassword . "_CODERCHAIN");
    }
    
    public function getLatestUser()
    {
        $dao = new \user\dao\User();
        return $dao->getLatestUser();
    }
    

    
    public function getUserAddress($sUserId)
    {

        $dao = new \user\dao\User();
        return $dao->getUserAddress($sUserId);
    }


    public function salt()
    {
        $sRet = "";

        for ($i = 1; $i <= 8; $i++) {
            $sRet .= chr(rand(97, 122));
        }

        return $sRet;
    }





    public function addFeedback( $sUserName, $sContact, $sContent)
    {
        $dao = new \user\dao\User();
        return $dao->addFeedback($sUserName,$sContact,$sContent);
    }
}