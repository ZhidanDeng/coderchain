<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 22:07
 */
namespace category\module;

class Category
{
    public function isCategoryExist($category_name)
    {
        $dao = new \category\dao\Category();
        return $dao->isCategoryExist($category_name);
    }

    public function addCategory($category_name)
    {
        $dao = new \category\dao\Category();
        return $dao->addCategory($category_name);
    }

    public function getAllCategory()
    {
        $dao = new \category\dao\Category();
        return $dao->getAllCategory();
    }
}