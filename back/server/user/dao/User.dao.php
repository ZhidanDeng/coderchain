<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 20:36
 */

namespace user\dao;



class User
{

    public function addFeedback($sUserName, $sContact, $sContent)
    {
        $db = get_database();
        $db->prepareExecute(combine_insert_prepare_sql('tbFeedbackEx',array(
            'sId',
            'sUserName',
            'sContact',
            'sContent',
            'createAt'
        )),array(
            \tool\module\Tool::uuid(),
            $sUserName,
            $sContact,
            $sContent,
            time()
        ));
        if ($db->getLastInsertID()>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    public function updateUserInfo( $sUserId,$params,$values)
    {
        $db = get_database();
        $sql = combine_update_prepare_sql('tbUserEx',$params,array(
            'sId'
        ));
        $values[] = $sUserId;
        $db->prepareExecute($sql,$values);
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }

    }



    public function getUser( $where, $value, $order)
    {
        $db = get_database();
        $sql = combine_select_prepare_sql('tbUserEx',array(
            '*'
        ),$where,$order);
        $result = $db->prepareExecute($sql,$value);
        if ($result['sId'])
        {
            return $result;
        }else{
            return false;
        }
    }

    public function getLatestUser()
    {
        $db = get_database();
        $sql = "SELECT `sId`, `sUserName`, `sDisplayName`, `sDescription`, `sAvatar`, `createAt` FROM tbUserEx WHERE  `createAt` != '-1' ORDER BY `createAt` DESC";
        $result = $db->prepareExecuteAll($sql);

        if ($result){
            return $result;
        }else{
            return array();
        }
    }

    public function getUserAddress($sUserId)
    {
        $db = get_database();

        $result = $db->prepareExecute(combine_select_prepare_sql('tbUserEx',array(
            '*'
        ),array(
            'sId'
        )),array(
            $sUserId
        ));
        if ($result['sWalletAddress'])
        {
            return $result['sWalletAddress'];
        }
        else
        {
            return false;
        }
    }

    public function login($user_name)
    {
        $db = get_database();
        $sql = "SELECT * FROM tbUserEx WHERE sUserName = ?";
        $result = $db->prepareExecute($sql,array($user_name));



        if ($db->getAffectRow()>0)
        {
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function isUserExisted( $user_name)
    {
        $db = get_database();
        $sql = "SELECT COUNT(*) num FROM tbUserEx WHERE sUserName = ?";
        $result = $db->prepareExecute($sql,array($user_name));


        if ($result['num'] > 0 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function registerUser( $params, $values)
    {
        $db = get_database();
        $sql = combine_insert_prepare_sql('tbUserEx',$params);
        $db->prepareExecute($sql,$values);


        if ($db->getAffectRow()>0 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}