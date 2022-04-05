<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 0:07
 */

namespace dynamic\module;

class Dynamic
{
    public function select($arr_param){
        $db = get_database();
        $sql = \tool\module\Tool::select($arr_param);
        $result = $db->prepareExecuteAll($sql);
        if ($result){
            return $result;
        }else{
            return array();
        }
    }
}