package io.nuls.service;

import io.nuls.base.data.Transaction;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.model.po.Alias;
import io.nuls.rpc.vo.Account;

import java.io.IOException;
import java.util.HashMap;
import java.util.List;

public interface UserService {
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
     * 验证用户名格式
     * @param userName
     * @return
     */
    boolean validUserName(String userName);

    /**
     * 验证用户密码格式
     * @param password
     * @return
     */
    boolean validPassword(String password);

    /**
     * 验证性别格式
     * @param sex
     * @return
     */
    boolean validUserSex(String sex);

    /**
     * 别名是否可用
     * @param alias
     * @return
     */
    boolean isAliasUsable(String alias);

    Alias getUserInfoByUserName(String userName);

    Alias getUserInfoByAddress(String address);

    /**
     * 根据别名获取地址
     * @param alias
     * @return
     */
    String getAddressByAlias(String alias);

    /**
     * 获取别名列表
     * @return
     * @throws NulsException
     */
    List<HashMap<String, Object>> getAliasList() throws NulsException;

    /**
     * 设置别名
     * @param address
     * @param aliasName
     * @param password
     * @return
     * @throws NulsRuntimeException
     * @throws NulsException
     * @throws IOException
     */
    Transaction setAlias(String address, String aliasName, String password) throws NulsRuntimeException, NulsException, IOException;

    /**
     * 更新用户的图片路径
     * @param address
     * @param password
     * @param imgName
     * @return
     * @throws NulsRuntimeException
     * @throws NulsException
     * @throws IOException
     */
    Transaction updateUserImage(String address,String password, String imgName) throws NulsRuntimeException, NulsException, IOException;


    /**
     * 交易回滚
     * @param alias
     * @return
     * @throws NulsException
     */
    boolean rollbackAlias(Alias alias, Transaction tx) throws NulsException;

    /**
     * 交易提交
     * @param alias
     * @return
     * @throws NulsException
     */
    boolean aliasTxCommit(Alias alias, Transaction tx) throws NulsException;

    /**
     * 交易验证
     * @param transaction
     * @return
     * @throws NulsException
     */
    boolean aliasTxValidate(Transaction transaction) throws NulsException;

    /**
     * 更新用户图片交易验证
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserImageTxValidate(Transaction tx) throws NulsException;

    /**
     * 更新用户图片交易提交
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserImageTxCommit(Transaction tx) throws NulsException;

    /**
     * 更新用户图片交易回滚
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserImageTxRollback(Transaction tx) throws NulsException;

    /**
     * 更新用户信息
     * @param userInfo
     * @param address
     * @param password
     * @return
     * @throws NulsRuntimeException
     * @throws IOException
     * @throws NulsException
     */
    Transaction updateUserInfo(Alias userInfo,String address, String password)  throws NulsRuntimeException, IOException, NulsException;

    /**
     * 更新用户信息交易验证
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserInfoTxValidate(Transaction tx) throws NulsException;

    /**
     * 更新用户信息交易提交
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserInfoTxCommit(Transaction tx) throws NulsException;

    /**
     * 更新用户信息交易回滚
     * @param tx
     * @return
     * @throws NulsException
     */
    boolean updateUserInfoTxRollback(Transaction tx) throws NulsException;
}
