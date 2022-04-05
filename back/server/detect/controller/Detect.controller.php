<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/3
 * @Time: 11:34
 */

namespace detect\controller;


class Detect
{

    private $log_path = "/ex/detect";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'detect');

    private $m_sDomain = "coderchain.cn";

    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }

    public function getAllDetectReport()
    {
        $sProjectName = securely_input('sProjectName');
        $sUserName = securely_input('sUserName');
        if (!isset($sProjectName) || !isset($sUserName))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $user_module = new \user\module\User();
            $sProjectName = urldecode($sProjectName);

            // 拉取数据前处理
            $arrUser = $user_module->getUser(array('sUserName'),array($sUserName));
            if (!$arrUser) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '用户不存在';
                exit_output($this->return_json);
            }
            $sUserId = $arrUser['sId'];
            
            $project_module = new \project\module\Project();
            $arrProject = $project_module->getProjectFromDb(array('sUserId', 'sProjectName'),array(
                $sUserId,
                $sProjectName
            ));
            if (count($arrProject) < 1) {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '项目不存在';
                exit_output($this->return_json);
            }

            $sProjectId = $arrProject[0]['sId'];

            $arrParam = array('sProjectId' , 'bFinish' );
            $arr_value = array(
                $sProjectId,
                1
            );
            $detect_module = new \detect\module\Detect();
            $arrRet = $detect_module->getDetectTask( $arrParam,$arr_value,  array(
                'createAt' => 'DESC',
            ));
            $this->return_json['retCode'] = 0;
            $this->return_json['retMsg'] = '获取所有检测报告成功';
            $this->return_json['oRet'] = $arrRet;
        }

        exit_output($this->return_json);


    }

    public function addDetectByName()
    {
        $user_module = new \user\module\User();
        if (!$user_module->checkLogin())
        {
            $this->return_json['retCode'] = -10;
            $this->return_json['retMsg'] = '没有登录';
            exit_output($this->return_json);
        }
        else
        {
            $sProjectName = securely_input('sProjectName');
            $sUserName = securely_input('sUserName');
            if (!isset($sProjectName) || !isset($sUserName))
            {
                $this->return_json['retCode'] = -101;
                $this->return_json['retMsg'] = '参数不合法';
            }
            else
            {
                $user_id = $user_module->getLoginUserId();
                if ($user_id) {
                    // 拉取数据前处理
                    $arrUser = $user_module->getUser(array('sUserName'),array($sUserName));
                    if (!$arrUser) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '用户不存在';
                        exit_output($this->return_json);
                    }
                    $sUserId = $arrUser['sId'];
                    if ($user_id != $sUserId) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '您没有权限检测该项目';
                        exit_output($this->return_json);
                    }
                    $project_module = new \project\module\Project();
                    $arrProject = $project_module->getProjectFromDb(array('sUserId','sProjectName'),array(
                        $sUserId,
                        $sProjectName
                    ));
                    if (count($arrProject) < 1) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '项目不存在';
                        exit_output($this->return_json);
                    }
                    
                    $sProjectId = $arrProject[0]['sId'];
                    if (strlen($sProjectId) != 32) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '错误的项目id';
                        exit_output($this->return_json);
                    }

                    // 不大理解的操作
                    $arrProject = $project_module->getProjectFromDb( array(
                        'sId'
                    ),array($sProjectId));
                    if (count($arrProject) < 1) {

                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '该项目id暂不存在，无法检测';
                        exit_output($this->return_json);
                    }

                    $oProject = $arrProject[0];
                    $sProjectPath = $oProject['sProjectPath'];

                    if ($sUserId != $oProject['sUserId']) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '不是项目所有者';
                        exit_output($this->return_json);

                    }

                    $detect_module = new \detect\module\Detect();
                    // 1 向python发起检测请求，获得检测id
                    $oRet = $detect_module->addJobByFsPath($sProjectPath);
                    $oRet = json_decode($oRet, true);

                    if ($oRet == false) {
                        // 检测失败

                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '代码检测服务暂时不可用';
                        exit_output($this->return_json);
                    }

                    if ($oRet['code'] != 1001) {
                        // 检测失败
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = $oRet['msg'];
                        exit_output($this->return_json);
                    }

                    $sId = $oRet['result']['sid'];

                    // 2 插入数据库，向前端返回
                    $bRet = $detect_module->saveDetectTask($sId, $sUserId, $sProjectId, $sProjectPath);

                    if (!$bRet) {
                        $this->return_json['retCode'] = 100;
                        $this->return_json['retMsg'] = '保存检测任务失败';
                        exit_output($this->return_json);

                    }

                    $this->return_json['retCode'] = 0;
                    $this->return_json['retMsg'] = '添加任务成功';
                    $this->return_json['oRet'] = array(
                        'sDetectTaskId' => $sId
                    );
                    exit_output($this->return_json);
                }
                else
                {
                    $this->return_json['retCode'] = -10;
                    $this->return_json['retMsg'] = '未登录';
                }
            }
        }
        exit_output($this->return_json);

    }

    public function detectStatus()
    {
        $task_id = securely_input('sDetectTaskId');
        if (!isset($task_id))
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        else
        {
            $detect_module = new \detect\module\Detect();
            $oRet = $detect_module->getStatus($task_id);
            $oRet = json_decode($oRet, true);

            if ($oRet['code'] != 1001) {
                // 检测失败
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = $oRet['msg'];
            }

            // 这里简单计算一下分数，分数越高越危险
            $arrStatistic = $oRet['result']['statistic'];
            $iScore = 0;
            $arrRiskWeight = array(
                'critical' => 10,
                'high' => 5,
                'medium' => 3,
                'low' => 1
            );
            foreach ($arrStatistic as $key => $val) {
                if ($val > 0) {
                    $iScore = $iScore + $arrRiskWeight[$key] * $val;
                }
            }


            // 插入数据库，修改状态
            $bRet = $detect_module->updateDetectTask($task_id, $iScore);

            if (!$bRet) {
                // 更新数据库失败

                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '更新检测任务状态到数据库失败';

            }

            $this->return_json['retCode'] = 0;
            $this->return_json['retMsg'] = '检测完成';
            $this->return_json['oRet'] = array(
                'sDetectTaskId' => $task_id,
                'sReportPath' => $oRet['result']['report'],
                'iScore' => $iScore
            );


        }
        exit_output($this->return_json);
    }

    public function detectReport()
    {
        $task_id = securely_input('sDetectTaskId');
        if ($task_id)
        {
            $detect_module = new \detect\module\Detect();
            $sRet = $detect_module->getReport($task_id);
            if (!$sRet) {
                $sRet = '检测服务暂不可用，快去体验其他功能吧~';
            }
            echo $sRet;
        }
        else
        {
            $this->return_json['retCode'] = -101;
            $this->return_json['retMsg'] = '参数不合法';
        }
        exit_output($this->return_json);
    }
}