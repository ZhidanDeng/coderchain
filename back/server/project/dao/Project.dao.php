<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/1
 * @Time: 23:50
 */

namespace project\dao;



class Project
{
    public function getProjectID($user_id,$projectName)
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT sId FROM tbProjectEx WHERE sUserId = ? AND sProjectName = ?",array(
            $user_id,
            $projectName
        ));
        if ($result)
        {

            return $result['sId'];
        }
        else
        {
            return false;
        }
    }

    public function checkProjectIsExist($sUserId, $sProjectName)
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT * FROM tbProjectEx WHERE sUserId = ? AND sProjectName = ?",array(
            $sUserId,
            $sProjectName
        ));
        if ($result)
        {

            return true;
        }
        else
        {
            return false;
        }
    }
    public function deleteProjectFromDb( $sUserId, $sProjectName)
    {
        $db = get_database();
        $db->prepareExecute("DELETE FROM tbProjectEx WHERE sUserId = ? AND sProjectName = ?",array(
            $sUserId,
            $sProjectName
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
    public function updateProjectToDb($user_id, $sProjectId, $sCategoryName, $sDescription)
    {
        $db = get_database();
        $db->prepareExecute(combine_update_prepare_sql('tbProjectEx',array(
            'sCategoryName',
            'sDescription'
        ),array(
            'sId',
            'sUserId'
        )),array(
            $sCategoryName,
            $sDescription,
            $sProjectId,
            $user_id
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
    public function concatProjectListDetailFromDb( $arrParams)
    {
        $db = get_database();
        $sql = \tool\module\Tool::select( $arrParams);
        $result = $db->prepareExecuteAll($sql);
        return $result;
    }
    public function insertProjectToDb($where, $values)
    {
        $db = get_database();
        $sql = combine_insert_prepare_sql('tbProjectEx',$where);

        $db->prepareExecute($sql, $values);
        if ($db->getAffectRow()>0){
            return true;
        }else{
            return false;
        }
    }

    public function getLatestProject(){
        $db = get_database();
        $sql = "SELECT `sId`, `sUserId`, `sProjectName`, `sDescription`, `sCategoryName`, `createAt` FROM tbProjectEx WHERE  `createAt` != '-1' ORDER BY `createAt` DESC";
        $result = $db->prepareExecuteAll($sql);
        if ($result){
            return $result;
        }else{
            return array();
        }
    }

    public function getProjectFromDb( $where, $value){
        $db = get_database();
        $sql = combine_select_prepare_sql('tbProjectEx',array(
            '*'
        ),$where);
        $result = $db->prepareExecuteAll($sql, $value);
        if ($db->getAffectRow()>0){
            return $result;
        }else{
            return array();
        }
    }
}