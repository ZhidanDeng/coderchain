<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 22:09
 */

namespace category\dao;


class Category
{
    public function isCategoryExist($category_name)
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT COUNT(*) num FROM tbCategoryEx WHERE sCategoryName = ?",array(
            $category_name
        ));
        if ($result['num']>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function addCategory($category_name){
        $db = get_database();
        $db->prepareExecute(combine_insert_prepare_sql('tbCategoryEx',array(
            'sId',
            'sCategoryName',
            'createAt'
        )),array(
            \tool\module\Tool::uuid(),
            $category_name,
            time()
        ));
        if ($db->getAffectRow()>0){
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getAllCategory()
    {
        $db = get_database();
        $result = $db->prepareExecuteAll("SELECT * FROM tbCategoryEx WHERE '1'='1' ORDER BY `createAt` ASC");
        if ($result)
        {
            return $result;
        }
        else
        {
            return array();
        }
    }
}