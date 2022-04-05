package io.nuls.service;

import io.nuls.base.data.Transaction;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.model.po.Project;
import io.nuls.model.po.Vote;
import io.nuls.model.po.VoteDetail;
import io.nuls.rpc.vo.Account;

import java.io.IOException;
import java.math.BigInteger;
import java.util.HashMap;
import java.util.List;

public interface ProjectService {

    /**
     * 交易签名
     * @param transaction
     * @param account
     * @param password
     * @return
     * @throws IOException
     */
    Transaction signTransaction(Transaction transaction, Account account, String password) throws IOException;

    /**
     * 验证项目名格式
     * @param projectName
     * @return
     */
    boolean validProjectName(String projectName);

    /**
     * 验证项目类型格式
     * @param projectType
     * @return
     */
    boolean validProjectType(String projectType);

    /**
     * 项目是否已经存在
     */
    boolean isProjectExist(String address,String projectName);

    /**
     * 获取项目
     * @param address
     * @param projectName
     * @return
     */
    Project getProject(String address, String projectName);

    /**
     * 获取项目的支持情况
     * @param address
     * @param projectName
     * @return
     */
    Vote getProjectSupport(String address, String projectName);

    /**
     * 创建项目的交易
     * @param address
     * @param password
     * @param projectName
     * @param description
     * @param projectType
     * @return
     */
    Transaction createProject(String address, String password, String projectName, String description, String projectType) throws NulsRuntimeException, NulsException, IOException;

    boolean createProjectTxValidate(Transaction tx) throws NulsException;

    boolean createProjectTxCommit(Transaction tx) throws NulsException;

    boolean createProjectTxRollback(Transaction tx) throws NulsException;

    /**
     * 更新项目交易
     * @param address
     * @param password
     * @param projectName
     * @param description
     * @param projectType
     * @return
     * @throws NulsRuntimeException
     * @throws NulsException
     * @throws IOException
     */
    Transaction updateProject(String address, String password, String projectName, String description, String projectType) throws NulsRuntimeException, NulsException, IOException;
    boolean updateProjectTxValidate(Transaction tx) throws NulsException;

    boolean updateProjectTxCommit(Transaction tx) throws NulsException;

    boolean updateProjectTxRollback(Transaction tx) throws NulsException;

    /**
     * 删除项目交易
     * @param address
     * @param password
     * @param projectName
     * @return
     * @throws NulsRuntimeException
     * @throws NulsException
     * @throws IOException
     */
    Transaction deleteProject(String address, String password, String projectName) throws NulsRuntimeException, NulsException, IOException;

    boolean deleteProjectTxValidate(Transaction tx) throws NulsException;

    boolean deleteProjectTxCommit(Transaction tx) throws NulsException;

    boolean deleteProjectTxRollback(Transaction tx) throws NulsException;

    /**
     * 项目支持交易
     * @param from_address
     * @param password
     * @param to_address
     * @param projectName
     * @param voteCount
     * @return
     * @throws NulsRuntimeException
     * @throws NulsException
     * @throws IOException
     */
    Transaction voteProject(String from_address, String password, String to_address, String projectName, BigInteger voteCount) throws NulsRuntimeException, NulsException, IOException;

    boolean voteProjectTxValidate(Transaction tx) throws NulsException;

    boolean voteProjectTxCommit(Transaction tx) throws NulsException;

    boolean voteProjectTxRollback(Transaction tx) throws NulsException;

    List<HashMap<String,Object>> getProjectList();

    List<HashMap<String,Object>> getUserProject(List<byte[]> keys);

    List<HashMap<String, Object>> getAllProject();

    List<HashMap<String, Object>> getAllProjectSupport();

    VoteDetail getProjectSupportDetail(byte[] key);

    List<HashMap<String, Object>> getSupportDetailList();
}
