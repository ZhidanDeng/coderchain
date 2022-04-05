<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 22:04
 */

namespace category\controller;


class Category
{
    private $log_path = "/ex/category/";

    private $logger = null;

    //返回json类型
    private $return_json = array('type' => 'category');



    public function __construct()
    {
        $this->logger = new \log\module\Logger($this->log_path);
    }
    
    public function insertCategory()
    {
        $category_name = securely_input("sCategoryName");
        $module = new \category\module\Category();
        if ($module->isCategoryExist($category_name))
        {
            $this->return_json['retCode'] = 100;
            $this->return_json['retMsg'] = '分类已经存在';
        }
        else
        {
            $result = $module->addCategory($category_name);
            if ($result)
            {
                $this->return_json['retCode'] = 0;
                $this->return_json['retMsg'] = '添加分类成功';
            }
            else
            {
                $this->return_json['retCode'] = 100;
                $this->return_json['retMsg'] = '添加分类失败';
            }
        }
        exit_output($this->return_json);
    }
    
    public function getAllCategory()
    {
        $module = new \category\module\Category();
        $result = $module->getAllCategory();
        if ($result)
        {
            $arr_res = array_map(function($item) {
                return $item['sCategoryName'];
            }, $result);
        }
        else
        {
            $arr_res = array();
        }
        $this->return_json['retCode'] = 0;
        $this->return_json['retMsg'] = '查询分类成功';
        $this->return_json['oRet'] = $arr_res;
        exit_output($this->return_json);
    }
}