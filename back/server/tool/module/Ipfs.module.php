<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/8/1
 * @Time: 22:30
 */

namespace tool\module;


class Ipfs
{
    /**
     * 获取用户的项目路径
     * @param $address
     * @return string
     */
    public function getUserHomePath($address,$project_name = null)
    {
        if ($project_name)
        {
            return "/coderchain/{$address}/{$project_name}";

        }
        else
        {
            return "/coderchain/".$address;
        }

    }

    /**
     * 判断用户路径是否在ipfs上
     * @param $address
     * @return bool
     */
    public function checkUserIsExistInIpfs($address)
    {
        $project_path = $this->getUserHomePath($address);

        $result = $this->getFileStat($project_path);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function createUser($address)
    {
        $project_path = $this->getUserHomePath($address);
        $res = $this->createPath($project_path);
        if ($res)
        {
            // 创建成功
            return $res;

        }
        else
        {
            // 文件夹已存在
            return false;
        }
    }


    /**
     * 判断项目/文件是否存在，存在返回true
     * @param $address
     * @param $project_name
     * @param null $dir_path
     * @return bool
     */
    public function checkProjectIsExist($address, $project_name,$dir_path=null)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        if ($dir_path)
        {
            $project_path = $project_path . '/' .$dir_path;
        }
        $result = $this->getFileStat($project_path);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * 创建项目目录，但没有初始化文件
     * /project/create/:sAddress
     */
    public function createProject($address, $project_name)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        $res = $this->createPath($project_path);
        if ($res)
        {
            // 创建成功
            return $res;

        }
        else
        {
            // 文件夹已存在
            return false;
        }
    }

    /**
     * 删除项目
     * @param $address
     * @param $project_name
     * @return bool
     * /project/removeProject/:sAddress
     */
    public function removeProject($address, $project_name)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        $result = $this->removePath($project_path);
        if ($result)
        {
            return true;
        }
        else
        {
            // 删除失败
            return false;
        }
    }

    /**
     * 获取用户所有的项目列表
     * /project/list/:sAddress
     */
    public function getProjectList($address)
    {
        $user_path = $this->getUserHomePath($address);
        return $this-> getProjectDetail($user_path);
    }
    public function getProjectStatus($sAddress,$projectName)
    {
        $user_path = $this->getUserHomePath($sAddress,$projectName);
        return $this->getFileStat($user_path);
    }

    /**
     * 更新文件内容
     * @param $address
     * @param $project_name
     * @param $file_path
     * @param $data
     * @return bool
     * /project/file/:sAddress
     */
    public function updateFileContent($address,$project_name,$file_path,$data)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        if ($file_path)
        {
            $file_real_path = $project_path . "/" . $file_path;
        }
        else
        {
            return false;
        }
        $result = $this->write($file_real_path, $data);
        return $result;
    }

    /**
     * 创建项目中的目录
     * /project/mkdir/:sAddress
     */
    public function projectMkdir($address,$project_name,$dir_path)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        $dir_real_path = $project_path . "/" . $dir_path;
        return $this->createPath($dir_real_path);
    }

    /**
     * 删除项目内的文件与目录
     * @param $address
     * @param $project_name
     * @param $dir_path
     * @return bool
     * /project/remove/:sAddress
     */
    public function projectRemove($address,$project_name,$dir_path)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        $dir_real_path = $project_path . "/" . $dir_path;
        return $this->removePath($dir_real_path);
    }

    /**
     * 通过项目的目录获取目录中的内容
     * @param $address
     * @param $project_name
     * @param $dir_path
     * @return array
     * /project/dir/:sAddress
     */
    public function getProjectInfo($address,$project_name,$dir_path)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        if ($dir_path)
        {
            $dir_real_path = $project_path . "/" . $dir_path;
        }
        else
        {
            $dir_real_path = $project_path;
        }


        return $this->getProjectDetail($dir_real_path);
    }

    /**
     * 获取所有项目列表
     * @return array
     * /project/all
     */
    public function getAllProject()
    {
        return $this->getProjectDetail('/coderchain');
    }

    /**
     * 将文件夹上传到IPFS(MFS)上，假如项目已存在会进行删除，如果不存在则会创建这个项目，并将文件夹内容移入,其实就是项目初始化
     * /project/files/:sAddress
     */
    public function uploadFiles($address,$project_name,$file_path)
    {
        return $this->cpFileAndResetProject($file_path,$address,$project_name);
    }

    /**
     * 提交图片资源
     * @param $address
     * @param $project_name
     * @param $source_path
     * @param $file_path
     * @param $file_name
     * @return bool
     * /project/res/:sAddress
     */
    public function uploadRes($address,$project_name,$source_path,$file_path,$file_name)
    {
        $project_path = $this->getUserHomePath($address, $project_name);
        if ($file_path)
        {
            $file_real_path = $project_path . "/" .$file_path . "/" . $file_name;
        }
        else
        {
            $file_real_path = $project_path .  "/" . $file_name;
        }
        return $this->cpRes($source_path,$file_real_path);
    }




    /**
     * 添加和复制源文件
     * @param $sSourcePath
     * @param $sPath
     * @param $sResName
     * @return bool
     */
    public function cpRes($sSourcePath, $sPath)
    {
        $hash = $this->add($sSourcePath);
        if ($hash)
        {
            return $this->cp($hash, $sPath);
        }
        else
        {
            return false;
        }
    }

    /**
     * 初始化项目
     * @param $sSourcePath
     * @param $sAddress
     * @param $sProjectName
     * @return bool
     */
    public function cpFileAndResetProject($sSourcePath, $sAddress, $sProjectName)
    {
        $project_path = $this->getUserHomePath($sAddress, $sProjectName);
        $hash = $this->add($sSourcePath);
        if ($hash)
        {
            $this->removePath($project_path);
            $result = $this->cp($hash,$project_path);
            return $result;
        }
        else
        {
            return false;
        }
    }


    /**
     * 路径创建
     * @param $path
     * @return bool
     */
    public function createPath($path)
    {
        $tool = new \tool\module\Tool();
        $arg = urlencode($path);
        $address = IPFS_API."/files/mkdir?arg=$arg";
        // 创建地址
        $res = $tool->post_ipfs($address);
        $res = json_decode($res,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        else
        {
            $result = $this->getFileStat($path);
            if($result)
            {
                return $result['Hash'];
            }
            else
            {
                return false;
            }

        }
    }




    /**
     * 获取目录/文件状态
     * @param $path
     * @return bool|mixed
     */
    public function getFileStat($path)
    {
        $tool = new \tool\module\Tool();
        // url编码
        $arg = urlencode($path);
        $url = IPFS_API."/files/stat?arg={$arg}&stream-channels=true";
        // 创建地址
        $res = $tool->post_ipfs($url);
        $res = json_decode($res,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        return $res;
    }

    /**
     * 移除文件/文件夹
     * @param $path
     * @return bool
     */
    public function removePath($path)
    {
        $tool = new \tool\module\Tool();
        // url编码
        $arg = urlencode($path);
        $url = IPFS_API."/files/rm?arg={$arg}&stream-channels=true&recursive=true";
        $res = $tool->post_ipfs($url);
        $res = json_decode($res,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 根据路径获取项目的目录信息
     * @param $path
     * @return array
     */
    public function getProjectDetail($path)
    {
        $project_list = $this->getPathList($path);
        if ($project_list)
        {
            $result = array();
            foreach ($project_list as $value)
            {
                $pro =  $this->getFileStat($path ."/" .$value['Name']);
                $pro['name'] = $value['Name'];
                $result[] = $pro;
            }
            return $result;
        }
        else
        {
            return array();
        }
    }

    /**
     * 获取指定路径的列表，成功返回Entries对象，失败提示项目不存在
     * @param $path
     * @return mixed
     */
    public function getPathList($path)
    {
        $tool = new \tool\module\Tool();
        $arg = urlencode($path);
        $address = IPFS_API."/files/ls?arg={$arg}";
        // 创建地址
        $res = $tool->post_ipfs($address);
        $res = json_decode($res,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        else
        {
            if ($res['Entries'])
            {
                return $res['Entries'];
            }
            else
            {
                return array();
            }
        }
    }

    /**
     * 往某个文件写内容
     */
    public function write($path, $content,$truncate = true)
    {
        $arg = urlencode($path);
        $url = IPFS_API."/files/write?truncate={$truncate}&stream-channels=true&create=true&arg={$arg}";
        $delimiter = uniqid();
        $data = '';
        $eol = "\r\n";
        // 拼接文件流
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="data";' . "\r\n"
            . 'Content-Type:application/octet-stream'."\r\n\r\n";

        $data .= $content . "\r\n";
        $data .= "--" . $delimiter . "--\r\n";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: multipart/form-data; boundary=" . $delimiter,
            "Content-Length: " . strlen($data)
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($response,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 将一个ipfs文件cp到指定MFS目录中
     * @param $hash
     * @param $target_path
     * @return bool
     */
    public function cp($hash, $target_path)
    {
        $tool = new \tool\module\Tool();
        // url编码
        $arg1 = urlencode("/ipfs/{$hash}");
        $arg2 = urlencode($target_path);
        $url = IPFS_API."/files/cp?arg={$arg1}&arg={$arg2}&stream-channels=true";
        $res = $tool->post_ipfs($url);
        $res = json_decode($res,true);
        if ($res['Type'] == 'error')
        {
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * 添加文件/文件夹到ipfs中
     * @param $path
     * @return bool|mixed
     */
    public function add($path)
    {
        $fields = $this->getFieldByPath($path);
        $url = IPFS_API."/add?recursive=true&stream-channels=true&pin=false&progress=true&wrap-with-directory=false";
        // 创建地址
        $res = $this->putPart($fields,$url);
        $res_error = json_decode($res,true);
        if ($res_error['Type'] == 'error')
        {
            return false;
        }
        else
        {
            // 正则暴力匹配所有哈希，返回最后一个
            $param_content = array();
            if (preg_match_all("/[\"]Hash[\"]\s{0,}:\s{0,}[\"]([\\s\\S]*?)[\"]/", $res, $param_content)>0)
            {
                return end($param_content[1]);
            }
            else
            {
                return false;
            }
        }

    }



    /**
     * 获取文件流参数
     * @param $path 文件的实际路径
     * @return array
     */
    private function getFieldByPath($path)
    {
        $arr = explode("/", $path);
        $root = end($arr);
        $result = array();
        if (is_dir($path))
        {
            $this->scan($path,$root,$result);
        }
        else
        {
            $result[] = array(
                'filename'=>urlencode($root),
                'data'=>file_get_contents($path)
            );
        }
        foreach ($result as $key => &$value)
        {
            $value['name'] = "file-{$key}";
        }

        return $result;
    }

    private function putPart($param,$url) {
        $delimiter = uniqid();
        $post_data = "";
        foreach ($param as $field)
        {
            $post_data .= "--" . $delimiter . "\r\n";
            $post_data .= $this->buildData($field,$delimiter);
        }
        $post_data .= "--" . $delimiter . "--\r\n";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: multipart/form-data; boundary=" . $delimiter,
            "Content-Length: " . strlen($post_data)
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function buildData($param){
        $data = '';
        $upload = $param['data'];
        unset($param['data']);
        // 拼接文件流
        $data .= "Content-Disposition: form-data; name={$param['name']}; filename={$param['filename']}" . "\r\n"
                . 'Content-Type:application/octet-stream'."\r\n\r\n";

        $data .= $upload . "\r\n";

        return $data;
    }

    private function scan($dir, $root,&$result){
        $dirArr = scandir($dir);
        foreach($dirArr as $v){
            if($v!='.' && $v!='..'){
                $dir_name = $dir."/".$v;
                if(is_dir($dir_name))
                {
                    $this->scan($dir_name,$root . '/' .$v, $result);
                }
                else
                {
                    $data = file_get_contents($dir_name);
                    $result[] = array(
                        'filename'=>urlencode($root . '/' .$v),
                        'data'=>$data
                    );
                }
            }
        }
    }
}