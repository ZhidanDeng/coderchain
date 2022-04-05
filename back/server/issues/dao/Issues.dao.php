<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/28
 * @Time: 23:45
 */

namespace issues\dao;


class Issues
{

    /**
     * 检查是否是项目主人
     */
    public function checkIsProjectOwner($user_id,$project_id)
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT * FROM tbprojectex WHERE sId = ? AND sUserId = ?",array(
            $project_id,
            $user_id
        ));
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
     * 检查是否是issues的创建者或项目主人
     */
    public function checkIsIssuesOwner($issueID,$createUser )
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT * FROM issues WHERE issueID = ? AND createUser= ?",array(
            $issueID,
            $createUser
        ));
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
     * 检查是否是issues的创建者或项目主人
     */
    public function checkIsCommentOwner($issueID,$createUser,$comment_id )
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT * FROM issue_comment WHERE issueID = ? AND createUser= ? AND commentID = ?",array(
            $issueID,
            $createUser,
            $comment_id
        ));
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
     * 获取issues列表
     * @param $project_id
     * @param $status
     * @return array
     */
    public function getIssuesList($project_id,$status)
    {
        $db = get_database();
        if ($status == -1)
        {
            $result = $db->prepareExecuteAll("SELECT issueID,title,type,createUser,status,createTime,level FROM issues WHERE projectID = ? ",array(
                $project_id
            ));
        }
        else
        {
            $result = $db->prepareExecuteAll("SELECT issueID,title,type,createUser,status,createTime,level FROM issues WHERE projectID = ? AND status = ?",array(
                $project_id,
                $status
            ));
        }

        if ($result)
        {

            return $result;
        }
        else
        {
            return array();
        }
    }

    /**
     * 获取issues信息
     * @param $issueID
     * @return bool
     */
    public function getIssuesInfo($issueID,$project_id,$user_name)
    {
        $db = get_database();
        $result = $db->prepareExecute("SELECT * FROM issues WHERE issueID = ? AND projectID=?",array(
            $issueID,
            $project_id
        ));
        if ($result)
        {
            $comments = $db->prepareExecuteAll("SELECT * FROM issue_comment WHERE issueID = ?",array(
                $issueID
            ));
            if ($comments)
            {
                foreach ($comments as &$com)
                {
                    if (!$com['content'])
                    {
                        $com['content'] = "";
                    }
                    if ($user_name == $com['createUser'])
                    {
                        $com['isCreator'] = true;
                    }
                    else
                    {
                        $com['isCreator'] = false;
                    }
                }

                $result['comments'] = $comments;
            }
            else
            {
                $result['comments'] = array();
            }
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
     * 增加Issues
     * @param $projectID
     * @param $title
     * @param $content
     * @param $type
     * @param $creater_name
     * @param $stauts
     * @param $level
     * @return bool|int
     */
    public function addIssues($projectID,$title,$content,$type,$creater_name,$status,$level)
    {
        $db = get_database();
        $db->prepareExecute(combine_insert_prepare_sql("issues",array(
            'projectID',
            'title',
            'content',
            'type',
            'createUser',
            'status',
            'createTime',
            'level'
        )),array(
            $projectID,
            $title,
            $content,
            $type,
            $creater_name,
            $status,
            date("Y-m-d H:i:s", time()),
            $level

        ));
        if ($db->getLastInsertID()>0)
        {
            return intval($db->getLastInsertID());
        }
        else
        {
            return false;
        }
    }

    /**
     * 修改Issues
     * @param $issueID
     * @param $title
     * @param $content
     * @param $type
     * @param $status
     * @param $level
     * @return bool
     */
    public function editIssues($issueID,$title,$content,$type,$status,$level,$project_id)
    {
        $db = get_database();
        $db->prepareExecute(combine_update_prepare_sql("issues",array(
            'title',
            'content',
            'type',
            'status',
            'level'
        ),array(
            'issueID',
            'projectID'
        )),array(
            $title,
            $content,
            $type,
            $status,
            $level,
            $issueID,
            $project_id
        ));
        return true;
    }

    /**
     * 更新issues状态，创建者或项目主人有权限
     */
    public function updateIssuesStatus($issueID,$status,$project_id)
    {
        $db = get_database();
        $db->prepareExecute(combine_update_prepare_sql("issues",array(
            'status'
        ),array(
            'issueID',
            'projectID'
        )),array(
            $status,
            $issueID,
            $project_id
        ));
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 批量更新issues状态，仅项目主人有权限
     */
    public function batchUpdateIssuesStatus($status, $issueID,$project_id)
    {
        if(!empty($issueID))
        {
            $db = get_database();
            $db->prepareExecuteALL("UPDATE issues SET status = ? WHERE issueID IN ({$issueID}) AND projectID = ?",array(
                $status,
                $project_id
            ));
            if ($db->getAffectRow()>0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }


    /**
     * 删除Issues
     */
    public function deleteIssues($issueID,$project_id)
    {
        $db = get_database();
        $db->prepareExecuteALL("DELETE FROM issues WHERE issueID = ? AND projectID = ?",array(
            $issueID,
            $project_id
        ));

        if ($db->getAffectRow()>0)
        {
            $db->prepareExecuteALL("DELETE FROM issue_comment WHERE issueID = ?",array(
                $issueID
            ));
            return true;
        }
        else
        {
            return false;
        }
    }

    public function batchDeleteIssues($issue_ids,$project_id)
    {
        $db = get_database();
        try
        {
            $db->beginTransaction();
            if(!empty($issue_ids))
            {
                $db = get_database();
                $db->prepareExecuteALL("DELETE FROM issues WHERE issueID IN ({$issue_ids}) AND projectID = ?",array(
                    $project_id
                ));
            }
            return true;
        }
        catch (\PDOException $e)
        {
            $db->rollback();
            return false;
        }


    }

    /**
     * 评论
     */
    public function addComment($issue_id,$content,$create_user)
    {
        $db = get_database();
        $db->prepareExecute(combine_insert_prepare_sql("issue_comment",array(
            'issueID',
            'content',
            'createUser',
            'createTime'
        )),array(
            $issue_id,
            $content,
            $create_user,
            date("Y-m-d H:i:s", time())
        ));
        if ($db->getLastInsertID()>0)
        {
            return intval($db->getLastInsertID());
        }
        else
        {
            return false;
        }
    }

    /**
     * 修改评论
     */
    public function editComment($comment_id,$content,$issue_id)
    {
        $db = get_database();
        $db->prepareExecute(combine_update_prepare_sql("issue_comment",array(
            'content'
        ),array(
            'commentID',
            'issueID'
        )),array(
            $content,
            $comment_id,
            $issue_id
        ));
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 删除评论
     */
    public function deleteComment($comment_id,$issue_id)
    {
        $db = get_database();
        $db->prepareExecuteALL("DELETE FROM issue_comment WHERE commentID = ? AND issueID = ?",array(
            $comment_id,
            $issue_id
        ));
        if ($db->getAffectRow()>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


}