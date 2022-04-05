package io.nuls.storge;


import io.nuls.core.exception.NulsException;
import io.nuls.model.po.Project;
import io.nuls.model.po.Vote;
import io.nuls.model.po.VoteDetail;

import java.util.HashMap;
import java.util.List;

public interface ProjectStorageService {

    Project getProject(String address, String projectName);

    /**
     * 别名存储
     * @param project
     * @return
     */
    boolean saveProject(Project project) throws NulsException;

    /**
     * 删除别名
     * @param project
     * @return
     */
    boolean removeProject(Project project) throws NulsException;

    boolean saveProjectSupport(Vote project) throws NulsException;

    Vote getProjectSupport(String address, String projectName);

    boolean deleteProjectSupport(Vote project) throws NulsException;

    boolean saveProjectSupportDetail(byte[] key, VoteDetail project) throws NulsException;

    boolean deleteProjectSupportDetail(byte[] key) throws NulsException;

    VoteDetail getProjectSupportDetail(byte[] key);

    List<Project> getProjectList();

    List<Project> getUserProjectList(List<byte[]> keys);

    List<Project> getAllProject();

    List<Vote> getAllProjectSupport();

    List<HashMap<String,Object>> getSupportDetailList();
}
