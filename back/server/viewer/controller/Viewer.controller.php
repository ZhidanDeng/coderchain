<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 21:54
 */
namespace viewer\controller;


class Viewer
{
    public function getPreviewer()
    {
        $hash = securely_input("sHash");
        $file_name = securely_input("sFileName");

        $s_url = IPFS_VIEW."/ipfs/{$hash}";
        $s_content = file_get_contents($s_url);
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($s_content));

        // 记住中文名需要编码
        header('Content-Disposition: attachment; filename=' . urlencode($file_name));
        header("Pragma: no-cache");
        header("Expires: 0");

        exit_output($s_content);
    }

    public function getDownLoad()
    {
        $hash = securely_input("sHash",'QmVb7NK1cYt8wYL4u6tRJnfFqW9KAF86FVFBcUaC72Udop');
        $file_name = securely_input("sFileName","系统功能图.png");

        $s_url = IPFS_VIEW."/ipfs/{$hash}";
        $s_content = file_get_contents($s_url);
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($s_content));

        // 记住中文名需要编码
        header('Content-Disposition: attachment; filename=' . urlencode($file_name));

        exit_output($s_content);
    }

}