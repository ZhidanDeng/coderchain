package io.nuls.storge.impl;

import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.constant.ProjectStorgeConstant;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.log.Log;
import io.nuls.core.model.StringUtils;
import io.nuls.core.rockdb.model.Entry;
import io.nuls.core.rockdb.service.RocksDBService;
import io.nuls.model.po.Project;
import io.nuls.model.po.Vote;
import io.nuls.model.po.VoteDetail;
import io.nuls.storge.ProjectStorageService;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;

@Component
public class ProjectStorageServiceImpl implements ProjectStorageService  {
    @Override
    public Project getProject(String address, String projectName) {
        if (address == null || "".equals(address.trim())) {
            return null;
        }
        if (projectName == null || "".equals(projectName.trim())) {
            return null;
        }
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
                if (!result) {
                    return null;
                }
            }

        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目信息数据库创建失败");
        }

        byte[] projectBytes = RocksDBService.get(ProjectStorgeConstant.DB_NAME_PROJECT_INFO, StringUtils.bytes(address+"/"+projectName));
        if (null == projectBytes) {
            return null;
        }
        Project project = new Project();

        try {
            //将byte数组反序列化为project返回
            project.parse(projectBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目信息反序列化失败");
        }
        return project;
    }

    @Override
    public boolean saveProject(Project project) throws NulsException {
        try {
            boolean result;
            //check if the table is exist
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO)) {
                result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
                if (!result) {
                    throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目信息数据库创建失败");
                }
            }
            result = RocksDBService.put(ProjectStorgeConstant.DB_NAME_PROJECT_INFO, StringUtils.bytes(AddressTool.getStringAddressByBytes(project.getAddress())+"/"+project.getProjectName()), project.serialize());
            if (!result) {
                throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"用户数据保存失败");
            }
            return true;
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"用户数据保存失败");
        }
    }

    @Override
    public boolean removeProject(Project project) throws NulsException {
        try {
            return RocksDBService.delete(ProjectStorgeConstant.DB_NAME_PROJECT_INFO, StringUtils.bytes(AddressTool.getStringAddressByBytes(project.getAddress())+"/"+project.getProjectName()));
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_DELETE_ERROR,e);
        }
    }

    @Override
    public boolean saveProjectSupport(Vote project) throws NulsException {
        try {
            boolean result;
            //check if the table is exist
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT)) {
                result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT);
                if (!result) {
                    throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目支持信息数据库创建失败");
                }
            }
            result = RocksDBService.put(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT, StringUtils.bytes(AddressTool.getStringAddressByBytes(project.getAddress())+"/"+project.getProjectName()), project.serialize());
            if (!result) {
                throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"项目支持信息数据保存失败");
            }
            return true;
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"项目支持信息数据保存失败");
        }
    }

    @Override
    public Vote getProjectSupport(String address, String projectName) {
        if (address == null || "".equals(address.trim())) {
            return null;
        }
        if (projectName == null || "".equals(projectName.trim())) {
            return null;
        }
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目支持信息数据库创建失败");
        }

        byte[] projectBytes = RocksDBService.get(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT, StringUtils.bytes(address+"/"+projectName));
        if (null == projectBytes) {
            return null;
        }
        Vote project = new Vote();
        try {
            //将byte数组反序列化为project返回
            project.parse(projectBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目支持信息反序列化失败");
        }
        return project;
    }



    @Override
    public boolean deleteProjectSupport(Vote project) throws NulsException {
        try {
            return RocksDBService.delete(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT, StringUtils.bytes(AddressTool.getStringAddressByBytes(project.getAddress()) + "/" + project.getProjectName()));
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_DELETE_ERROR,e);
        }
    }

    @Override
    public boolean saveProjectSupportDetail(byte[] key, VoteDetail project) throws NulsException {
        try {
            boolean result;
            //check if the table is exist
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL)) {
                result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL);
                if (!result) {
                    throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目支持信息详情数据库创建失败");
                }
            }
            result = RocksDBService.put(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL, key, project.serialize());
            if (!result) {
                throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"项目支持信息详情数据保存失败");
            }
            return true;
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"项目支持信息详情数据保存失败");
        }
    }

    @Override
    public boolean deleteProjectSupportDetail(byte[] key) throws NulsException {
        try {
            return RocksDBService.delete(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL, key);
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_DELETE_ERROR,e);
        }
    }

    @Override
    public VoteDetail getProjectSupportDetail(byte[] key) {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目支持信息详情数据库创建失败");
        }

        byte[] projectBytes = RocksDBService.get(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL, key);
        if (null == projectBytes) {
            return null;
        }
        VoteDetail project = new VoteDetail();
        try {
            //将byte数组反序列化为project返回
            project.parse(projectBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目支持信息反序列化失败");
        }
        return project;
    }

    @Override
    public List<Project> getProjectList() {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目信息数据库创建失败");
        }
        try {
            List<byte[]> value_list = RocksDBService.valueList(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
            if (null == value_list) {
                return new ArrayList<>();
            }

            List<Project> project_list = new ArrayList<>();
            for (int i = 0; i < value_list.size(); i++) {
                Project p = new Project();
                p.parse(new NulsByteBuffer(value_list.get(i),0));
                project_list.add(p);
            }
            if (project_list.size()>0)
            {
                //按照创建时间排序排序
                Collections.sort(project_list, (o1, o2) -> {
                    //排序属性
                    return o2.getCreateTime().compareTo(o1.getCreateTime());
                });
            }
            int size = value_list.size();
            if (size>20)
            {
                size = 20;
            }
            return project_list.subList(0,size);
        }
        catch (Exception e)
        {
            Log.error(e);
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目信息获取失败");
        }
    }

    @Override
    public List<Project> getUserProjectList(List<byte[]> keys) {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目信息数据库创建失败");
        }
        try
        {
            List<byte[]> value_list = RocksDBService.multiGetValueList(ProjectStorgeConstant.DB_NAME_PROJECT_INFO,keys);
            List<Project> res = new ArrayList<>();

            for (int i = 0; i < value_list.size(); i++) {
                Project a = new Project();
                a.parse(new NulsByteBuffer(value_list.get(i),0));
                res.add(a);
            }
            return res;
        }
        catch (Exception e)
        {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"用户项目信息获取失败");
        }

    }

    @Override
    public List<Project> getAllProject() {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目信息数据库创建失败");
        }
        try {
            List<byte[]> value_list = RocksDBService.valueList(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
            if (null == value_list) {
                return new ArrayList<>();
            }
            List<Project> project_list = new ArrayList<>();

            for (int i = 0; i < value_list.size(); i++) {
                Project p = new Project();
                p.parse(new NulsByteBuffer(value_list.get(i),0));
                project_list.add(p);
            }
            if (project_list.size()>0)
            {
                //按照创建时间排序排序
                Collections.sort(project_list, (o1, o2) -> {
                    //排序属性
                    return o2.getCreateTime().compareTo(o1.getCreateTime());
                });
            }
            return project_list;
        }
        catch (Exception e)
        {
            Log.error(e);
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目信息获取失败");
        }
    }

    @Override
    public List<Vote> getAllProjectSupport() {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"项目支持信息数据库创建失败");
        }
        try {
            List<byte[]> value_list = RocksDBService.valueList(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT);
            if (null == value_list) {
                return new ArrayList<>();
            }
            List<Vote> project_list = new ArrayList<>();

            for (int i = 0; i < value_list.size(); i++) {
                Vote p = new Vote();
                p.parse(new NulsByteBuffer(value_list.get(i),0));
                project_list.add(p);
            }
            if (project_list.size()>0)
            {
                //按照投票数目排序
                Collections.sort(project_list, (o1, o2) -> {
                    //排序属性
                    return o2.getVoteCount().compareTo(o1.getVoteCount());
                });
            }
            return project_list;
        }
        catch (Exception e)
        {
            Log.error(e);
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"项目支持信息获取失败");
        }
    }

    @Override
    public List<HashMap<String,Object>> getSupportDetailList() {
        try {
            if (!RocksDBService.existTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL)) {
                boolean result = RocksDBService.createTable(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"转账信息数据库创建失败");
        }
        try
        {
            List<Entry<byte[], byte[]>> result = RocksDBService.entryList(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL);
            List<HashMap<String,Object>> res = new ArrayList<>();
            for (int i = 0; i < result.size(); i++) {
                HashMap<String,Object> o = new HashMap<>();
                VoteDetail a = new VoteDetail();
                a.parse(new NulsByteBuffer(result.get(i).getValue(),0));
                String key = HexUtil.encode(result.get(i).getKey());
                o.put("hash",key);
                o.put("txCount",a.getVoteCount());
                o.put("createTime",a.getCreateTime());
                res.add(o);
            }
            return res;
        }
        catch (Exception e)
        {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"用户转账信息获取失败");
        }
    }
}
