<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 0:04
 */

namespace tool\module;



class Tool
{
    public static function select($arrParams)
    {
        $fields = $arrParams['fields'];
        $wheres = $arrParams['wheres'];
        $limit = $arrParams['limit'];
        $ins = $arrParams['ins'];
        $table = $arrParams['table'];
        $order = $arrParams['order'];


        $strOrder = '';

        if (!$fields) {
            $fields = '*';
        }

        if ($fields == '*') {
            $strField = '*';
        } else {
            $fields = array_map(function ($field) {
                return "`{$field}`";
            }, $fields);
            $strField = implode(', ', $fields);
        }

        $arrTemp = array();

        if (isset($wheres) && ($wheres['$eq'] || $wheres['$neq'] || $wheres['$gt'] || $wheres['$lt'])) {

            $eq = $wheres['$eq'];
            $neq = $wheres['$neq'];
            $gt = $wheres['$gt'];
            $lt = $wheres['$lt'];

            foreach ($eq as $key => $value) {
                $arrTemp[] = "`{$key}`" . "=" . "'{$value}'";
            }
            foreach ($neq as $key => $value) {
                $arrTemp[] = "`{$key}`" . "!=" . "'{$value}'";
            }
            foreach ($gt as $key => $value) {
                $arrTemp[] = "`{$key}`" . ">" . "'{$value}'";
            }
            foreach ($lt as $key => $value) {
                $arrTemp[] = "`{$key}`" . "<" . "'{$value}'";
            }
        } else {
            if ($wheres)
            {
                foreach ($wheres as $key => $value) {
                    $arrTemp[] = "`{$key}`" . "=" . "'{$value}'";
                }
            }
        }

        $strWhere = implode(' AND ', $arrTemp);

        $arrTemp = array();
        foreach ($ins as $key => $value) {
            $arrTemp[] = "`{$key}`" . " in " . "({$value})";
        }
        $strIn = implode(' AND ', $arrTemp);

        if ($strIn) {
            if ($strWhere) {
                $strWhere = $strWhere . ' AND ' . $strIn;
            } else {
                $strWhere = $strIn;
            }
        }

        if ($order && $order['field']) {
            $field = $order['field'];
            $type = isset($order['type']) ? $order['type'] : 'ASC';
            // 不指定排序方式，默认是增序 ASC
            // 逆序 DESC
            $strOrder = "ORDER BY `{$field}` {$type}";
        }

        if (!$strWhere) {
            $strWhere = "'1'='1'";
        }

        $sSql = "SELECT {$strField} FROM {$table} WHERE {$strWhere}";

        if ($strOrder) {
            $sSql = $sSql . " {$strOrder}";
        }

        if (isset($limit)) {
            $sSql = $sSql . " LIMIT {$limit}";
        }

        return $sSql;
    }

    public static function uuid()
    {
        $prefix = '';
        $divider = '';
        $chars = md5(uniqid(rand(), true));
        $uuid  = substr($chars, 0, 8)  .  $divider;
        $uuid .= substr($chars, 8, 4)  .  $divider;
        $uuid .= substr($chars, 12, 4) .  $divider;
        $uuid .= substr($chars, 16, 4) .  $divider;
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    public function post_ipfs($url,$data=array()){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;

    }

    // json post请求
    public function post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response,true);
    }


    public function drawImage($sText)
    {
        // $sText = mb_convert_encoding($sText, 'utf-8');
        // $sText = iconv('gbk', 'utf-8', $sText);
        $sText = strtoupper($sText);
        $iSize = 50;
        $iImgWidth = 100;
        $iImgHeight = 100;
        $img = imagecreate($iImgWidth, $iImgHeight);
        // $sFont = "/data/coderchain-static-files/fonts/consola.ttf";
        $sFont = STATIC_FONTS . "/simhei.ttf";
        imagecolorallocate($img, 0xff, 0xff, 0xff);
        $black = imagecolorallocate($img, 0x14, 0x4f, 0xbc);

        // 计算位置
        $box = imagettfbbox($iSize, 0, $sFont, $sText);
        $iTextWidth = $box[2] - $box[0];
        $iTextHeight = $box[1] - $box[7];

        // x坐标是以文字左边开始算的
        // y坐标是以文字底部开始算的
        $x = ($iImgWidth - $iTextWidth) / 2;
        // 100 = 2x + sTextWidth => x => y = 100- x
        $y = $iImgHeight - ($iImgHeight - $iTextHeight) / 2;

        if (strlen($sText) == 3) {
            // 中文字符，需要另外调位置
            $x = $x - 3;
            $y = 75;
        }

        imagettftext($img, $iSize, 0, $x, $y, $black, $sFont, $sText);
        return $img;
    }

    public function saveImage($img, $sFilePath)
    {
        return imagejpeg($img, $sFilePath);
    }

    /**
     * 获取状态码
     */
    public function getHttpCodeForSwagger($url)
    {
        $data = get_headers($url, 1);
        $data = json_encode($data);
        if(preg_match('/200 OK/', $data))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 根据路径获取内容
     * @param string $base_url 内容基本路径
     * @param string $url 内容路径
     * @param string $param 请求参数
     * @param array $api_content_result 获取的内容数组
     * @param int $repository_type 仓库类型
     * @param $private_token string 私钥
     * @param $owner string 空间名/用户名
     * @param $language string 语言类型
     */
    public function getContentForSwagger($base_url, $url, $param, &$api_content_result, $repository_type, $private_token, $owner, $language)
    {
        $api_dir_url = "";
        if ($url == "/")
        {
            if ($repository_type == 0)
            {
                $api_dir_url = $base_url."/tree".$param;
            }
            elseif ($repository_type == 1)
            {
                $api_dir_url = $base_url . $param;
            }
            elseif ($repository_type == 2)
            {
                $api_dir_url = $base_url . $param;
            }
        }
        else
        {
            // 去掉前面的反斜杠
            $url = ltrim($url, "/");
            if ($repository_type == 0)
            {
                $api_dir_url =$base_url."/tree".$param."&path=".$url;
            }
            elseif ($repository_type == 1)
            {
                $api_dir_url = $base_url . "/" . $url . $param;
            }
            elseif ($repository_type == 2)
            {
                $api_dir_url = $base_url . "/" . $url . $param;
            }
        }
        $api_data = $this->getUrlContentForSwagger($api_dir_url, 10, $repository_type, $private_token, $owner);
        $api_data = json_decode($api_data, TRUE);
        if(is_array($api_data))
        {
            foreach($api_data as $api)
            {
                if($api["type"] == "tree" || $api['type'] == "dir")
                {
                    $path = $api["path"];
                    $this->getContentForSwagger($base_url, $path, $param, $api_content_result, $repository_type, $private_token, $owner, $language);
                }
                elseif($api["type"] == "blob" && $repository_type == 0)
                {
                    if ($language == 0 && substr($api['path'],-4) == ".php")
                    {
                        $file_path = $base_url ."/files/" .urlencode($api['path']) .$param;
                        $api_content_result[] = $file_path;
                    }
                    elseif($language == 1 && substr($api['path'],-5) == ".java")
                    {
                        $file_path = $base_url ."/files/" .urlencode($api['path']) .$param;
                        $api_content_result[] = $file_path;
                    }
                    else
                    {
                        continue;
                    }
                }
                elseif ($api['type'] == "file" && ($repository_type == 1 || $repository_type == 2))
                {
                    if ($language == 0 && substr($api['path'],-4) == ".php")
                    {
                        $file_path = $base_url ."/" . $api['path'] .$param;
                        $api_content_result[] = $file_path;
                    }
                    elseif($language == 1 && substr($api['path'],-5) == ".java")
                    {
                        $file_path = $base_url ."/" . $api['path'] .$param;
                        $api_content_result[] = $file_path;
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
            }
        }
    }


    /**
     * 读取远程文件内容
     */
    public function getUrlContentForSwagger($url, $timeout, $repository_type, $private_token, $owner, $decode = false)
    {

        // 初始化
        $curl = curl_init();
        // 设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置头文件的信息作为数据流输出
        if ($repository_type == 1)
        {
            $oauth = base64_encode("Authorization:" . $private_token);
            $header = array(
                "authorization: Basic ".$oauth, // 关键
                "content-type: application/json",
                "User-Agent: {$owner}" // 用户名
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        else
        {
            curl_setopt($curl, CURLOPT_HEADER, 0);
        }
        // 设置超时
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        // 设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 跳过证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        // 执行命令
        $data = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        // 显示获得的数据


        if ($decode)
        {
            $data = json_decode($data, TRUE);
            return base64_decode($data['content']);
        }
        else
        {
            return $data;
        }

    }

    function get_nuls_params($method,$params){
        $data = array(
            "jsonrpc"=>"2.0",
            "method"=>$method,
            "params"=>$params
        );
        return json_encode($data);
    }
    function http_post_nuls($url, $method, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_nuls_params($method,$params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response,true);
    }

}