<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/4
 * @Time: 12:47
 */
namespace user\controller;


class User
{
    private $log_path = "/ex/user/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'user');

    public function hi(){
        echo "hi";
    }

    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);

        // 设置跨域
        $sOrigin = $_SERVER['HTTP_ORIGIN'];
        header('Access-Control-Allow-Origin: ' . $sOrigin);
        header('Access-Control-Allow-Credentials: true');
    }

    public function test()
    {
        $oRedis = \tool\module\Redis::connect();
        $sUserInfo = $oRedis->set("test","ttt");
        var_dump($sUserInfo);
    }

    function get_nuls_params($method,$params){
        $data = array(
            "jsonrpc"=>"2.0",
            "method"=>$method,
            "params"=>$params,
            "id"=>1234
        );
        return json_encode($data);
    }
    function http_post_nuls($url, $method, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_nuls_params($method,$params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;'));


        $response = curl_exec($ch);
        $err = curl_error($ch);
        if ($err){
            return $err;
        }

        curl_close($ch);
        return json_decode($response,true);
    }
    public function addFeedback()
    {
        $content = quick_input("content");
        $password = quick_input("password");
        $address = quick_input("saddress");
        if (!isset($content))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $arr = array(
                2,
                $address,
                $password,
                0,
                2000000,
                30,
                "tNULSeBaNAKV9SLpncJK7rTPNQBoi1CENpg18s",
                "addComments",
                null,
                array(
                    $content
                ),
                "remark-jsonrpc-call",
                null
            );

            $result = $this->http_post_nuls("http://42.194.219.30:18004/jsonrpc", "contractCall",$arr);
            if ($result["result"])
            {
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = 'success';
            }
            else
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '反馈失败';
            }

        }
        exit_output($this->return_json);

    }

    public function getFeedbackList()
    {
        $arr = array(
            2,
            "tNULSeBaNAKV9SLpncJK7rTPNQBoi1CENpg18s",
            "getComments",
            "",array()
        );
        $result = $this->http_post_nuls("http://42.194.219.30:18004/jsonrpc", "invokeView",$arr);
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = 'success';
        var_dump($result);
        if ($result["result"]["result"]){
            $this->return_json['oRet'] = json_decode($result["result"]["result"]);
        }
        else
        {
            $this->return_json['oRet'] = array();
        }

        exit_output($this->return_json);
    }

    public function transfer()
    {
        $user_module = new \user\module\User();
        $userName = securely_input("fromUser");
        $toUserName = securely_input("toUser");
        $password = securely_input("password");
        $amount = securely_input("transferCount");
        $remark = securely_input("remark");
        if (!isset($userName) || !isset($toUserName) || !isset($password) || !isset($amount) || !isset($remark))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
        }
        elseif ($user_module->isUserNameUsable($userName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '发款用户不存在';
        }
        elseif ($user_module->isUserNameUsable($toUserName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '收款用户不存在';
        }
        elseif (!$user_module->checkUser($userName,$password))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '密码错误';
        }
        else
        {
            $result = $user_module->transferByName($userName, $toUserName, $password, $amount, $remark);
            if ($result["success"])
            {
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '转账成功';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                if ($result["msg"])
                {
                    $this->return_json['retMsg'] = $result["msg"];
                }
                else
                {
                    $this->return_json['retMsg'] = '转账失败，请检查余额等信息后再重新尝试';
                }
            }
        }
        exit_output($this->return_json);
    }


    /**
     * 用户登录
     */
    public function login()
    {
        $user_name = securely_input('sUserName');
        $paw = securely_input('sPwd');
        if (!isset($user_name) || !isset($paw))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $paw))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->login($user_name, $paw);
            if ($result["success"])
            {
                $data = $result['data'];

                if (!$user_module->checkUserIsExistInIpfs($data['address']))
                {
                    $user_module->createUser($data['address']);
                }

                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = 'success';
                $this->return_json['oRet'] = array(
                    'sId' => $data['address'],
                    'sUserName' => $data['userName'],
                    'sDisplayName' => $data['userName'],
                    'sAvatar' => $data['avatar'],
                    'sWalletAddress' => $data['address']
                );
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不存在或密码错误';
            }

        }
        exit_output($this->return_json);

    }

    /**
     * 用户注册
     */
    public function register()
    {
        $user_name = securely_input('sUserName');
        $paw = securely_input('sPwd');
        if (!isset($user_name) || !isset($paw))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $paw))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
        }
        else
        {

            $user_module = new \user\module\User();
            if (!$user_module->isUserNameUsable( $user_name))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '用户名已经存在，请选择其他用户名';
            }
            else
            {
                $result = $user_module->register($user_name, $paw);
                if ($result["success"])
                {
                    // ipfs创建目录
                    if (!$user_module->checkUserIsExistInIpfs($result["data"]["address"]))
                    {
                        $user_module->createUser($result["data"]["address"]);
                    }
                    // 创建目录
                    $file = new \file\module\File();
                    $file->mkdir(STATIC_PROJECTS."/".$result["data"]["address"]);
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '注册成功，马上登陆吧';
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    if ($result["msg"])
                    {
                        $this->return_json['retMsg'] = $result["msg"];
                    }
                    else
                    {
                        $this->return_json['retMsg'] = '注册失败，网络繁忙，请重新再试';
                    }
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 登出
     */
    public function logout()
    {
        $user_module = new \user\module\User();
        $user_module->logout();
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '退出成功';
        exit_output($this->return_json);
    }

    /**
     * 获取sessionID
     */
    public function getSessionId()
    {
        $user_module = new \user\module\User();
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = 'sessionId';
        $this->return_json['oRet'] = $user_module->getLoginUserId();
        exit_output($this->return_json);
    }

    /**
     * 获取用户在链上的token数
     */
    public function getToken()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $address  = $user_module->getLoginUserId();
            if ($address)
            {
                $result = $user_module->getToken($address);
                if ($result["success"])
                {
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '获取Token成功';
                    $this->return_json['oRet']['dBalance'] = intval($result["data"]);
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    if ($result["msg"])
                    {
                        $this->return_json['retMsg'] = $result["msg"];
                    }
                    else
                    {
                        $this->return_json['retMsg'] = '获取Token失败';
                    }

                }
            }
            else
            {
                $this->return_json['retCode'] = -10;
                $this->return_json['retMsg'] = '没有登录no_address';
            }

        }
        exit_output($this->return_json);
    }

    /**
     * 获取登录状态
     */
    public function getLoginState()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $address = $user_module->getLoginUserId();
            $result = $user_module->getUserInfoByAddress($address);
            if ($result["success"])
            {
                $data = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取成功';
                $this->return_json['oRet'] = array(
                    'sId' => $data['address'],
                    'sUserName' => $data['userName'],
                    'sDisplayName' => $data['userName'],
                    'sAvatar' => $data['avatar'],
                    'sWalletAddress' => $data['address'],
                    'iSex' => $data['sex'],
                    'sDescription'=>$data["description"]
                );
            }
            else
            {
                $this->return_json['retCode'] = 100;
                if ($result["msg"])
                {
                    $this->return_json['retMsg'] = $result["msg"];
                }
                else
                {
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }


    /**
     * 获取用户信息
     */
    public function getInfo()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $address = $user_module->getLoginUserId();
            $result = $user_module->getUserInfoByAddress($address);
            if ($result["success"])
            {
                $data = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取成功';
                $this->return_json['oRet'] = array(
                    'sId' => $data['address'],
                    'sUserName' => $data['userName'],
                    'sDisplayName' => $data['userName'],
                    'sAvatar' => $data['avatar'],
                    'sWalletAddress' => $data['address'],
                    'iSex' => $data['sex'],
                    'sDescription'=>$data["description"]
                );
            }
            else
            {
                $this->return_json['retCode'] = 100;
                if ($result["msg"])
                {
                    $this->return_json['retMsg'] = $result["msg"];
                }
                else
                {
                    $this->return_json['retMsg'] = '用户不存在';
                }

            }
        }
        exit_output($this->return_json);
    }

    /**
     * 通过用户名获取用户信息
     */
    public function getInfoByName()
    {
        $user_name = securely_input('sUserName');
        if (!isset($user_name))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->getUserInfoByName($user_name);
            if ($result["success"])
            {
                $data = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取成功';
                $this->return_json['oRet'] = array(
                    'sId' => $data['address'],
                    'sUserName' => $data['userName'],
                    'sDisplayName' => $data['userName'],
                    'sAvatar' => $data['avatar'],
                    'sWalletAddress' => $data['address'],
                    'iSex' => $data['sex'],
                    'sDescription'=>$data["description"]
                );
            }
            else
            {
                $this->return_json['retCode'] = 100;
                if ($result["msg"])
                {
                    $this->return_json['retMsg'] = $result["msg"];
                }
                else
                {
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 修改用户信息
     */
    public function update()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $address = $user_module->getLoginUserId();
            $user = $user_module->getUserInfoByAddress($address);
            if ($user["success"])
            {
                $user_info = $user["data"];
                $user_name = securely_input('sUserName');
                $display_name = securely_input('sDisplayName');
                $sex = securely_input('iSex',"-1");
                $avatar = securely_input('sAvatar',"");
                $description = securely_input('sDescription',"");
                $password = securely_input("password");
                if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
                {
                    $this->return_json['retCode'] = -101;
                    $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
                }
                elseif (!isset($user_name) || $user_name != $user_info["userName"])
                {
                    $this->return_json['retCode'] = -101;
                    $this->return_json['retMsg'] = '用户名错误';
                }
                elseif (!isset($display_name) || $display_name != $user_info["userName"])
                {
                    $this->return_json['retCode'] = -101;
                    $this->return_json['retMsg'] = '用户名错误';
                }
                else
                {
                    if (!isset($sex))
                    {
                        $sex = "-1";
                    }
                    if (!isset($avatar))
                    {
                        $avatar = "";
                    }
                    if (!isset($description))
                    {
                        $description = "";
                    }
                    $result = $user_module->updateUserInfo($user_name,$password,$sex,$avatar,$description);
                    if ($result["success"]) {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '修改信息成功';
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = $result["msg"] ? $result["msg"]:'更改信息失败';
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                if ($user["msg"])
                {
                    $this->return_json['retMsg'] = $user["msg"];
                }
                else
                {
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }


    /**
     * 修改密码
     */
    public function updatePassword()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sUserName =securely_input('sUserName');
            $sOriginPwd = securely_input('sOriginPwd');
            $sPwd = securely_input('sPwd');

            if (!isset($sUserName) || !isset($sOriginPwd) || !isset($sPwd))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $sPwd))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $sOriginPwd))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            else
            {
                $result = $user_module->checkUser($sUserName,$sOriginPwd);
                if ($result)
                {
                    $result = $user_module->updatePassword($sUserName,$sOriginPwd,$sPwd);
                    if ($result["success"])
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '修改密码成功';
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = $result["msg"] ? $result["msg"]:'修改密码失败';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '原密码不正确';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 上传文件
     */
    public function uploadFile()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $user_name =securely_input('sUserName');
            $sPwd = securely_input('password');
            if (!isset($user_name) || !isset($sPwd))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $sPwd))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            else
            {
                $file = $_FILES['upload-file'];
                $tmpName = $file['tmp_name'];
                $fileName = $file['name'];
                $file_module = new \file\module\File();
                $ext = $file_module->getFileExtension($fileName);

                // 上传的文件目录
                $uploadDirPath = STATIC_AVATARS;
                // 新文件名
                $newFileName = time() . '-' . \tool\module\Tool::uuid() .  '.' . $ext;
                $fullFilePath = $uploadDirPath . '/' . $newFileName;

                // 没有上传文件
                if (!isset($file)) {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '没有指定文件';
                    exit_output($this->return_json);
                }

                // 检测文件是否是通过HTTP POST方式上传上来
                if (!is_uploaded_file($tmpName)) {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '文件请求方法异常，不是POST';
                    exit_output($this->return_json);
                }

                // 检测文件是不是图片
                if (!$file_module->isImage($ext)) {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '上传的不是图片';
                    exit_output($this->return_json);
                }

                // 目录不存在则创建
                if (!file_exists($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777, true);
                }
                // 移动文件
                if (move_uploaded_file($tmpName, $fullFilePath)) {
                    // 返回一个id
                    // 做下图片更新交易处理
                    $result = $user_module->updateUserImage($user_name,$sPwd,$newFileName);
                    if ($result["success"])
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '获取成功';
                        $this->return_json['oRet'] = array(
                            'sFileName' => $newFileName
                        );
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '更新图片交易失败';
                    }
                    exit_output($this->return_json);
                } else {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '移动文件失败';
                    exit_output($this->return_json);
                }
            }
        }
        exit_output($this->return_json);
    }



    /**
     * 获取图片
     */
    public function getImage()
    {
        $img_path = securely_input('sImagePath');
        $uploadDirPath = STATIC_AVATARS;
        if (!isset($img_path))
        {
            $fullFilePath  = $uploadDirPath . '/' . 'default.png';
        }
        else
        {
            $fullFilePath = $uploadDirPath . '/' . $img_path;
            if (!file_exists($fullFilePath)) {
                $fullFilePath  = $uploadDirPath . '/' . 'default.png';
            }
        }
        $img = file_get_contents($fullFilePath);
        header("Content-type: image/jpg");
        echo $img;
    }


    /**
     * 获取token-openApi方式
     */
    public function openLoginByToken()
    {
        $sToken = quick_input('sToken');
        if (!isset($sToken))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $oRedis = \tool\module\Redis::connect();
            $sUserInfo = $oRedis->get($sToken);
            if (false !== $sUserInfo) {
                //有登录态，刷新登录态
                $arrUserInfo = json_decode($sUserInfo, true);
                // 存储session_id
                $user_module->saveLoginState($arrUserInfo['sId']);
                // 删除这个token
                $oRedis->delete($sToken);
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = 'SUCCESS';
                $this->return_json['oRet'] = $arrUserInfo;
            } else {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = 'Token失效，请重新登录';
            }
        }
        header('location:https://coderchain.cn');
    }

    public function openRegisterByToken()
    {
        $sToken = quick_input('sToken');
        if (!isset($sToken))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $oRedis = \tool\module\Redis::connect();
            $sUserInfo = $oRedis->get($sToken);
            if (false !== $sUserInfo) {
                //有登录态，刷新登录态
                $arrUserInfo = json_decode($sUserInfo, true);
                // 存储session_id
                $user_module->saveLoginState($arrUserInfo['address']);
                // 删除这个token
                $oRedis->delete($sToken);
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = 'SUCCESS';
                $this->return_json['oRet'] = $arrUserInfo;
            } else {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = 'Token失效，请重新登录';
            }
        }
        // 让子弹飞一会
        sleep(3);
        header('location:https://coderchain.cn');
    }

    /**
     * open api部分
     */

    public function openLogin()
    {
        quick_require(PATH_EXTEND . 'rsa.php');
        $user_name = securely_input('sUserName');
        $password = quick_input('sPassword');
        $key = quick_input('sEncryptedAESKey');
        $this->logger->writeLog(__FILE__, __LINE__, 'LP_INFO', "username => " . $user_name . "password => " . $password);
        if (!isset($user_name) || !isset($password) || !isset($key))
        {
            $this->return_json['iCode'] = -700;
            $this->return_json['sMsg'] = '参数不合法';
            exit_output($this->return_json);
        }
        $password = \EncryptUtil::rsa_aes_decrypt($password, $key);
        if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
        {
            $this->return_json['iCode'] = -700;
            $this->return_json['sMsg'] = '密码需要由8-20位的字母和数字组成';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->openLogin( $user_name, $password);
            if ($result)
            {
                $oRedis = \tool\module\Redis::connect();
                $sToken = \tool\module\Tool::uuid();
                $oRedis->set($sToken, json_encode($result));
                $this->return_json['iCode'] = 0;
                $this->return_json['sToken'] = $sToken;
            }
            else
            {
                $this->return_json['iCode'] = -800;
                $this->return_json['sMsg'] = '账号名或密码错误';
            }
        }
        exit_output($this->return_json);
    }
    

    
    public function openRegister()
    {
        quick_require(PATH_EXTEND . 'rsa.php');
        $user_name = securely_input('sUserName');
        $password = quick_input('sPassword');
        $key = quick_input('sEncryptedAESKey');
        $user_module = new \user\module\User();
        if (!isset($user_name) || !isset($password) || !isset($key))
        {
            $this->return_json['iCode'] = -700;
            $this->return_json['sMsg'] = '参数不合法';
            exit_output($this->return_json);
        }
        $password = \EncryptUtil::rsa_aes_decrypt($password, $key);
        if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
        {
            $this->return_json['iCode'] = -700;
            $this->return_json['sMsg'] = '密码需要由8-20位的字母和数字组成';
        }
        elseif (!$user_module->isUserNameUsable( $user_name))
        {
            $this->return_json['iCode'] = -801;
            $this->return_json['sMsg'] = '用户名已经存在，请选择其他用户名';
        }
        else
        {

            $result = $user_module->register( $user_name, $password);
            if ($result["success"])
            {
                $address = $result["data"]["address"];
                // 创建目录
                $file = new \file\module\File();
                $file->mkdir(STATIC_PROJECTS."/".$address);
                $oRedis = \tool\module\Redis::connect();
                $sToken = \tool\module\Tool::uuid();
                $oRedis->set($sToken, json_encode($result["data"]));
                $this->return_json['iCode'] = 0;
                $this->return_json['sToken'] = $sToken;
            }
            else
            {
                $this->return_json['iCode'] = -900;
                if ($result["msg"])
                {
                    $this->return_json['sMsg'] = $result["msg"];
                }
                else
                {
                    $this->return_json['sMsg'] = '注册失败，网络繁忙，请重新再试';
                }

            }
        }
        exit_output($this->return_json);
    }
    
    public function getAllUser()
    {
        $user_module = new \user\module\User();
        $result = $user_module->getAllUser();

        if ($result["success"])
        {
            $arrMerge = $result["data"];
        }
        else
        {
            $arrMerge = array();
        }
        $this->return_json['oRet'] = $arrMerge;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }

    public function getTransferList()
    {
        $user_module = new \user\module\User();
        $result = $user_module->getTransferList();
        if ($result["success"])
        {
            $arrMerge = $result["data"];
        }
        else
        {
            $arrMerge = array();
        }
        $this->return_json['oRet'] = $arrMerge;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }

    public function getTransferDetail()
    {
        $user_module = new \user\module\User();
        $hash = securely_input("hash");
        if (!isset($hash))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $result = $user_module->getTransferDetail($hash);
            if ($result["success"])
            {
                $this->return_json['oRet'] = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取数据完成';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = $result["msg"] ? $result["msg"] : '获取交易详情失败';
            }
        }
        exit_output($this->return_json);
    }

    public function getUserTxDetail()
    {
        $user_module = new \user\module\User();
        $hash = securely_input("hash");
        if (!isset($hash))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $result = $user_module->getUserTxDetail($hash);
            if ($result["success"])
            {
                $this->return_json['oRet'] = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取数据完成';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = $result["msg"] ? $result["msg"] : '获取交易详情失败';
            }
        }
        exit_output($this->return_json);
    }

    public function getUserTx()
    {
        $user_module = new \user\module\User();
        $userName = securely_input("userName");
        $type = securely_input("type");
        if (!isset($userName) || !isset($type))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $result = $user_module->getUserTx($userName,$type);
            if ($result["success"])
            {
                $this->return_json['oRet'] = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取数据完成';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = $result["msg"] ? $result["msg"] : '获取交易失败';
            }
        }
        exit_output($this->return_json);
    }
    
    public function getUserPriKey()
    {
        $user_name = securely_input('sUserName');
        $password = quick_input('sPassword');
        if (!isset($user_name) || !isset($password))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';

        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->getUserPriKey($user_name, $password);
            if ($result["success"])
            {
                $this->return_json['oRet'] = $result["data"];
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取数据完成';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = $result["msg"] ? $result["msg"] : '获取数据失败';
            }
        }
        exit_output($this->return_json);
    }
    
    
    public function getUserRank()
    {
        
    }
    
    public function getUserInfo()
    {
        
    }
    
}