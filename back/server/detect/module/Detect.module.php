<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/3
 * @Time: 11:35
 */

namespace detect\module;


class Detect
{
    static $g_key = 'your_secret_key';
    
    static function post($sUrl, $arrParams)
    {
        $sJsonStr = json_encode($arrParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sJsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($sJsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // return array($httpCode, $response);
        return $response;
    }

    static function getDetectUrl()
    {
        // 暂时不启用，文件共享问题还没有解决
        // return 'http://47.100.187.162:3307';
        return 'http://127.0.0.1:8383';
    }


    public function getReport( $sId)
    {
        $sUrl = self::getDetectUrl() . '/?sid=' . $sId;
        return file_get_contents($sUrl);
    }
    
    public function getDetectTask( $where, $arr_value, $order = array())
    {
        $dao = new \detect\dao\Detect();
        return $dao->getDetectTask($where,$arr_value, $order);
    }   
    
    public function addJobByFsPath($sProjectPath)
    {
        $sUrl = self::getDetectUrl() . '/api/addfspath';
        $arrParams = array('sFilePath' => $sProjectPath);

        $oRet = self::post($sUrl, $arrParams);

        return $oRet;
    }
    
    public function saveDetectTask($sId, $sUserId, $sProjectId, $sProjectPath)
    {
        $dao = new \detect\dao\Detect();
        return $dao->saveDetectTask($sId, $sUserId, $sProjectId, $sProjectPath);
    }
    
    public function getStatus($sDetectTaskId)
    {
        $sUrl = self::getDetectUrl() . '/api/status';
        $arrParams = array('sid' => $sDetectTaskId, 'key' => self::$g_key);
       
        $oRet = self::post($sUrl, $arrParams);

        return $oRet;
    }

    public function updateDetectTask( $task_id,$iScore)
    {
        $dao = new \detect\dao\Detect();
        return $dao->updateDetectTask( $task_id,$iScore);
    }
}