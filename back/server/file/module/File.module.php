<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/2
 * @Time: 22:37
 */

namespace file\module;


class File
{

    public function mkdir($sPath)
    {
        // 目录不存在，创建
        if (!file_exists($sPath)) {
            mkdir($sPath, 0777, true);
            chmod($sPath, 0777);
        }
    }

    // 根据哈希获取文件内容
    static public function getFileContent($sHash)
    {
        $sUrl = IPFS_VIEW . "/ipfs/{$sHash}";
        $sData = file_get_contents($sUrl);
        return $sData;
    }

    public function scan($dir, $root,&$count){
        $dirArr = scandir($dir);
        foreach($dirArr as $v){
            if($v!='.' && $v!='..'){
                $dirname = $dir."/".$v; //子文件夹的目录地址
                if(is_dir($dirname)){
                    if ($root == '/')
                    {
                        $count['dir'][] = $v;
                        $this->scan($dirname,$v, $count);
                    }
                    else
                    {
                        $count['dir'][] = $root . '/' .$v;
                        $this->scan($dirname,$root . '/' .$v, $count);
                    }
                }
            }
        }
    }



    public function saveFile($sAddress, $sProjectName, $sFilePath, $sData)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->updateFileContent($sAddress,$sProjectName,$sFilePath,$sData);
    }

    public function deleteFile($sAddress, $sProjectName, $sPath)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->projectRemove($sAddress,$sProjectName,$sPath);
    }
    


    public function getFileExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public function convertDirFilesEncoding($path, $newEncoding = 'utf-8')
    {
        if (file_exists($path)) {
            if (is_dir($path)) {
                foreach (glob("$path/*") as $key => $value) {
                    self::convertDirFilesEncoding($value);
                }
                return;
            }

            // 这里处理各种文件类型，如果是图片
            $ext = self::getFileExtension($path);
            if (self::isResourceType($ext)) {
                return;
                // file
            }
            $sFileContent = file_get_contents($path);

            // 这里对内容进行编码转换，转换成UTF-8
            $encoding = mb_detect_encoding($sFileContent, array('GB2312', 'GBK', 'UTF-16', 'UCS-2', 'UTF-8', 'BIG5', 'ASCII', 'CP936'));


            if ($encoding != 'UTF-8') {
                // 转换编码
                $sFileContent = mb_convert_encoding($sFileContent, $newEncoding, $encoding);
                file_put_contents($path, $sFileContent);
            }
        }
    }

    public function isImage($ext)
    {
        $IMG_RESOURCE = ['jpg', 'jpeg', 'gif', 'png', 'ico'];
        return in_array(strtolower($ext), $IMG_RESOURCE);
    }

    public function isResourceType($ext)
    {
        $IMG_RESOURCE = ['jpg', 'jpeg', 'gif', 'png', 'ico', 'pdf', 'doc', 'docx', 'woff', 'ppt', 'pptx', 'xlsx', 'xls', 'mp4', 'flv', 'mp3'];
        return in_array(strtolower($ext), $IMG_RESOURCE);
    }


    public function write($sPath, $sData)
    {
        if (strpos($sPath,'/')!=false)
        {
            $position = strrpos($sPath,'/');
            $path = substr($sPath,0,$position);
            if(!file_exists($path)){
                mkdir($path,0777,true);
            }
        }
        return file_put_contents($sPath, $sData);
    }

    public function unlink($sPath)
    {
        return unlink($sPath);
    }

    public function rmdir($sPath)
    {
        if (is_dir($sPath)) {
            $p = scandir($sPath);
            if (count($p)>0)
            {
                foreach ($p as $val) {
                    // 排除.和..
                    if ($val != '.' && $val != '..') {
                        $sChildPath = $sPath . '/' . $val;
                        if (is_dir($sChildPath)) {
                            self::rmdir($sChildPath);
                            rmdir($sChildPath);
                        } else {
                            self::unlink($sChildPath);
                        }
                    }
                }
                rmdir($sPath);
            }
            return true;
        } else {
            return false;
        }
    }

    public function saveResource($sAddress, $sProjectName, $sFilePath, $sResPath, $sResName)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->uploadRes($sAddress,$sProjectName,$sResPath,$sFilePath,$sResName);
    }

    public function unzip($Url_zip, $zip_name, $To_zip)
    {
        // unzip
        $path = $Url_zip; //文件路径
        $topath = $To_zip; //存储路径
        $dir = $topath . $zip_name; //解压文件名
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (file_exists($path)) { //文件存在

            $resource = zip_open($path); //进入压缩包
            if ($resource == false) {
                return false;
                // echo ("zip打开失败");
            } else {
                while ($zip = zip_read($resource)) { //遍历项目

                    zip_entry_open($resource, $zip);
                    //处理项目名
                    $file_content = $dir . '/' . zip_entry_name($zip);
                    // +++
                    // $file_content = iconv('UTF-8', 'GB2312', $file_content);
                    //
                    $file_contentw = substr($file_content, strrpos($file_content, '/'));
                    //如果是文件夹，创建文件夹
                    if ($file_contentw == '/') {
                        mkdir($file_content, 0777, true);
                    } else { //如果不是文件夹
                        $save_path = $file_content; //存储目录
                        $file_size = zip_entry_filesize($zip); //返回文件中尺寸。
                        $file = zip_entry_read($zip, $file_size); //读取文件中内容。

                        // ...
                        // 这里处理一下文件编码
                        $file = self::convertStringEncoding($file);
                        // ...
                        //
                        // 转码
                        // $save_path = iconv('UTF-8', 'GB2312', $save_path);
                        $num = file_put_contents($save_path, $file); //写入内容
                        zip_entry_close($zip); //关闭的 zip 项目资源。
                    }
                } //while
            }
            zip_close($resource);
            return true;
        } else { // if(file_exists($path))
            // echo ("zip文件不存在");
            return false;
        }
    }

    public function convertStringEncoding($sFileContent, $newEncoding = 'utf-8')
    {
        // 这里对内容进行编码转换，转换成UTF-8
        $encoding = mb_detect_encoding($sFileContent, array('GB2312', 'GBK', 'UTF-16', 'UCS-2', 'UTF-8', 'BIG5', 'ASCII', 'CP936'));

        if ($encoding != 'UTF-8') {
            // 转换编码
            $sFileContent = mb_convert_encoding($sFileContent, $newEncoding, $encoding);
        }

        return $sFileContent;
    }

    public function filterNullDir($arrDir)
    {
        $arrDir = array_filter($arrDir, function ($dir) {
            if ($dir == '.' || $dir == '..') {
                return false;
            }
            return true;
        });

        return $arrDir;
    }



}