<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/3
 * @Time: 12:16
 */

namespace detect\dao;

class Detect
{
    public function getDetectTask( $where,$arr_value, $order = array())
    {
        $db = get_database();
        $sql = combine_select_prepare_sql('tbDetectTaskEx',array(
            '*'
        ),$where, $order );
        $result = $db->prepareExecuteAll($sql,$arr_value);
        if ($db->getAffectRow()>0)
        {
            return $result;
        }
        else
        {
            return array();
        }
    }

    public function saveDetectTask($sDetectId, $sUserId, $sProjectId, $sProjectPath)
    {
        $db = get_database();
        $sql = combine_insert_prepare_sql('tbDetectTaskEx',array(
            'sId',
            'sDetectTaskId',
            'sUserId',
            'sProjectId' ,
            'sProjectPath'
        ));
        $db->prepareExecute($sql,array(
            \tool\module\Tool::uuid(),
            $sDetectId,
            $sUserId,
            $sProjectId,
            $sProjectPath
        ));
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function updateDetectTask($task_id, $iScore)
    {
        $db = get_database();
        $sql = combine_update_prepare_sql('tbDetectTaskEx',array(
            'bFinish' ,
            'iScore' ,
            'updateAt'
        ),array(
            'sDetectTaskId'
        ));
        $db->prepareExecute($sql,array(
            1,
            $iScore,
            time(),
            $task_id
        ));
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }

    }
}