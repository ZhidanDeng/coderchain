<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/1
 * @Time: 21:07
 */

namespace project\controller;


class Project
{
    private $log_path = "/ex/project/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'project');

    private $m_sDomain = "coderchain.cn";

    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }

    /**
     * 创建项目
     */
    public function create()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sProjectName = securely_input('sProjectName');
            $sDescription = securely_input('sDescription',"");
            $sCategoryName = securely_input('sCategoryName',"");
            $password = securely_input("password");
            if (!isset($sDescription)) {
                $sDescription = '';
            }
            if (!isset($sCategoryName)) {
                $sCategoryName = '';
            }
            if (!isset($sProjectName))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            else
            {
                $project_module = new \project\module\Project();
                // 获取地址
                $address = $user_module->getLoginUserId();
                // 判断项目是否存在
                if ($project_module->isProjectExistByAddress($address,$sProjectName))
                {
                    $this->return_json['retCode'] = -101;
                    $this->return_json['retMsg'] = '项目已经存在';
                    exit_output($this->return_json);
                }
                // 获取用户信息
                $user_info = $user_module->getUserInfoByAddress($address);
                if ($user_info["success"])
                {
                    // 用来判断文件是否上传失败
                    $bMoveFileError = false;
                    $data = $user_info["data"];
                    $file_module = new \file\module\File();
                    if (!$user_module->checkUser($data["userName"], $password))
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '用户密码错误';
                        exit_output($this->return_json);
                    }
                    // 新建项目路径
                    $sProjectPath = STATIC_PROJECTS . '/' . $address . '/' . $sProjectName;
                    // 判断项目名是否合法
                    if (!$project_module->isValidFileName($sProjectName)) {

                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目名不合法';
                        exit_output($this->return_json);
                    }
                    // 判断项目是否有初始化文件
                    if (isset($_FILES['upload-file'])) {
                        // 文件信息获取
                        $file = $_FILES['upload-file'];
                        $tmpName =  $file['tmp_name'];
                        $fileName = $file['name'];
                        // 临时文件目录
                        $uploadDirPath = STATIC_PROJECTS . '/' . $address;
                        // 新文件的文件路径
                        $newFileName = $fileName;
                        $fullFilePath = $uploadDirPath . '/' . $newFileName;
                        // 临时文件目录不存在则创建（这也是用户项目的根目录）
                        if (!file_exists($uploadDirPath)) {
                            mkdir($uploadDirPath, 0777, true);
                            chmod($uploadDirPath, 0777);
                        }
                        // 检测文件是否是通过HTTP POST方式上传上来
                        if (!is_uploaded_file($tmpName)) {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '文件上传格式非法';
                            exit_output($this->return_json);
                        }
                        // 移动文件，将用户上传的文件在服务器上保存，失败则创建一个空项目
                        if (move_uploaded_file($tmpName, $fullFilePath)) {
                            // linux环境
                            // 获取文件后缀
                            $ext = $file_module->getFileExtension($fileName);
                            // 判断是不是压缩文件
                            if ($ext == 'zip' || $ext == 'rar')
                            {
                                // 解压处理
                                $newFileName = $sProjectName;
                                // 设置编码格式
                                $locale = 'zh_CN.UTF-8';
                                setlocale(LC_ALL, $locale);
                                putenv('LC_ALL=' . $locale);
                                // 磁盘文件的路径，目的是直接解压到指定的项目目录文件下
                                $distFilePath = $uploadDirPath . '/' .  $newFileName;
                                $sCmd = '';
                                if ($ext == 'zip')
                                {
                                    $sCmd = "unzip -O CP936 -d {$distFilePath} {$fullFilePath}";
                                }
                                else if ($ext == 'rar')
                                {
                                    // rar解压必须要先创建目录，不然会解压失败
                                    if (!file_exists($distFilePath)) {
                                        mkdir($distFilePath, 0777, true);
                                        chmod($distFilePath, 0777);
                                    }
                                    $sCmd = "unrar x {$fullFilePath} {$distFilePath}";
                                }
                                // 执行解压命令
                                exec($sCmd);
                                // 对用户的这个文件夹进行遍历处理，都转化成UTF-8
                                $file_module->convertDirFilesEncoding($distFilePath);
                                // 得到绝对路径
                                $fullFilePath = $uploadDirPath . '/' .  $newFileName;
                                // 删除zip（待定）
                                $file_module->unlink($uploadDirPath."/".$fileName);

                                // IPFS创建新项目
                                $oRet = $project_module->createProject( $address, $sProjectName,$fullFilePath);
                                if ($oRet)
                                {
                                    // 错误才输出
                                    if (is_numeric($oRet) && $oRet == 1) {
                                        $this->return_json['retCode'] = 100;
                                        $this->return_json['retMsg'] = '项目名已经存在，请填入其他项目名';
                                        exit_output($this->return_json);
                                    }
                                }
                                else
                                {
                                    $this->return_json['retCode'] = 100;
                                    $this->return_json['retMsg'] = '上传项目失败';
                                    exit_output($this->return_json);
                                }
                            }
                            else
                            {
                                // 普通文件，要移动出来，先创建项目目录文件
                                if (!file_exists($sProjectPath)) {
                                    mkdir($sProjectPath, 0777, true);
                                    chmod($sProjectPath, 0777);
                                }
                                // 调用系统命令移动单个文件，改为复制后删除
                                $sMoveFilePath = $sProjectPath . '/' . $newFileName;
//                                $sCmd = "mv {$fullFilePath} {$sMoveFilePath}";
//                                exec($sCmd);
                                copy($fullFilePath, $sMoveFilePath);
                                $file_module->unlink($fullFilePath);
                                $oRet = $project_module->createProject( $address, $sProjectName);
                                if ($oRet)
                                {
                                    // 错误才输出
                                    if (is_numeric($oRet) && $oRet == 1) {
                                        $this->return_json['retCode'] = 100;
                                        $this->return_json['retMsg'] = '项目名已经存在，请填入其他项目名';
                                        exit_output($this->return_json);
                                    }
                                }
                                else
                                {
                                    $this->return_json['retCode'] = 100;
                                    $this->return_json['retMsg'] = '上传项目失败';
                                    exit_output($this->return_json);
                                }
                                // 这里判断是不是图片
                                if ($file_module->isResourceType($ext)) {
                                    // 根路径
                                    $sPath = '';
                                    $file_module->saveResource($address, $sProjectName, $sPath, $sMoveFilePath, $fileName);
                                } else {
                                    $sPath = $fileName;
                                    $sFileContent = file_get_contents($sMoveFilePath);
                                    // 这里对文件进行编码转换
                                    $sFileContent = $file_module->convertStringEncoding($sFileContent);
                                    // 这里的项目名必须要encode一下
                                    $file_module->saveFile($address, $sProjectName, $sPath, $sFileContent);
                                }
                            }
                        }
                        else
                        {
                            $bMoveFileError = true;
                            // 向IPFS发请求创建一个空项目
                            $oRet = $project_module->createProject( $address, $sProjectName);
                            if ($oRet)
                            {

                                // 错误才输出
                                if (is_numeric($oRet) && $oRet == 1) {
                                    $this->return_json['retCode'] = 100;
                                    $this->return_json['retMsg'] = '项目名已经存在，请填入其他项目名';
                                    exit_output($this->return_json);
                                }
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '上传项目失败';
                                exit_output($this->return_json);
                            }
                        }
                    }
                    else
                    {
                        // 向IPFS发请求创建一个空项目
                        $oRet = $project_module->createProject( $address, $sProjectName);
                        if ($oRet)
                        {
                            if (is_numeric($oRet) && $oRet == 1) {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '项目名已经存在，请填入其他项目名';
                                exit_output($this->return_json);
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '上传项目失败';
                            exit_output($this->return_json);
                        }
                    }

                    // 输出信息处理
                    if ($bMoveFileError) {
                        $sMsg = '项目创建成功，但是没有成功导入文件';
                    } else {
                        $sMsg = '创建项目成功';
                    }

                    // 确保项目目录存在
                    if (!file_exists($sProjectPath)) {
                        mkdir($sProjectPath, 0777, true);
                        chmod($sProjectPath, 0777);
                    }

                    // 链上创建项目
                    $result = $project_module->createProjectInNuls($data["userName"],$password, $sDescription, $sProjectName,$sCategoryName);

                    if ($result["success"])
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = $sMsg;
                    }
                    else
                    {
                        // 回滚ipfs
                        $file_module->rmdir(STATIC_PROJECTS . '/' . $address . '/' . $sProjectName);
                        $project_module->deleteProject($address,$sProjectName);
                        $this->return_json['retCode'] = 100;
                        if ($result["msg"])
                        {
                            $this->return_json['retMsg'] = $result["msg"];
                        }
                        else
                        {
                            $this->return_json['retMsg'] = $sMsg;
                        }
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    if ($user_info["msg"])
                    {
                        $this->return_json['retMsg'] = $user_info["msg"];
                    }
                    else
                    {
                        $this->return_json['retMsg'] = '用户不存在';
                    }
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 获取项目的所有目录
     */
    public function getDir()
    {
        $sUserName = securely_input('sUserName');
        $sProjectName = securely_input('sProjectName');

        if (!isset($sUserName) || !isset($sProjectName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->getUserInfoByName($sUserName);
            if ($result["success"])
            {
                $data = $result["data"];
                $sProjectPath = STATIC_PROJECTS . '/' . $data["address"] . '/' . $sProjectName;
                if(is_dir($sProjectPath))
                {
                    $file_module = new \file\module\File();
                    $dir = array();
                    $dir['dir'][] = '/';
                    $file_module->scan($sProjectPath, '/',$dir);
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '获取目录成功';
                    $this->return_json['oRet'] = $dir;
                }
                else
                {
                    $project_module = new \project\module\Project();
                    // 先判断项目是否存在
                    $oRet = $project_module->getProjectDetail($data["address"], $sProjectName, '');
                    if ($oRet) {
                        $dir = array();
                        $dir['dir'][] = '/';
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '获取目录成功';
                        $this->return_json['oRet'] = $dir;
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目用户不存在';
            }
        }
        exit_output($this->return_json);
    }
    /**
     * 获取项目详情
     */
    public function getProjectDetail()
    {
        $sUserName = securely_input('sUserName');
        $sProjectName = securely_input('sProjectName');
        $sPath = securely_input('sPath','');
        if (!isset($sUserName) || !isset($sProjectName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->getUserInfoByName($sUserName);
            if ($result["success"]) {
                $data = $result["data"];
                $project_module = new \project\module\Project();
                if (!$project_module->isValidFileName($sProjectName)) {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目名不合法';
                    exit_output($this->return_json);
                }
                $oRet = $project_module->getProjectDetail($data["address"], $sProjectName, $sPath);
                // 登陆逻辑
                if ($oRet) {
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '拉取项目成功';
                    $this->return_json['oRet'] = $oRet;
                }
                else
                {
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '拉取项目成功';
                    $this->return_json['oRet'] = array();
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目用户不存在';
            }
        }
        exit_output($this->return_json);
    }

    public function getChainInfo()
    {
        $address = securely_input("address");
        $projectName = securely_input("projectName");
        $project_module = new \project\module\Project();
        $result = $project_module->getProjectStatus($address,$projectName);
        if ($result)
        {
            $this->return_json['retCode'] = 0;
            $this->return_json['retMsg'] = '获取项目链上信息成功';
            $this->return_json['oRet'] = $result;
        }
        else
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '获取项目链上信息失败';
        }
        exit_output($this->return_json);
    }

    public function get()
    {
        $sUserName = securely_input('sUserName');
        $user_module = new \user\module\User();
        $result = $user_module->getUserInfoByName($sUserName);
        if ($result["success"])
        {
            $user_info = $result["data"];
            $address = $user_info['address'];
            $project_module = new \project\module\Project();
            // 获取IPFS的项目列表
            $oRet =  $project_module->getProjectList($address);
            if (count($oRet)>0)
            {
                $result = $project_module->getUserProject($address,$oRet);
                $hash_info = array();
                foreach ($oRet as $project)
                {
                    $hash_info[$project["name"]] = $project;
                }
                if ($result["success"])
                {
                    $project_result = array();
                    if ($result["data"])
                    {
                        $data = $result["data"];
                        $user_name = $user_info["userName"];
                        $user_avatar = $user_info["avatar"];
                        foreach ($data as $item)
                        {
                            $project_result[] = array_merge(array(
                                'sUserName'=>$user_name,
                                "sAvatar"=>$user_avatar,
                                "sDisplayName"=>$user_name,
                                'sUserId'=>$address,
                                "createAt"=>$item["createTime"],
                                "sCategoryName"=>$item["projectType"],
                                "sDescription" =>$item["description"],
                                "sProjectName"=>$item["projectName"],
                                "sId" =>$user_name."/".$item["projectName"]
                            ),$hash_info[$item["projectName"]]);
                        }
                    }
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '获取用户的所有项目成功';
                    $this->return_json['oRet'] = $project_result;
                }
                else
                {
                    if ($result["msg"])
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = $result["msg"];
                    }
                    else
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '获取用户的所有项目成功';
                        $this->return_json['oRet'] = array();
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取用户的所有项目成功';
                $this->return_json['oRet'] = array();
            }
        }
        else
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '用户不存在';
        }
        exit_output($this->return_json);
    }

    /**
     * 创建目录
     */
    public function createDir()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sAddress = $user_module->getLoginUserId();
            $sProjectName = securely_input('sProjectName');
            $sPath = securely_input('sPath');
            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($sProjectName)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
                exit_output($this->return_json);
            }
            if (!isset($sPath) || trim($sPath) == "")
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目录格式不合法';
                exit_output($this->return_json);
            }
            // 向IPFS发请求创建项目
            $oRet = $project_module->createDir($sAddress, $sProjectName, $sPath);
            // 这里再创建实际目录
//            $sProjectName = urldecode($sProjectName);
            $sFullDirPath = STATIC_PROJECTS . '/' . $sAddress . '/' . $sProjectName . '/' . $sPath;
            $file_module = new \file\module\File();
            // 创建新的项目文件目录
            $file_module->mkdir($sFullDirPath);
            // 登陆逻辑
            if ($oRet) {
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '创建文件夹成功';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '创建文件夹失败';
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 根据用户名称和项目名称来获取项目的token
     */
    public function getSupportCountByName()
    {
        $sProjectName = securely_input('sProjectName');
        $sUserName = securely_input('sUserName');
        $project_module = new \project\module\Project();
        $user_module = new \user\module\User();
        if (!$project_module->isValidFileName($sProjectName)) {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不合法';
        }
        elseif (!isset($sUserName))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '用户名不合法';
        }
        elseif ($user_module->isUserNameUsable($sUserName))
        {
            // 用户名可用，证明用户不存在
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '用户不存在';
        }
        elseif (!$project_module->isProjectExist($sUserName,$sProjectName))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目不存在';
        }
        else
        {
            # 获取项目投票
            $result = $project_module->getSupportCount($sUserName, $sProjectName);

            if ($result["success"])
            {

                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取投票数量成功';
                $this->return_json['oRet'] = $result["data"];
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
                    $this->return_json['retMsg'] = '获取投票数量失败';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 投票
     */
    public function voteByName()
    {
        $project_module = new \project\module\Project();
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sProjectName = securely_input('sProjectName');
            $sUserName = securely_input('sUserName');
            $iSupportCount = securely_input('iSupportCount');
            $password = securely_input("password");
            if (!isset($sProjectName)|| !isset($sUserName) || !isset($iSupportCount) || !isset($password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
                exit_output($this->return_json);
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            elseif (!$project_module->isValidFileName($sProjectName)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
            }
            elseif ($user_module->isUserNameUsable($sUserName))
            {
                // 用户名可用，证明用户不存在
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目用户不存在';
            }
            elseif (!$project_module->isProjectExist($sUserName,$sProjectName))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目不存在';
            }
            else
            {
                $sAddress = $user_module->getLoginUserId();
                $result = $user_module->getUserInfoByAddress($sAddress);
                if ($result["success"])
                {
                    $from_user_name = $result["data"]["userName"];
                    if ($from_user_name != $sUserName)
                    {
                        if ($user_module->checkUser($from_user_name,$password))
                        {
                            $result = $project_module->vote($from_user_name,$password,$sUserName,$sProjectName,$iSupportCount);
                            if ($result["success"])
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '投票成功';
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
                                    $this->return_json['retMsg'] = '项目投票失败，请检查余额或联系管理员处理';
                                }
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户密码错误';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '不能给自己的项目投票';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }
    /**
     * 根据用户名称和项目名称来下载项目
     */
    public function download()
    {
        $project_module = new \project\module\Project();
        $user_module = new \user\module\User();
        $sProjectName = securely_input('sProjectName');
        $sUserName = securely_input('sUserName');
        if (!isset($sProjectName) || !isset($sUserName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        elseif (!$project_module->isValidFileName($sProjectName)) {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不合法';
        }
        elseif ($user_module->isUserNameUsable($sUserName))
        {
            // 用户名可用，证明用户不存在
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目用户不存在';
        }
        elseif (!$project_module->isProjectExist($sUserName,$sProjectName))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目不存在';
        }
        else
        {
            $result = $user_module->getUserInfoByName($sUserName);
            if ($result["success"])
            {
                $address = $result["data"]["address"];
                // 上传文件内容
//                $sProjectName = urldecode($sProjectName);
                $sProjectPath = STATIC_PROJECTS . '/' . $address . '/' . $sProjectName;
                // 压缩文件夹
                $sTmpPath = STATIC_TMP . '/' . $sProjectName . '.zip';
                $sCmd = "cd {$sProjectPath} && zip -r {$sTmpPath} ./*";
                exec($sCmd);
                // 返回zip文件
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($sTmpPath));
                // 中文编码
                $sProjectName = iconv('utf-8', 'gb2312', $sProjectName);
                header("Content-Disposition: attachment; filename=\"" . $sProjectName . '.zip' . "\"");
                readfile($sTmpPath);
                $file_module = new \file\module\File();
                // 删除压缩文件夹
                $file_module ->unlink($sTmpPath);
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目用户不存在';
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 根据用户名称和项目名称来获取项目的详情
     */
    public function getProjectInfo()
    {
        $project_module = new \project\module\Project();
        $sProjectName = securely_input('sProjectName');
        $sUserName = securely_input('sUserName');
        if (!isset($sProjectName) || !isset($sUserName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        elseif (!$project_module->isValidFileName($sProjectName)) {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不合法';
        }
        elseif (!$project_module->isProjectExist($sUserName,$sProjectName))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目不存在';
        }
        else
        {
            $user_module = new \user\module\User();
            $result = $user_module->getUserInfoByName($sUserName);
            if ($result["success"]) {
                $user_info = $result["data"];
                $result = $project_module->getProjectInfo($sUserName,$sProjectName);
                if ($result["success"])
                {
                    $data = $result["data"];
                    $user_name = $sUserName;
                    $user_avatar = $user_info["avatar"];
                    $address = $user_info["address"];
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '获取项目信息';
                    $this->return_json['oRet'] = array(
                        'sUserName'=>$user_name,
                        "sAvatar"=>$user_avatar,
                        "sDisplayName"=>$user_name,
                        'sUserId'=>$address,
                        "createAt"=>$data["createTime"],
                        "sCategoryName"=>$data["projectType"],
                        "sDescription" =>$data["description"],
                        "sProjectName"=>$data["projectName"],
                        "sId" =>$user_name."/".$data["projectName"]
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
                        $this->return_json['retMsg'] = '项目信息获取失败';
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户不存在';
            }
        }
        exit_output($this->return_json);
    }

    public function updateProjectInfo()
    {
        $project_module = new \project\module\Project();
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sProjectName = securely_input('sProjectName');
            $sCategoryName = securely_input('sCategoryName');
            $sDescription = securely_input('sDescription');
            $password = securely_input("password");
            if (  !isset($sCategoryName ) || !isset($sDescription) || !isset($password) || !isset($sProjectName))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            elseif (!$project_module->isValidFileName($sProjectName)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
            }
            else
            {
                $address= $user_module->getLoginUserId();
                $result = $user_module->getUserInfoByAddress($address);
                if ($result["success"]) {
                    $user_info = $result["data"];
                    $bRet = $project_module->updateProject($user_info["userName"], $password, $sDescription,$sProjectName,$sCategoryName);
                    if ($bRet["success"]) {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '更新项目信息成功';
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        if ($bRet["msg"])
                        {
                            $this->return_json['retMsg'] = $bRet["msg"];
                        }
                        else
                        {
                            $this->return_json['retMsg'] = '更新失败';
                        }
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 删除项目
     */
    public function delete()
    {
        $user_module = new \user\module\User();
        $project_module = new \project\module\Project();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $sProjectName = securely_input('sProjectName');
            $password = securely_input("password");
            if (!isset($sProjectName) || !isset($password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            elseif (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/', $password))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '密码需要由8-20位的字母和数字组成';
            }
            elseif (!$project_module->isValidFileName($sProjectName)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
                exit_output($this->return_json);
            }
            else
            {
                $address= $user_module->getLoginUserId();
                $result = $user_module->getUserInfoByAddress($address);
                if ($result["success"]) {
                    $user_info = $result["data"];
                    if (!$user_module->checkUser($user_info["userName"], $password))
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '用户密码错误';
                        exit_output($this->return_json);
                    }

                    // 删除服务器文件
                    $file_module = new \file\module\File();
                    $sFullDirPath = STATIC_PROJECTS . '/' . $address . '/' . $sProjectName;
                    $bRet = $file_module->rmdir($sFullDirPath);
                    if (!$bRet) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '删除项目失败_FILE';
                        exit_output($this->return_json);
                    }
                    # 删除IPFS的项目
                    $oRet =$project_module->deleteProject($address, $sProjectName);
                    if (!$oRet) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '删除项目失败_IPFS';
                        exit_output($this->return_json);
                    }

                    // NULS删除项目
                    $result = $project_module->deleteProjectInNuls($user_info["userName"], $password, $sProjectName);
                    if ($result["success"])
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '删除项目成功';
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = $result["msg"] ? $result["msg"] : '删除项目失败';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }

    public function getAllProject()
    {
        $project_module = new \project\module\Project();
        $arrProject = $project_module->getAllProject();
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

    public function getAllRank()
    {
        $project_module = new \project\module\Project();
        $arrProject = $project_module->getAllProjectSupport();
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
                    "sProjectName"=>$item["projectName"],
                    "sId" =>$item["userName"]."/".$item["projectName"],
                    "iSupportToken"=>$item["supportCount"]
                );
            }
        }
        $this->return_json['oRet'] = $arrMerge;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }

    public function getSupportDetailList()
    {
        $project_module = new \project\module\Project();
        $arrProject = $project_module->getSupportDetailList();
        $this->return_json['oRet'] = $arrProject;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取数据完成';
        exit_output($this->return_json);
    }

    public function getSupportDetail()
    {
        $project_module = new \project\module\Project();
        $hash = securely_input("hash");
        if (!isset($hash))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $result = $project_module->getSupportDetail($hash);
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


    /**
     * 从静态json获取项目列表
     */
    public function getAll()
    {
        $sStaticFilePath = STATIC_FILES . '/project-list.json';
        $sJsonText =  file_get_contents($sStaticFilePath);
        $arrRet = json_decode($sJsonText,true);
        if (!$sJsonText) {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '没有项目';
        }
        else
        {
            $this->return_json['retCode'] = 0;
            $this->return_json['retMsg'] = '获取全部项目成功';
            $this->return_json['oRet'] =$arrRet;
        }
        exit_output($this->return_json);
    }
    /**
     * 从静态json获取项目排行列表
     */
    public function getRank()
    {
        // 读取文件
        $sStaticFilePath = STATIC_FILES .'/project-rank-list.json';
        $sJsonText =  file_get_contents($sStaticFilePath);
        $arrRet = json_decode($sJsonText, true);
        // 取前二十条
        if (!$sJsonText) {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '没有项目';
        }
        else
        {
            $arrRet = array_slice($arrRet, 0, 20);
            $this->return_json['retCode'] = 0;
            $this->return_json['retMsg'] = '获取项目排行成功';
            $this->return_json['oRet'] =$arrRet;
        }
        exit_output($this->return_json);
    }

    public function generateStaticProjectRank()
    {
        $project_module = new \project\module\Project();
        // 获取所有地址，遍历
        $arrProject = $project_module->getAllChainProject();
        $arrRet = array();
        // sWalletAddress, $arrProjectList

        foreach ($arrProject as $oProject) {
            $sWalletAddress = $oProject['sWalletAddress'];
            $arrUserProject = $oProject['arrProject'];
            $user = new \user\module\User();
            // concat
            $arrUser = $user->getUser(array('sWalletAddress'),array($sWalletAddress));

            $oUser = $arrUser;
            $sUserId = $oUser['sId'];
            $sAvatar = $oUser['sAvatar'];
            $sUserName = $oUser['sUserName'];
            $sDisplayName = $oUser['sDisplayName'];
            $arrUserProject = $project_module->concatProjectListDetailFromDb( $sUserId, $arrUserProject);
            // 再次拼接

            foreach ($arrUserProject as $oUserProject) {
                $oUserProject['sAvatar'] = $sAvatar;
                $oUserProject['sUserId'] = $sUserId;
                $oUserProject['sUserName'] = $sUserName;
                $oUserProject['sDisplayName'] = $sDisplayName;

                // 这里再循环获取项目的Token支持数
                $oRet = $project_module->getSupportCount($oUserProject['name'], $sWalletAddress);
                $iToken = 0;

                $oRet = json_decode($oRet, true);
                if ($oRet['iCode'] != 0) {
                    $this->logger->writeLog(__FILE__, __LINE__, 'LP_INFO', "拉取项目投票失败: projectName => " . $oUserProject['name'] . "sAddress => " . $sWalletAddress . ' 拉取列表结果：' . json_encode($oRet));
                    $iToken = '---';
                }

                $iToken = $oRet['sMsg'];
                $oUserProject['iSupportToken'] = $iToken;

                $arrRet[] = $oUserProject;
            }
        }

        $sStaticFilePath = STATIC_FILES. '/project-rank-list.json';

        $sJsonText =  json_encode($arrRet);
        // 把这个内容写进去
        file_put_contents($sStaticFilePath, $sJsonText);
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '写入文件成功';
        $this->return_json['oRet'] = $sJsonText;
        exit_output($this->return_json);
    }

    public function generateStaticFile()
    {
        // 获取所有地址，遍历
        $project_module = new \project\module\Project();
        $arrProject = $project_module->getAllChainProject();
        $arrRet = array();
        // sWalletAddress, $arrProjectList
        foreach ($arrProject as $oProject) {
            $sWalletAddress = $oProject['sWalletAddress'];
            $arrUserProject = $oProject['arrProject'];
            $user_module = new \user\module\User();
            // concat
            $arrUser = $user_module->getUser( array('sWalletAddress'),array($sWalletAddress));

            $oUser = $arrUser[0];
            $sUserId = $oUser['sId'];
            $sAvatar = $oUser['sAvatar'];
            $sUserName = $oUser['sUserName'];
            $sDisplayName = $oUser['sDisplayName'];
            $arrUserProject = $project_module->concatProjectListDetailFromDb( $sUserId, $arrUserProject);
            // 再次拼接

            foreach ($arrUserProject as $oUserProject) {
                $oUserProject['sAvatar'] = $sAvatar;
                $oUserProject['sUserId'] = $sUserId;
                $oUserProject['sUserName'] = $sUserName;
                $oUserProject['sDisplayName'] = $sDisplayName;
                $arrRet[] = $oUserProject;
            }
        }

        $sStaticFilePath = STATIC_FILES.'/project-list.json';
        $sJsonText =  json_encode($arrRet);
        // 把这个内容写进去
        file_put_contents($sStaticFilePath, $sJsonText);
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '写入文件成功';
        $this->return_json['oRet'] = $sJsonText;
        exit_output($this->return_json);
    }
}