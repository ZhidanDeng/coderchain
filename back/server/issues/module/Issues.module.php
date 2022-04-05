<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/28
 * @Time: 23:45
 */

namespace issues\module;


class Issues
{

    /**
     * 检查是否是项目主人
     */
    public function checkIsProjectOwner($user_id,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->checkIsProjectOwner($user_id,$project_id);
    }

    /**
     * 检查是否是issues的创建者或项目主人
     */
    public function checkIsIssuesOwner($issueID,$createUser )
    {
        $dao = new \issues\dao\Issues();
        return $dao->checkIsIssuesOwner($issueID,$createUser );
    }

    /**
     * 检查是否是issues的创建者或项目主人
     */
    public function checkIsCommentOwner($issueID,$createUser,$comment_id )
    {
        $dao = new \issues\dao\Issues();
        return $dao->checkIsCommentOwner($issueID,$createUser,$comment_id );
    }

    public function getIssuesList($project_id,$status)
    {
        $dao = new \issues\dao\Issues();
        return $dao->getIssuesList($project_id,$status);
    }

    public function addIssues($projectID,$title,$content,$type,$creater_name,$status,$level)
    {
        $dao = new \issues\dao\Issues();
        return $dao->addIssues($projectID,$title,$content,$type,$creater_name,$status,$level);
    }

    public function getIssuesInfo($issueID,$project_id,$user_name)
    {
        $dao = new \issues\dao\Issues();
        return $dao->getIssuesInfo($issueID,$project_id,$user_name);

    }

    public function editIssues($issueID,$title,$content,$type,$status,$level,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->editIssues($issueID,$title,$content,$type,$status,$level,$project_id);
    }

    public function updateIssuesStatus($issueID,$status,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->updateIssuesStatus($issueID,$status,$project_id);
    }

    public function batchUpdateIssuesStatus($status, $issueID,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->batchUpdateIssuesStatus($status, $issueID,$project_id);
    }
    public function deleteIssues($issueID,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->deleteIssues($issueID,$project_id);
    }

    public function batchDeleteIssues($issue_ids,$project_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->batchDeleteIssues($issue_ids,$project_id);
    }

    public function addComment($issue_id,$content,$create_user)
    {
        $dao = new \issues\dao\Issues();
        return $dao->addComment($issue_id,$content,$create_user);
    }

    public function editComment($comment_id,$content,$issue_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->editComment($comment_id,$content,$issue_id);
    }

    public function deleteComment($comment_id,$issue_id)
    {
        $dao = new \issues\dao\Issues();
        return $dao->deleteComment($comment_id,$issue_id);
    }


}