<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/18
 * @Time: 14:47
 */

namespace api\controller;


class Code
{

    //返回json类型
    private $return_json = array('type' => 'api');

    /**
     * 获取登录易对接代码文件
     */
    public function getDengLu1Code()
    {
        $language_type = securely_input("codeType");
        $path = STATIC_CODES . '/' ;
        if ($language_type == "NodeJs")
        {
            $path .= "server_demo_nodejs.zip";
        }
        elseif ($language_type == "PHP")
        {
            $path .= "server_demo_php.zip";
        }
        elseif ($language_type == "Python")
        {
            $path .= "server_demo_python27.zip";
        }
        elseif ($language_type == "C#")
        {
            $path .= "server_demo_CSharp.zip";
        }
        elseif ($language_type == "Java")
        {
            $path .= "server_demo_java.zip";
        }
        else
        {
            $this->return_json ['retCode'] = 100;
            $this->return_json ['msg'] = "请求非法";
            exit_output($this->return_json);
        }
        header("Content-type:application/zip");
        header("Accept-ranges:bytes");
        header("Content-Length: " . filesize($path));
        // 中文编码
        $file_name = "server_demo.zip";
        $file_name = iconv('utf-8', 'gb2312', $file_name);
        header("Content-Disposition:attachment; filename={$file_name}");
        echo file_get_contents($path);
    }

}