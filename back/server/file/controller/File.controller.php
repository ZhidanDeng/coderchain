<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 22:36
 */

namespace file\controller;




class File
{
    private $log_path = "/fileupload/";
    
    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'file');

    private $m_sDomain = "coderchain.cn";

    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }
    
    public function upload()
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
            $project_name = securely_input('sProjectName');
            $path = securely_input('sPath');
            $path = trim($path);
            $file = $_FILES['upload-file'];
            $tmpName = $file['tmp_name'];
            $fileName = $file['name'];
            if (!isset($file)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '没有指定的文件，请确保文件存在且大小控制在30M以下';
            }
            elseif (!is_uploaded_file($tmpName))
            {
                // 检测文件是否是通过HTTP POST方式上传上来
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '文件上传格式非法';
            }
            elseif (!$project_module->isValidFileName($project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
            }
            else
            {
                $file_module = new \file\module\File();
                $address = $user_module->getLoginUserId();
                if ($address)
                {
                    // 临时文件目录
                    $uploadDirPath = STATIC_PROJECTS. '/' . $address. '/' . $project_name;
                    if ($path) {
                        $uploadDirPath .= '/' . $path;
                    }
                    // 新文件的文件路径
                    $newFileName = $fileName;
                    $fullFilePath = $uploadDirPath . '/' . $newFileName;
                    // 目录不存在则创建
                    $file_module->mkdir( $uploadDirPath);
                    // 移动文件
                    if (move_uploaded_file($tmpName, $fullFilePath)) {
                        // 判断资源的后缀
                        $ext = $file_module->getFileExtension($fileName);
                        if ($file_module->isResourceType($ext))
                        {
                            $oRet = $file_module->saveResource($address, $project_name, $path, $fullFilePath, $fileName);
                        }
                        else
                        {
                            // 处理文件路径
                            if ($path)
                            {
                                $path = $path . '/' . $fileName;
                            } else {
                                $path = $fileName;
                            }
                            $sFileContent = file_get_contents($fullFilePath);
                            $sFileContent = $file_module->convertStringEncoding($sFileContent);
                            # 上传文件内容
                            $oRet = $file_module->saveFile($address, $project_name, $path, $sFileContent);
                        }
                        if ($oRet)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '上传文件成功';
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '上传文件失败，请重新再试';

                        }
                    }
                    else
                    {
                        unlink($fullFilePath);
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '上传文件失败，请重新再试';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = -10;
                    $this->return_json['retMsg'] = '用户未登录no_address';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 获取文件内容
     * @throws \Exception
     */
    public function getContent()
    {
        $sHash = securely_input('sHash');
        // 向Node发请求创建项目
        $file_module = new \file\module\File();
        $oRet = $file_module->getFileContent($sHash);
        // 编码文件内容
        $oRet = rawurlencode($oRet);
        $oData = array();
        $oData['sMsg'] = $oRet;
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '获取内容成功';
        $this->return_json['oRet'] = $oData;
        exit_output($this->return_json);
    }

    public function updateContent()
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
            $sPath = securely_input('sPath');
            $sPath = trim($sPath);
            $sData = securely_input('sData',"");
            $sData = trim($sData);
            if(!isset($sProjectName) || !isset($sPath))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '参数格式不合法';
            }
            elseif (!$project_module->isValidFileName($sProjectName))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
            }
            else
            {
                $address = $user_module->getLoginUserId();
                if ($address)
                {
                    $file_module = new \file\module\File();
                    $oRet = $file_module->saveFile($address, $sProjectName, $sPath, $sData);
                    if ($oRet)
                    {
                        // 修改本地文件内容
                        $uploadDirPath = STATIC_PROJECTS . '/' . $address . '/' . $sProjectName;
                        if ($sPath) {
                            $uploadDirPath .= '/' . $sPath;
                        }
                        $bRet = $file_module->write($uploadDirPath, $sData);
                        if (!$bRet)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '修改文件内容完成，但是文件系统没有写入';
                        }
                        else
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '修改文件内容完成';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '上传文件失败，请重新再试';
                    }

                }
                else
                {
                    $this->return_json['retCode'] = -10;
                    $this->return_json['retMsg'] = '未登录no_address';
                }
            }
        }
        exit_output($this->return_json);
    }

    public function delete()
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
            if ($address)
            {
                $sProjectName = securely_input('sProjectName');
                $sPath = securely_input('sPath');
                $sPath = trim($sPath);
                $project_module = new \project\module\Project();
                $file_module = new \file\module\File();
                if (!isset($sProjectName) || !isset($sPath))
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '参数不合理';
                }
                elseif(!$project_module->isValidFileName($sProjectName))
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目名不合法';
                }
                else
                {
                    // 删除文件或者目录
                    $oRet = $file_module->deleteFile($address, $sProjectName, $sPath);
                    if ($oRet) {
                        // 删除目录/文件
                        $uploadDirPath = STATIC_PROJECTS . '/' . $address . '/' . $sProjectName;
                        if ($sPath) {
                            $uploadDirPath .= '/' . $sPath;
                        }
                        if (is_dir($uploadDirPath)) {
                            $bRet = $file_module->rmdir($uploadDirPath);
                        } else {
                            $bRet = $file_module->unlink($uploadDirPath);
                        }
                        if ($bRet)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '删除文件成功';
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '链上删除文件完成，但是文件系统没有删除';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '删除文件失败';
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = -10;
                $this->return_json['retMsg'] = '未登录';
            }
        }
        exit_output($this->return_json);
    }
}