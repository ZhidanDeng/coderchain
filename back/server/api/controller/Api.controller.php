<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/18
 * @Time: 14:43
 */

namespace api\controller;


class Api
{
    private $log_path = "/ex/api/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'api');

    private $user_address = null;
    private $user_id = null;

    public function __construct()
    {
        ini_set("max_execution_time", 0);
        ini_set('memory_limit', '3072M');
        ignore_user_abort(TRUE);


        $this->logger = new \log\module\Logger($this->log_path);

        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $this->user_id = $user_module->getLoginUserId();
        if ($this->user_id)
        {
            $this->user_address = $user_module->getUserAddress($this->user_id);
        }
        else
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
    }
    /**
     * 创建项目并同步
     */
    public function createAndSynchronizeRepo()
    {
        $project_name = securely_input("projectName");
        $project_description = securely_input("projectDescription","");
        $project_category = securely_input("projectCategory");

        $repo_type = securely_input("repoType", 0);
        $address = securely_input('repoAddress');
        $repo_token = securely_input('repoToken');
        // 代码分支
        $ref = securely_input("ref");
        // 项目ID，gitlab
        $repertory_project_id = securely_input("projectID");
        // 仓库所属空间/用户名，github/码云
        $owner = securely_input("owner");
        // 仓库名，github/码云
        $repo = securely_input("repoName");
        $root_dir = "/";
        $tool = new \tool\module\Tool();
        if (!isset($project_name))
        {
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "项目名格式非法";
        }
        elseif (!isset($project_category))
        {
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "项目类别格式非法";
        }
        elseif(! in_array($repo_type, array(
            0,
            1,
            2
        )))
        {
            // 仓库格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "仓库格式非法";
        }
        elseif(! $tool->getHttpCodeForSwagger($address))
        {
            // 地址格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "地址格式非法";

        }
        elseif(empty($repo_token))
        {


            // 账号令牌格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "令牌格式非法";

        }
        elseif(empty($ref))
        {
            // 分支格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "分支格式非法";
        }
        else
        {
            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目录名不合法';
                exit_output($this->return_json);
            }

            $repository_type = $repo_type;
            if($repository_type == 0)
            {
                if(! preg_match('/^[0-9]{1,11}$/', $repertory_project_id))
                {
                    // 项目仓库projectID格式非法
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库projectID格式非法";
                    exit_output($this->return_json);
                }
            }
            elseif($repository_type == 1)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }

            }
            elseif($repository_type == 2)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }
            }

            $base_url = "";
            $param = "";
            $api_dir_url = "";
            if($repository_type == 0)
            {
                $param = "?private_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v4/projects/{$repertory_project_id}/repository";
            }
            elseif($repository_type == 1)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                if($address == "https://github.com")
                {
                    $base_url = "https://api.github.com/repos/{$owner}/{$repo}/contents";
                }
                else
                {
                    $base_url = $address . "/api/v3/repos/{$owner}/{$repo}/contents";
                }
            }
            elseif($repository_type == 2)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v5/repos/{$owner}/{$repo}/contents";
            }
            if($repository_type == 0)
            {
                $api_dir_url = $base_url . "/tree" . $param;
            }
            elseif($repository_type == 1)
            {
                $api_dir_url = $base_url . $param;
            }
            elseif($repository_type == 2)
            {
                $api_dir_url = $base_url . $param;
            }

            $api_file_list = $tool->getUrlContentForSwagger($api_dir_url, 5, $repository_type, $repo_token, $owner);

            if(strpos($api_file_list, "404 Project Not Found") !== false)
            {
                // 项目ID错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "项目不存在";
                exit_output($this->return_json);

            }
            if(strpos($api_file_list, "401 Unauthorized") !== false || strpos($api_file_list, "invalid_token") !== false || strpos($api_file_list, "Bad credentials") !== false)
            {
                // 令牌秘钥错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "秘钥错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "404 Tree Not Found") !== false || strpos($api_file_list, "No commit found for the ref") !== false || strpos($api_file_list, "404 Branch Not Found") !== false)
            {
                // 代码分支错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "分支错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "Not Found") !== false)
            {
                // 配置信息错误（大多为github）
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "Not Found";
                exit_output($this->return_json);
            }

            $api_file_list = json_decode($api_file_list, TRUE);
            if(empty($api_file_list))
            {
                // 接口文件所在目录路径错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "网络错误，请稍后重试";
                exit_output($this->return_json);
            }

            // 创建项目
            $sProjectPath = STATIC_PROJECTS . '/' . $this->user_address . '/' . $project_name;
            // 向Node发请求创建项目
            $oRet = $project_module->createProject( $this->user_address, $project_name);

            if (is_numeric($oRet) && $oRet == 1) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名已经存在，请填入其他项目名';
                exit_output($this->return_json);
            }
            // node操作失败
            if (!$oRet) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '上传项目失败';
                $this->return_json['oRet'] = $oRet;
                exit_output($this->return_json);
            }


            $result = $project_module->insertProjectToDb( $this->user_id, $project_name, $project_description, $project_category, true, $sProjectPath);

            if (!$result)
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = "项目创建失败";
                exit_output($this->return_json);
            }

            // 项目拉取
            $api_module = new \api\module\Api();
            $result = $api_module->synchronizeRepo($project_name,$root_dir, $this->user_address,$repository_type,$base_url,"/", $param,$repo_token, $owner);
            if ($result)
            {
                $this->return_json ['retCode'] = 0;
                $this->return_json ['retMsg'] = "项目同步成功";
                exit_output($this->return_json);
            }
            else
            {
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "项目同步出错";
                exit_output($this->return_json);
            }
        }

        exit_output($this->return_json);

    }


    /**
     * 仓库同步
     */
    public function synchronizeRepo()
    {
        $project_name = securely_input("projectName");
        $root_dir = securely_input("rootPath","/");
        $repo_type = securely_input("repoType", 0);
        $address = securely_input('repoAddress');
        $repo_token = securely_input('repoToken');
        // 代码分支
        $ref = securely_input("ref");
        // 项目ID，gitlab
        $repertory_project_id = securely_input("projectID");
        // 仓库所属空间/用户名，github/码云
        $owner = securely_input("owner");
        // 仓库名，github/码云
        $repo = securely_input("repoName");

        if (TEST)
        {
            $this->user_address = '0x5d30CecAD338c05EDd5C9193E3d5809BeEb6eA55';
            $project_name = "first";
            $root_dir = "children";
            $repo_type = 2;
            $address = "https://gitee.com/";
            $repo_token = '7dc74a55aa3b690d35d7c1f6a512b9e0';
            // 代码分支
            $ref = "master";
            // 项目ID，gitlab
            $repertory_project_id = 1;
            // 仓库所属空间/用户名，github/码云
            $owner = "zekeGitee_admin";
            // 仓库名，github/码云
            $repo = "denglu1-xielu";
        }

        $tool = new \tool\module\Tool();
        if (!isset($project_name))
        {
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "项目名格式非法";
        }
        elseif(! in_array($repo_type, array(
            0,
            1,
            2
        )))
        {
            // 仓库格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "仓库格式非法";
        }
        elseif(! $tool->getHttpCodeForSwagger($address))
        {
            // 地址格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "地址格式非法";

        }
        elseif(empty($repo_token))
        {


            // 账号令牌格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "令牌格式非法";

        }
        elseif(empty($ref))
        {
            // 分支格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "分支格式非法";
        }
        else
        {
            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目录名不合法';
                exit_output($this->return_json);
            }

            $repository_type = $repo_type;
            if($repository_type == 0)
            {
                if(! preg_match('/^[0-9]{1,11}$/', $repertory_project_id))
                {
                    // 项目仓库projectID格式非法
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库projectID格式非法";
                    exit_output($this->return_json);
                }
            }
            elseif($repository_type == 1)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }

            }
            elseif($repository_type == 2)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }
            }

            $base_url = "";
            $param = "";
            $api_dir_url = "";
            if($repository_type == 0)
            {
                $param = "?private_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v4/projects/{$repertory_project_id}/repository";
            }
            elseif($repository_type == 1)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                if($address == "https://github.com")
                {
                    $base_url = "https://api.github.com/repos/{$owner}/{$repo}/contents";
                }
                else
                {
                    $base_url = $address . "/api/v3/repos/{$owner}/{$repo}/contents";
                }
            }
            elseif($repository_type == 2)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v5/repos/{$owner}/{$repo}/contents";
            }
            if($repository_type == 0)
            {
                $api_dir_url = $base_url . "/tree" . $param;
            }
            elseif($repository_type == 1)
            {
                $api_dir_url = $base_url . $param;
            }
            elseif($repository_type == 2)
            {
                $api_dir_url = $base_url . $param;
            }

            $api_file_list = $tool->getUrlContentForSwagger($api_dir_url, 5, $repository_type, $repo_token, $owner);
            if(strpos($api_file_list, "404 Project Not Found") !== false)
            {
                // 项目ID错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "项目ID错误";
                exit_output($this->return_json);

            }
            if(strpos($api_file_list, "401 Unauthorized") !== false || strpos($api_file_list, "invalid_token") !== false || strpos($api_file_list, "Bad credentials") !== false)
            {
                // 令牌秘钥错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "秘钥错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "404 Tree Not Found") !== false || strpos($api_file_list, "No commit found for the ref") !== false || strpos($api_file_list, "404 Branch Not Found") !== false)
            {
                // 代码分支错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "分支错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "Not Found") !== false)
            {
                // 配置信息错误（大多为github）
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "Not Found";
                exit_output($this->return_json);


            }
            $api_file_list = json_decode($api_file_list, TRUE);
            if(empty($api_file_list))
            {
                // 接口文件所在目录路径错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "所在目录路径错误";
                exit_output($this->return_json);
            }
            $api_module = new \api\module\Api();
            $result = $api_module->synchronizeRepo($project_name,$root_dir, $this->user_address,$repository_type,$base_url,"/", $param,$repo_token, $owner);
            if ($result)
            {
                $this->return_json ['retCode'] = 0;
                $this->return_json ['retMsg'] = "同步成功";
                exit_output($this->return_json);
            }
            else
            {
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "同步出错";
                exit_output($this->return_json);
            }
        }

        exit_output($this->return_json);

    }

    /**
     * 码农链文档生成
     */
    public function generateDocumentByCoderChain()
    {
        $project_name = securely_input("projectName");
        $root_dir = securely_input("rootPath","/");
        $target_user_name = securely_input("targetUserName");
        $target_project_name = securely_input("targetProjectName");
        $target_language = securely_input("targetLanguage","java");
        $document_type = securely_input("documentType", "word");
        $document_name = securely_input("documentName");
        if (TEST)
        {
            $project_name= "second";
            $this->user_address = '0xa2e608C63FcfC3a4Af93545b3B262132ba8c1b90';
            $this->user_id = '56f507edff2c5c157f3007541e9782b0';
            $target_project_name  = "swagger3";
            $target_user_name = "zeke2";
            $root_dir = "直接下载";
            $target_language = 'java';
            $document_type = "word";
            $document_name = 'swagger111';

        }
        if (!isset($target_language))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '语言类型不合法';
        }
        elseif(!isset($document_type))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档类型不合法';
        }
        elseif (!isset($project_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不合法';
        }
        elseif (!isset($root_dir))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档部署目录不合法';
        }
        elseif (!isset($target_user_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '目标项目名不合法';
        }
        elseif (!isset($target_project_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '目标用户名不合法';
        }
        elseif (!isset($document_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档名格式非法';
        }
        else
        {
            // 获取目标用户的address和id
            $user_module = new \user\module\User();
            $target_user = $user_module->getUser(array(
                'sUserName'
            ),array(
                $target_user_name
            ));
            if (!$target_user)
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目标用户不存在';
                exit_output($this->return_json);
            }
            $target_user_id = $target_user['sId'];
            $target_user_address = $target_user['sWalletAddress'];


            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
                exit_output($this->return_json);
            }
            if (!$project_module->isValidFileName($target_project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目标项目名不合法';
                exit_output($this->return_json);
            }
            // 检查当前项目是否存在
            if (!$project_module->checkProjectIsExist($this->user_id,$project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目不存在';
                exit_output($this->return_json);
            }
            // 检查目标项目是否存在
            if (!$project_module->checkProjectIsExist($target_user_id, $target_project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目标项目不存在';
                exit_output($this->return_json);
            }
            // 检查部署目标文档名是否已经存在，直接检查当前静态文件
            $document_path = $document_name;
            if ($document_type == "word")
            {
                $document_path = $document_path . ".docx";
            }
            if ($document_type == "pdf")
            {
                $document_path = $document_path . ".pdf";
            }

            if ($root_dir == "/")
            {
                $check_path = STATIC_PROJECTS . '/' . $this->user_address . '/' . $project_name . '/' .$document_path;
            }
            else
            {
                $check_path = STATIC_PROJECTS . '/' . $this->user_address . '/' . $project_name . '/' . $root_dir . "/" . $document_path;
            }
            if ($root_dir != "直接下载")
            {
                if (file_exists($check_path))
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '文档名已存在，请重新输入';
                    exit_output($this->return_json);
                }
                // 是否有相应的文件夹，没有创建
                if (strpos($check_path,'/')!=false)
                {
                    $position = strrpos($check_path,'/');
                    $path = substr($check_path,0,$position);
                    if(!file_exists($path)){
                        mkdir($path,0777,true);
                    }
                }
            }

            // 检查完毕，开始获取文件内容并识别
            $api_module = new \api\module\Api();
            $content_list = array();
            $api_module->getProjectContentList($target_user_address, $target_project_name, '', $content_list, $target_language);
            if ($content_list)
            {
                $module = new \api\module\coderChainSwagger();
                $data = $module->getApiData($content_list);
                if ($document_type == "word")
                {
                    $objWriter = $api_module->getWord($data);
                    if ($root_dir == "直接下载")
                    {
                        $file_module = new \file\module\File();
                        $tmp_path = STATIC_PROJECTS . '/' . $this->user_address . '/' .$document_path;
                        $objWriter->save($tmp_path);
                        $content = file_get_contents($tmp_path);
                        if ($content)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '保存成功';
                            $this->return_json['oRet']['path'] = $document_path;
                        }
                        else
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '下载失败';
                            $file_module->unlink($tmp_path);
                        }
                    }
                    else
                    {
                        $file_module = new \file\module\File();
                        $objWriter->save($check_path);
                        $content = file_get_contents($check_path);
                        if ($content)
                        {
                            if ($root_dir == "/")
                            {
                                $path = '';
                            }
                            else
                            {
                                $path =  $root_dir;
                            }
                            $oRet = $file_module->saveResource($this->user_address, $project_name, $path, $check_path, $document_path);
                            if (!$oRet)
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '上传文件失败，请检查是否文件名重复';
                                exit_output($this->return_json);

                            }
                            else
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '文档保存成功';
                                exit_output($this->return_json);
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '文档保存失败';
                            exit_output($this->return_json);
                        }

                    }
                }
                else
                {
                    $objWriter = $api_module->getPdf($data);
                    if ($root_dir == "直接下载")
                    {
                        $file_module = new \file\module\File();
                        $content = $objWriter ->getPDFData();
                        $tmp_path = STATIC_PROJECTS . '/' . $this->user_address . '/' .$document_path;
                        $file_module->write($tmp_path,$content);
                        $content = file_get_contents($tmp_path);
                        if ($content)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '保存成功';
                            $this->return_json['oRet']['path'] = $document_path;
                        }
                        else
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '下载失败';
                            $file_module->unlink($tmp_path);
                        }
                    }
                    else
                    {
                        $file_module = new \file\module\File();
                        $content = $objWriter ->getPDFData();
                        if ($content)
                        {
                            $file_module->write($check_path,$content);
                            if ($root_dir == "/")
                            {
                                $path = '';
                            }
                            else
                            {
                                $path =  $root_dir;
                            }
                            $oRet = $file_module->saveResource($this->user_address, $project_name, $path, $check_path, $document_path);

                            if ($oRet)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '文档保存成功';
                                exit_output($this->return_json);
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '上传文件失败，请检查是否文件名重复';
                                exit_output($this->return_json);
                            }

                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '文档保存失败';
                            exit_output($this->return_json);
                        }
                    }
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目标项目不存在';
                exit_output($this->return_json);
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 仓库文档生成
     */
    public function generateDocument()
    {

        $target_language = securely_input("targetLanguage","java");
        $document_type = securely_input("documentType", "word");
        $document_name = securely_input("documentName");
        $project_name = securely_input("projectName");
        $root_dir = securely_input("rootPath","/");
        $repo_type = securely_input("repoType", 0);
        $address = securely_input('repoAddress');
        $repo_token = securely_input('repoToken');
        // 代码分支
        $ref = securely_input("ref");
        // 项目ID，gitlab
        $repertory_project_id = securely_input("projectID");
        // 仓库所属空间/用户名，github/码云
        $owner = securely_input("owner");
        // 仓库名，github/码云
        $repo = securely_input("repoName");
        if (TEST)
        {
            $this->user_address = '0xa2e608C63FcfC3a4Af93545b3B262132ba8c1b90';
            $this->user_id = '56f507edff2c5c157f3007541e9782b0';
            $project_name = "second";
            $root_dir = "直接下载";
            $target_language = 'java';
            $document_type = "word";
            $document_name = 'test';


            $repo_type = 2;
            $address = "https://gitee.com/";
            $repo_token = '7dc74a55aa3b690d35d7c1f6a512b9e0';
            // 代码分支
            $ref = "master";
            // 项目ID，gitlab
            $repertory_project_id = 1;
            // 仓库所属空间/用户名，github/码云
            $owner = "zekeGitee";
            // 仓库名，github/码云
            $repo = "swagger";
        }
        $tool = new \tool\module\Tool();
        if (!isset($target_language))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '语言类型不合法';
        }
        elseif(!isset($document_type))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档类型不合法';
        }
        elseif (!isset($project_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不合法';
        }
        elseif (!isset($root_dir))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档部署目录不合法';
        }
        elseif (!isset($document_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '文档名格式非法';
        }
        elseif(! in_array($repo_type, array(
            0,
            1,
            2
        )))
        {
            // 仓库格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "仓库格式非法";
        }
        elseif(! $tool->getHttpCodeForSwagger($address))
        {
            // 地址格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "地址格式非法";

        }
        elseif(empty($repo_token))
        {
            // 账号令牌格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "令牌格式非法";

        }
        elseif(empty($ref))
        {
            // 分支格式非法
            $this->return_json ['retCode'] = 100;
            $this->return_json ['retMsg'] = "分支格式非法";
        }
        else
        {
            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($project_name)) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目录名不合法';
                exit_output($this->return_json);
            }
            // 检查当前项目是否存在
            if (!$project_module->checkProjectIsExist($this->user_id,$project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目不存在';
                exit_output($this->return_json);
            }

            $repository_type = $repo_type;
            if($repository_type == 0)
            {
                if(! preg_match('/^[0-9]{1,11}$/', $repertory_project_id))
                {
                    // 项目仓库projectID格式非法
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库projectID格式非法";
                    exit_output($this->return_json);
                }
            }
            elseif($repository_type == 1)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }

            }
            elseif($repository_type == 2)
            {
                if(empty($owner))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目主人格式非法";
                    exit_output($this->return_json);
                }
                elseif(empty($repo))
                {
                    $this->return_json ['retCode'] = 100;
                    $this->return_json ['retMsg'] = "项目仓库名格式非法";
                    exit_output($this->return_json);
                }
            }

            // 检查部署目标文档名是否已经存在，直接检查当前静态文件
            $document_path = $document_name;
            if ($document_type == "word")
            {
                $document_path = $document_path . ".docx";
            }
            else
            {
                $document_path = $document_path . ".pdf";
            }

            if ($root_dir == "/")
            {
                $check_path = STATIC_PROJECTS . '/' . $this->user_address . '/' . $project_name . '/' .$document_path;
            }
            else
            {
                $check_path = STATIC_PROJECTS . '/' . $this->user_address . '/' . $project_name . '/' . $root_dir . "/" . $document_path;
            }
            if ($root_dir != "直接下载")
            {
                if (file_exists($check_path))
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '文档名已存在，请重新输入';
                    exit_output($this->return_json);
                }
                // 是否有相应的文件夹，没有创建
                if (strpos($check_path,'/')!=false)
                {
                    $position = strrpos($check_path,'/');
                    $path = substr($check_path,0,$position);
                    if(!file_exists($path)){
                        mkdir($path,0777,true);
                    }
                }
            }


            $base_url = "";
            $param = "";
            $api_dir_url = "";
            if($repository_type == 0)
            {
                $param = "?private_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v4/projects/{$repertory_project_id}/repository";
            }
            elseif($repository_type == 1)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                if($address == "https://github.com")
                {
                    $base_url = "https://api.github.com/repos/{$owner}/{$repo}/contents";
                }
                else
                {
                    $base_url = $address . "/api/v3/repos/{$owner}/{$repo}/contents";
                }
            }
            elseif($repository_type == 2)
            {
                $param = "?access_token={$repo_token}&ref={$ref}&per_page=100";
                $base_url = $address . "/api/v5/repos/{$owner}/{$repo}/contents";
            }
            if($repository_type == 0)
            {
                $api_dir_url = $base_url . "/tree" . $param;
            }
            elseif($repository_type == 1)
            {
                $api_dir_url = $base_url . $param;
            }
            elseif($repository_type == 2)
            {
                $api_dir_url = $base_url . $param;
            }

            $api_file_list = $tool->getUrlContentForSwagger($api_dir_url, 5, $repository_type, $repo_token, $owner);
            if(strpos($api_file_list, "404 Project Not Found") !== false)
            {
                // 项目ID错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "项目ID错误";
                exit_output($this->return_json);

            }
            if(strpos($api_file_list, "401 Unauthorized") !== false || strpos($api_file_list, "invalid_token") !== false || strpos($api_file_list, "Bad credentials") !== false)
            {
                // 令牌秘钥错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "秘钥错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "404 Tree Not Found") !== false || strpos($api_file_list, "No commit found for the ref") !== false || strpos($api_file_list, "404 Branch Not Found") !== false)
            {
                // 代码分支错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "分支错误";
                exit_output($this->return_json);
            }
            if(strpos($api_file_list, "Not Found") !== false)
            {
                // 配置信息错误（大多为github）
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "Not Found";
                exit_output($this->return_json);
            }
            $api_file_list = json_decode($api_file_list, TRUE);
            if(empty($api_file_list))
            {
                // 接口文件所在目录路径错误
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "所在目录路径错误";
                exit_output($this->return_json);
            }
            $api_module = new \api\module\Api();
            // 检查完毕
            if ($target_language == "java")
            {
                $module = new \api\module\SwaggerJAVA();
                $api_structure_list = $module->getDatastructureList($base_url,"/",$param,$repository_type,$repo_token,$owner);
                $data = $module->getApiData($api_structure_list,$base_url,"/",$param,$repository_type,$repo_token,$owner);
                if ($document_type == "word")
                {
                    $objWriter = $api_module->getWord($data);
                    if ($root_dir == "直接下载")
                    {
                        $file_module = new \file\module\File();
                        $tmp_path = STATIC_PROJECTS . '/' . $this->user_address . '/' .$document_path;
                        $objWriter->save($tmp_path);
                        $content = file_get_contents($tmp_path);
                        if ($content)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '保存成功';
                            $this->return_json['oRet']['path'] = $document_path;
                        }
                        else
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '下载失败';
                            $file_module->unlink($tmp_path);
                        }
                    }
                    else
                    {
                        $file_module = new \file\module\File();
                        $objWriter->save($check_path);
                        $content = file_get_contents($check_path);
                        if ($content)
                        {
                            if ($root_dir == "/")
                            {
                                $path = '';
                            }
                            else
                            {
                                $path =  $root_dir;
                            }
                            $oRet = $file_module->saveResource($this->user_address, $project_name, $path, $check_path, $document_path);

                            if (!$oRet)
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '上传文件失败，请检查是否文件名重复';
                                exit_output($this->return_json);

                            }
                            else
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '文档保存成功';
                                exit_output($this->return_json);
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '文档保存失败';
                            exit_output($this->return_json);
                        }
                    }
                }
                else
                {
                    $objWriter = $api_module->getPdf($data);
                    if ($root_dir == "直接下载")
                    {
                        $file_module = new \file\module\File();
                        $content = $objWriter ->getPDFData();
                        $tmp_path = STATIC_PROJECTS . '/' . $this->user_address . '/' .$document_path;
                        $file_module->write($tmp_path,$content);
                        $content = file_get_contents($tmp_path);
                        if ($content)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '保存成功';
                            $this->return_json['oRet']['path'] = $document_path;
                        }
                        else
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '下载失败';
                            $file_module->unlink($tmp_path);
                        }
                    }
                    else
                    {
                        $file_module = new \file\module\File();
                        $content = $objWriter ->getPDFData();
                        if ($content)
                        {
                            $file_module->write($check_path,$content);
                            if ($root_dir == "/")
                            {
                                $path = '';
                            }
                            else
                            {
                                $path =  $root_dir;
                            }
                            $oRet = $file_module->saveResource($this->user_address, $project_name, $path, $check_path, $document_path);

                            if (!$oRet)
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '上传文件失败，请检查是否文件名重复';
                                exit_output($this->return_json);

                            }
                            else
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '文档保存成功';
                                exit_output($this->return_json);
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '文档保存失败';
                            exit_output($this->return_json);
                        }
                    }
                }
            }
            else
            {
                $this->return_json ['retCode'] = 100;
                $this->return_json ['retMsg'] = "暂不支持该编程语言的注解";
                exit_output($this->return_json);
            }

        }
        exit_output($this->return_json);
    }

    public function download()
    {
        $file_module = new \file\module\File();
        $path = securely_input("path");
        $type = securely_input("type");
        $fileName = iconv('utf-8', 'gb2312', $path);
        $path = STATIC_PROJECTS.'/'.$this->user_address.'/'.$path;
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . filesize($path));
        if ($type == "pdf")
        {
            header('Content-Type:application/pdf');
            header("Content-Disposition: attachment; filename=\"" . $fileName. "\"");
        }
        else
        {
            header("Content-type:application/msword");
            header("Content-Disposition: attachment; filename=\"" . $fileName. "\"");
        }
        readfile($path);
        $file_module->unlink($path);
    }



}