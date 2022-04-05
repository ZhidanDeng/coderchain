<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/27
 * @Time: 23:43
 */

namespace issues\controller;


class Issues
{
    private $log_path = "/ex/issues/";

    private $logger = null;
    //返回json类型
    private $return_json = array('type' => 'issues');

    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }

    /**
     * 增加Issues
     */
    public function addIssues()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            $title = securely_input("title");
            $content = quick_input("content");
            // issues类别，-1为未设置，0为bug，1为enhancement，2为feature，3为duplicate，4为invalid，5为question，6为wontfix
            $type = securely_input("type",-1);
            // issues状态,0为待办的，1进行中，2已完成，3已拒绝
            $status = securely_input("status");
            // 优先级，-1不指定，0严重，1主要，2次要，3不重要
            $level = securely_input("level",-1);
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            elseif(!isset($title))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '标题不能为空';
            }
            elseif (!isset($content))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '内容不能为空';
            }
            elseif(!in_array($type,array(
                -1,
                0,
                1,
                2,
                3,
                4,
                5,
                6
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '类型格式非法';
            }
            elseif(!in_array($status,array(
                0,
                1,
                2,
                3,
                4
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '状态格式非法';
            }
            elseif(!in_array($level,array(
                -1,
                0,
                1,
                2,
                3
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '优先级格式非法';
            }
            else
            {
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);

                if ($project_user_id)
                {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id,$project_name);
                    if ($project_id)
                    {
                        // 获取当前用户的用户名
                        $user_name = $user_module->getUserNameByUserId($user_id);
                        $issues_module = new \issues\module\Issues();
                        $result = $issues_module->addIssues($project_id,$title,$content,$type,$user_name,$status,$level);
                        if ($result)
                        {
                            $this->return_json['retCode'] = 0;
                            $this->return_json['retMsg'] = '添加成功';
                            $this->return_json['oRet']['issuesID'] = $result;
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '添加失败';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }
        }
        exit_output($this->return_json);
    }

    /**
     * 根据类别获取issues列表
     */
    public function getIssuesList()
    {
        $project_name = securely_input("projectName");
        // 项目主人名
        $user_name = securely_input("userName");
        $issue_type = securely_input("status",-1);
        if (!isset($project_name) || !isset($user_name) || !isset($issue_type))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $project_module = new \project\module\Project();
            if (!$project_module->isValidFileName($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不合法';
                exit_output($this->return_json);
            }
            $user_module = new \user\module\User();
            $target_user = $user_module->getUser(array(
                'sUserName'
            ),array(
                $user_name
            ));
            if (!$target_user)
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '目标用户不存在';
                exit_output($this->return_json);
            }
            $target_user_id = $target_user['sId'];
            $project_id = $project_module->getProjectID($target_user_id, $project_name);
            if ($project_id)
            {
                $module = new \issues\module\Issues();
                $result = $module->getIssuesList($project_id,$issue_type);
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '获取成功';
                $this->return_json['oRet']['data'] = $result;

            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目不存在';
            }

        }
        exit_output($this->return_json);
    }

    /**
     * 获取issues信息
     */
    public function getIssuesInfo()
    {
        $issues_id = securely_input("issuesID");
        $project_name = securely_input("projectName");
        $project_user_name = securely_input("userName");
        if (!isset($project_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '项目名不能为空';
        }
        elseif (!isset($project_user_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '用户名不能为空';
        }
        else if(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '格式非法';
        }
        else
        {
            $user_module = new \user\module\User();
            if (!$user_module->checkLogin())
            {
                $user_name = null;
            }
            else
            {
                $user_id = $user_module->getLoginUserId();
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
            }
            $project_user_id = $user_module->getUserIdByUserName($project_user_name);

            if ($project_user_id) {
                $project_module = new \project\module\Project();
                $project_id = $project_module->getProjectID($project_user_id, $project_name);
                if ($project_id) {
                    $issues_module = new \issues\module\Issues();
                    $result = $issues_module->getIssuesInfo($issues_id,$project_id,$user_name);
                    if ($result)
                    {
                        if (!$result['content'])
                        {
                            $result['content'] = "";
                        }
                        if ($result['createUser'] == $user_name)
                        {
                            $result['isCreator'] = true;
                        }
                        else
                        {
                            $result['isCreator'] = false;
                        }
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '获取成功';
                        $this->return_json['oRet']['issueInfo'] = $result;
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '获取失败';

                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目不存在';
                }
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目用户不存在';
                exit_output($this->return_json);
            }


        }
        exit_output($this->return_json);
    }



    /**
     * 修改Issues
     */
    public function editIssues()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $issues_id = securely_input("issueID");
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            $title = securely_input("title");
            $content = quick_input("content");
            // issues类别，-1为未设置，0为bug，1为enhancement，2为feature，3为duplicate，4为invalid，5为question，6为wontfix
            $type = securely_input("type",-1);
            // issues状态,0为待办的，1进行中，2已完成，3已拒绝
            $status = securely_input("status");
            // 优先级，-1不指定，0严重，1主要，2次要，3不重要
            $level = securely_input("level",-1);
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            elseif(!isset($title))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '标题不能为空';
            }
            elseif (!isset($content))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '内容不能为空';
            }
            elseif(!in_array($type,array(
                -1,
                0,
                1,
                2,
                3,
                4,
                5,
                6
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '类型格式非法';
            }
            elseif(!in_array($status,array(
                0,
                1,
                2,
                3,
                4
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '状态格式非法';
            }
            elseif(!in_array($level,array(
                -1,
                0,
                1,
                2,
                3
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '优先级格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            else
            {
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id) {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id)
                    {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsIssuesOwner($issues_id,$user_name)||$issues_module->checkIsProjectOwner($user_id,$project_id))
                        {
                            $result = $issues_module->editIssues($issues_id,$title,$content,$type,$status,$level,$project_id);
                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '修改成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '修改失败';
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }

        }
        exit_output($this->return_json);
    }

    /**
     * 更新issues状态
     */
    public function updateIssuesStatus()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $issues_id = securely_input("issuesID");
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            // issues状态,0为待办的，1进行中，2已完成，3已拒绝
            $status = securely_input("status");
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            elseif(!in_array($status,array(
                0,
                1,
                2,
                3,
                4
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '状态格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            else
            {
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id) {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id)
                    {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsIssuesOwner($issues_id,$user_name)||$issues_module->checkIsProjectOwner($user_id,$project_id))
                        {
                            $result = $issues_module->updateIssuesStatus($issues_id,$status,$project_id);
                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '更新成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '更新失败';
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }

        }
        exit_output($this->return_json);
    }

    public function batchUpdateIssuesStatus()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            // issuesID
            $ids = securely_input ('issueID');
            $arr = json_decode ($ids);

            // 去掉数组中不是数字的ID
            $arr = preg_grep ('/^[0-9]{1,11}$/', $arr);
            $issue_ids = implode (',', $arr);
            // issues状态,0为待办的，1进行中，2已完成，3已拒绝
            $status = securely_input("status");
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            elseif(!in_array($status,array(
                0,
                1,
                2,
                3,
                4
            ))){
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '状态格式非法';
            }
            else
            {
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id) {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id)
                    {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsProjectOwner($user_id,$project_id))
                        {
                            $result = $issues_module->batchUpdateIssuesStatus($status,$issue_ids,$project_id);
                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '修改成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '修改失败';
                            }

                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }

        }
        exit_output($this->return_json);

    }


    /**
     * 删除Issues
     */
    public function deleteIssues()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $issues_id = securely_input("issueID");
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            if(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            else
            {
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id) {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id)
                    {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsIssuesOwner($issues_id,$user_name)||$issues_module->checkIsProjectOwner($user_id,$project_id))
                        {
                            $result = $issues_module->deleteIssues($issues_id,$project_id);
                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '删除成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '删除失败';
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }
        }
        exit_output($this->return_json);
    }


    public function batchDeleteIssues()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            // issuesID
            $ids = securely_input ('issueID');
            $arr = json_decode ($ids);

            // 去掉数组中不是数字的ID
            $arr = preg_grep ('/^[0-9]{1,11}$/', $arr);
            $issue_ids = implode (',', $arr);

            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            else
            {
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id) {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id)
                    {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsProjectOwner($user_id,$project_id))
                        {

                            $result = $issues_module->batchDeleteIssues($issue_ids,$project_id);

                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '失败';
                            }

                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }

            }
        }
        exit_output($this->return_json);
    }
    /**
     * 评论
     */
    public function addComment()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $content = quick_input("content");
            $issues_id = securely_input("issueID");
            // 获取当前用户的用户名
            $user_name = $user_module->getUserNameByUserId($user_id);
            if (empty($content))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '评论内容格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            else
            {
                $issue_module = new \issues\module\Issues();
                $result = $issue_module->addComment($issues_id,$content,$user_name);
                if ($result)
                {
                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '添加成功';
                    $this->return_json['oRet']['commentID'] = $result;
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '添加失败';
                }

            }
        }
        exit_output($this->return_json);
    }

    /**
     * 修改评论
     */
    public function editComment()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $issues_id = securely_input("issueID");
            $comment_id = securely_input("commentID");
            $content = securely_input("content");
            if (empty($content))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '评论内容格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $comment_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'commentID格式非法';
            }
            else
            {
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
                $issues_module = new \issues\module\Issues();
                if ($issues_module->checkIsCommentOwner($issues_id,$user_name,$comment_id))
                {
                    $result = $issues_module->editComment($comment_id,$content,$issues_id);
                    if ($result)
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '更新成功';
                    }
                    else
                    {
                        $this->return_json['retCode'] = 0;
                        $this->return_json['retMsg'] = '更新失败';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '用户权限不足';
                }
            }
        }
        exit_output($this->return_json);
    }

    /**
     * 删除评论
     */
    public function deleteComment()
    {
        // 检查登录
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        $user_id = $user_module->getLoginUserId();
        if (!$user_id)
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
        }
        else
        {
            $project_name = securely_input("projectName");
            $project_user_name = securely_input("userName");
            $issues_id = securely_input("issueID");
            $comment_id = securely_input("commentID");
            if (!isset($project_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目名不能为空';
            }
            elseif (!isset($project_user_name))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户名不能为空';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $issues_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'issuesID格式非法';
            }
            elseif(! preg_match ('/^[0-9]{1,11}$/', $comment_id))
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = 'commentID格式非法';
            }
            else
            {
                // 获取当前用户的用户名
                $user_name = $user_module->getUserNameByUserId($user_id);
                // 获取项目用户的id
                $project_user_id = $user_module->getUserIdByUserName($project_user_name);
                if ($project_user_id)
                {
                    $project_module = new \project\module\Project();
                    $project_id = $project_module->getProjectID($project_user_id, $project_name);
                    if ($project_id) {
                        $issues_module = new \issues\module\Issues();
                        if ($issues_module->checkIsCommentOwner($issues_id,$user_name,$comment_id)||$issues_module->checkIsIssuesOwner($issues_id, $user_name) || $issues_module->checkIsProjectOwner($user_id, $project_id))
                        {
                            $result = $issues_module->deleteComment($comment_id,$issues_id);
                            if ($result)
                            {
                                $this->return_json['retCode'] = 0;
                                $this->return_json['retMsg'] = '删除成功';
                            }
                            else
                            {
                                $this->return_json['retCode'] = 100;
                                $this->return_json['retMsg'] = '删除失败';
                            }
                        }
                        else
                        {
                            $this->return_json['retCode'] = 100;
                            $this->return_json['retMsg'] = '用户权限不足';
                        }
                    }
                    else
                    {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                    }
                }
                else
                {
                    $this->return_json['retCode'] = 100;
                    $this->return_json['retMsg'] = '项目用户不存在';
                }
            }
        }
        exit_output($this->return_json);
    }



}