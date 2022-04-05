<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/1
 * @Time: 21:08
 */

namespace project\module;


class Project
{

    /**
     * 获取最新的20个项目
     * @return array
     */
    public function getLatestProject(){
        $node = new \tool\module\Node();
        $result = $node->getProjectList();
        if ($result["success"])
        {
            return $result["data"];
        }
        else
        {
            return array();
        }
    }

    public function getAllProject()
    {
        $node = new \tool\module\Node();
        $result = $node->getAllProject();
        if ($result["success"])
        {
            return $result["data"];
        }
        else
        {
            return array();
        }
    }

    public function getAllProjectSupport()
    {
        $node = new \tool\module\Node();
        $result = $node->getAllProjectSupport();
        if ($result["success"])
        {
            return $result["data"];
        }
        else
        {
            return array();
        }
    }

    public function getSupportDetailList()
    {
        $node = new \tool\module\Node();
        $result = $node->getSupportDetailList();
        if ($result["success"])
        {
            return $result["data"];
        }
        else
        {
            return array();
        }
    }


    /**
     * 判断项目名是否可用
     * @param $name
     * @return bool
     */
    public function isValidFileName($name)
    {
        // return true;
        $reg = '/[*\\/\\?"<>|]+/';
        return preg_match($reg, $name) ? false : true;
    }

    /**
     * 项目是否已经存在
     * @param $address
     * @param $projectName
     * @return bool
     */
    public function isProjectExistByAddress($address, $projectName)
    {
        $node = new \tool\module\Node();
        $result = $node->isProjectExistByAddress($address,$projectName);
        if ($result["success"])
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function isProjectExist($userName,$projectName)
    {
        $node = new \tool\module\Node();
        $result = $node->isProjectExist($userName,$projectName);
        if ($result["success"])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function createProjectInNuls($userName,$password,$description,$projectName,$projectType)
    {
        $node = new \tool\module\Node();
        return $node->createProject($userName,$password,$description,$projectName,$projectType);
    }

    public function getUserProject($address,$project_list)
    {
        $list = array();
        foreach ($project_list as $project)
        {
            $list[] = "{$address}/{$project["name"]}";
        }
        $node = new \tool\module\Node();
        return $node->getUserProject($list);
    }

    public function getSupportDetail($tx)
    {
        $node = new \tool\module\Node();
        return $node->getSupportDetail($tx);
    }

    public function getSupportCount($userName, $projectName)
    {
        $node = new \tool\module\Node();
        return $node->getProjectSupport($userName,$projectName);
    }

    public function vote($userName,$password,$projectAuthor,$projectName,$voteCount)
    {
        $node = new \tool\module\Node();
        return $node->voteProject($userName,$password,$projectAuthor,$projectName,$voteCount);
    }

    public function getProjectInfo($userName,$projectName)
    {
        $node = new \tool\module\Node();
        return $node->getProjectInfo($userName,$projectName);
    }

    public function updateProject($userName,$password,$description,$projectName,$projectType)
    {
        $node = new \tool\module\Node();
        return $node->updateProject($userName,$password,$description,$projectName,$projectType);
    }
    public function deleteProjectInNuls($userName,$password,$projectName)
    {
        $node = new \tool\module\Node();
        return $node->deleteProject($userName,$password,$projectName);
    }

    public function deleteProject($sAddress, $sProjectName)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->removeProject($sAddress, $sProjectName);
    }
    public function createDir($sAddress, $sProjectName, $sPath)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->projectMkdir($sAddress,$sProjectName,$sPath);
    }
    public function getProjectList($sAddress)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->getProjectList($sAddress);
    }

    public function getProjectStatus($sAddress,$projectName)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->getProjectStatus($sAddress,$projectName);
    }

    public function getProjectDetail($sAddress, $sProjectName, $sPath)
    {
        $ipfs = new \tool\module\Ipfs();
        return $ipfs->getProjectInfo($sAddress,$sProjectName,$sPath);
    }

    /**
     * IPFS上创建项目
     * @param $sAddress
     * @param $sProjectName
     * @param string $fullFilePath
     * @return mixed
     */
    public function createProject( $sAddress, $sProjectName, $fullFilePath = '')
    {
        $ipfs = new  \tool\module\Ipfs();
        if ($fullFilePath == '') {
            if ($ipfs->checkProjectIsExist($sAddress,$sProjectName))
            {
                return 1;
            }
            else
            {
                return $ipfs->createProject($sAddress,$sProjectName);
            }
        } else {
            return $ipfs->uploadFiles($sAddress,$sProjectName,$fullFilePath);
        }
    }





























    /**
     * 查询项目是否存在
     * @param $sUserId
     * @param $sProjectName
     * @return bool
     */
    public function checkProjectIsExist($sUserId, $sProjectName)
    {
        $dao = new \project\dao\Project();
        return $dao->checkProjectIsExist($sUserId, $sProjectName);
    }
    public function getAllChainProject()
    {
        $ipfs = new \tool\module\Ipfs();
        $arrUser = $ipfs->getAllProject();
        $arrRet = array();
        if ($arrUser)
        {
            foreach ($arrUser as $oUser) {
                $sWalletAddress = $oUser['name'];
                $arrUserProject = self::getProjectList($sWalletAddress);
                if (count($arrUserProject) > 0) {
                    // 有结果
                    $item = array();
                    $item['sWalletAddress'] = $sWalletAddress;
                    $item['arrProject'] = $arrUserProject;
                    $arrRet[] = $item;
                }
            }
        }
        return $arrRet;

    }










    public function concatProjectListDetailFromDb( $sUserId, $arrProject)
    {
        $arrProjectName = array_map(function ($item) {
            // 要urldecode，以前存项目名称留下的痛
            return "'" . urldecode($item['name']) . "'";
        }, $arrProject);

        $arrParams = array(
            "table" => 'tbProjectEx',
            "ins" => array(
                'sProjectName' => implode(', ', $arrProjectName)
            ),
            "wheres" => array(
                'sUserId' => $sUserId
            )
        );
        $dao = new \project\dao\Project();
        $arrRet =  $dao->concatProjectListDetailFromDb($arrParams);

        // 开始php代码拼接
        $map = array();
        $arrMerge = array();

        // 处理成map
        foreach ($arrRet as $key => $item) {
            // 编码过的
            $sName = $item['sProjectName'];
            if (!$map[$sName]) {
                $map[$sName] = $item;
            }
        }

        // 遍历
        foreach ($arrProject as $key => $item) {
            $sName = $item['name'];
            // 需要编码
            $sName = urldecode($sName);
            if ($map[$sName]) {
                // 不知道为什么
                $item['createAt'] = $map[$sName]['createAt'];
                $item['sDescription'] = $map[$sName]['sDescription'];
                $item['sProjectId'] = $map[$sName]['sId'];
            }
            $arrMerge[$key] = $item;
        }

        return $arrMerge;
    }


    public function getProjectFromDb( $where, $value)
    {
        $dao = new \project\dao\Project();
        return $dao->getProjectFromDb($where, $value);
    }







    public function insertProjectToDb( $sUserId, $sProjectName, $sDescription, $sCategoryName, $bPublic, $sProjectPath)
    {
        $where = array(
            'sId',
            'sUserId',
            'sProjectName',
            'sDescription',
            'sCategoryName',
            'bPublic',
            'sProjectPath',
            'createAt'
        );

        $values = array(
            \tool\module\Tool::uuid(),
            $sUserId,
            $sProjectName,
            $sDescription,
            $sCategoryName,
            $bPublic,
            $sProjectPath,
            time()
        );

        $dao = new \project\dao\Project();
        return $dao->insertProjectToDb($where, $values);
    }

    public function getProjectID($user_id,$projectName)
    {
        $dao = new \project\dao\Project();
        return $dao->getProjectID($user_id,$projectName);
    }

   
}