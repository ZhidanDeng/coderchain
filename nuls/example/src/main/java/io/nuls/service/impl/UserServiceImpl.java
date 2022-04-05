package io.nuls.service.impl;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.constant.Constant;
import io.nuls.core.model.BigIntegerUtils;
import io.nuls.core.model.ByteUtils;
import io.nuls.model.bo.UpdateModel;
import io.nuls.model.po.TxDetail;
import io.nuls.service.TransactionService;
import io.nuls.service.UserService;
import io.nuls.storge.TxStorageService;
import io.nuls.utils.Utils;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Service;
import io.nuls.core.model.StringUtils;
import io.nuls.model.po.Alias;
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.TransactionTools;
import io.nuls.rpc.vo.Account;
import io.nuls.rpc.vo.AccountBalance;
import io.nuls.storge.AliasStorageService;
import io.nuls.base.data.*;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;

import java.io.IOException;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;


@Service
public class UserServiceImpl implements UserService {

    @Autowired
    Config config;
    @Autowired
    LegderTools legderTools;
    @Autowired
    AccountTools accountTools;
    @Autowired
    AliasStorageService aliasStorageService;
    @Autowired
    TxStorageService txStorageService;
    @Autowired
    TransactionTools transactionTools;
    @Autowired
    TransactionService transactionService;

    /**
     * 校验用户名，长度0-50
     *
     * @param userName
     * @return
     */
    public boolean validUserName(String userName) {
        return StringUtils.isNotBlank(userName) && userName.trim().length() < 50;
    }

    /**
     * 校验密码，8-20
     *
     * @param password
     * @return
     */
    public boolean validPassword(String password) {
        return password.matches("^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$");
    }

    public boolean validUserSex(String sex) {
        String[] result ={"-1","0","1"};
        return  Arrays.asList(result).contains(sex);
    }

    /**
     * 别名是否可用
     * @param alias
     * @return
     */
    public boolean isAliasUsable(String alias) {
        return null == aliasStorageService.getAlias(alias);
    }

    /**
     * 根据用户名获取用户信息
     * @param userName
     * @return
     */

    public Alias getUserInfoByUserName(String userName)
    {
        return aliasStorageService.getAlias(userName);
    }

    /**
     * 根据地址获取用户信息
     * @param address
     * @return
     */
    public Alias getUserInfoByAddress(String address)
    {
        return aliasStorageService.getAliasByAddress(address);
    }

    /**
     * 根据用户名查地址
     *
     * @param alias
     * @return
     */
    public String getAddressByAlias(String alias) throws NulsRuntimeException{
        Alias res = aliasStorageService.getAlias(alias);
        if (null == res) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
        } else {
            return AddressTool.getStringAddressByBytes(res.getAddress());
        }
    }

    public List<HashMap<String, Object>> getAliasList() throws NulsException {
        List<HashMap<String, Object>> res = aliasStorageService.getAliasList();
        return res;
    }


    public Transaction setAlias(String address, String aliasName, String password) throws NulsRuntimeException, NulsException, IOException {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createSetAliasTxWithoutSign(account, aliasName);
        //签名别名交易
        signTransaction(tx, account, password);
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "别名交易异常");
        }
        return tx;
    }

    /**
     * 交易签名
     * @param transaction
     * @param account
     * @param password
     * @return
     * @throws IOException
     */
    public Transaction signTransaction(Transaction transaction, Account account, String password) throws IOException {
        return Utils.signTransaction(transaction, account.getEncryptedPrikeyHex(), account.getPubkeyHex(), password);
    }


    public boolean rollbackAlias(Alias alias, Transaction tx) throws NulsException {
        Alias po = aliasStorageService.getAlias(alias.getAlias());
        if (po != null && Arrays.equals(po.getAddress(), alias.getAddress())) {
            boolean result = aliasStorageService.removeAlias(po);
            if (!result)
            {
                throw new NulsException(CommonCodeConstanst.FAILED, "用户记录回滚删除失败");
            }
        }
        // 回滚交易记录
        boolean result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }

        return true;
    }


    public boolean aliasTxCommit(Alias alias,Transaction tx) throws NulsException {
        boolean result = aliasStorageService.saveAlias(alias);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户记录保存失败");
        }
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }

        return true;
    }

    public boolean aliasTxValidate(Transaction transaction) throws NulsException {
        Alias alias = new Alias();
        alias.parse(new NulsByteBuffer(transaction.getTxData(),0));
        byte[] addrByte = alias.getAddress();
        String address = AddressTool.getStringAddressByBytes(addrByte);
        // 判断是不是有效地址
        if (!AddressTool.validAddress(config.getChainId(), address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "地址格式不合理");
        }
        if (!validUserName(alias.getAlias())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "别名格式不合理");
        }
        if (!isAliasUsable(alias.getAlias())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "别名已重复");
        }
        //验证是否转入指定资产到手续费账户
        CoinData coinData = new CoinData();
        coinData.parse(new NulsByteBuffer(transaction.getCoinData(),0));
        List<CoinFrom> from = coinData.getFrom();
        //支付账户只能有一个
        if (from.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户有多个");
        }
        CoinFrom cf = from.get(0);
        //验证支付账户是否和申请邮箱地址的账户是同一个
        if(!Arrays.equals(cf.getAddress(),alias.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户是否和申请邮箱地址的账户不是同一个");
        }
        List<CoinTo> to = coinData.getTo();
        //收款账户只能有一个
        if (to.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户有多个");
        }
        CoinTo ct = to.get(0);
        //验证收款地址是否是平台地址
        if (!Arrays.equals(ct.getAddress(), AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户不正确");
        }
        //验证支付的申请费用是否正确 出金 = 入金 + 手续费 且 入金 = 更改费用
        if (!((ct.getAmount().add(transaction.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getUserRegisterFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "申请费用不正确");
        }
        //验证余额是否足够支付申请费用和交易手续费
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(config.getChainId(), AddressTool.getStringAddressByBytes(cf.getAddress()), config.getChainId(), config.getAssetId());

        if (BigIntegerUtils.isLessThan(accountBalance.getAvailable(),ct.getAmount().add(transaction.getFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "余额不足够支付申请费用和交易手续费");
        }
        return true;
    }


    /**
     * 图片路径交易
     */
    public Transaction updateUserImage(String address,String password, String imgName) throws NulsRuntimeException, NulsException, IOException
    {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createUpdateImgTxWithoutSign(account, imgName);
        //签名别名交易
        signTransaction(tx, account, password);
        // 发送交易
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "图片路径更新交易异常");
        }
        return tx;
    }

    public boolean updateUserImageTxValidate(Transaction tx) throws NulsException {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        // 验证该用户信息是否存在
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新用户不存在");
        }
        String oldImg = ByteUtils.asString(data.getOldData());
        String newImg = ByteUtils.asString(data.getNewData());
        // 一样则不更新
        if (StringUtils.equals(oldImg,newImg))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "两次更新的图片地址一致");
        }
        //验证是否转入指定资产到手续费账户
        CoinData coinData = new CoinData();
        coinData.parse(new NulsByteBuffer(tx.getCoinData(),0));
        List<CoinFrom> from = coinData.getFrom();
        //支付账户只能有一个
        if (from.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户有多个");
        }
        CoinFrom cf = from.get(0);
        //验证支付账户是否和申请的账户是同一个
        if(!Arrays.equals(cf.getAddress(),data.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户是否和申请邮箱地址的账户不是同一个");
        }
        List<CoinTo> to = coinData.getTo();
        //收款账户只能有一个
        if (to.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户有多个");
        }
        CoinTo ct = to.get(0);
        //验证收款地址是否是平台手续费地址
        if (!Arrays.equals(ct.getAddress(), AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户不正确");
        }
        //验证支付的申请费用是否正确 出金 = 入金 + 手续费 且入金 = 更改图片路径费用
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getUpdateUserImgFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "申请费用不正确");
        }
        //验证余额是否足够支付申请费用和交易手续费
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(config.getChainId(), AddressTool.getStringAddressByBytes(cf.getAddress()), config.getChainId(), config.getAssetId());
        if (BigIntegerUtils.isLessThan(accountBalance.getAvailable(),ct.getAmount().add(tx.getFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "余额不足够支付申请费用和交易手续费");
        }
        return true;
    }

    public boolean updateUserImageTxCommit(Transaction tx) throws NulsException
    {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String newImg = new String(data.getNewData());
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }
        // 设置新的图片路径，重新保存
        alias.setAvatar(newImg);
        boolean result = aliasStorageService.saveAlias(alias);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "用户图片路径更新失败");
        }
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    public boolean updateUserImageTxRollback(Transaction tx) throws NulsException
    {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String oldImg = new String(data.getOldData());
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            // 不存在
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }
        if (!StringUtils.equals(alias.getAvatar(),oldImg)) {
            // 如果不一样，恢复，重新保存
            alias.setAvatar(oldImg);
            boolean result = aliasStorageService.saveAlias(alias);
            if (!result)
            {
                // 保存失败
                throw new NulsException(CommonCodeConstanst.FAILED, "用户图片路径回滚保存失败");
            }
        }
        boolean result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }

        return true;
    }

    /**
     * 用户信息更新交易
     * @param userInfo
     * @param address
     * @param password
     * @return
     * @throws NulsRuntimeException
     * @throws IOException
     * @throws NulsException
     */
    public Transaction updateUserInfo(Alias userInfo,String address, String password) throws NulsRuntimeException, IOException, NulsException {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createUpdateUserInfoTxWithoutSign(account, userInfo);
        //签名别名交易
        signTransaction(tx, account, password);
        // 发送交易
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户信息更新交易异常");
        }
        return tx;
    }

    @Override
    public boolean updateUserInfoTxValidate(Transaction tx) throws NulsException {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        // 验证该用户信息是否存在
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新用户不存在");
        }
        Alias old_info = new Alias();
        old_info.parse(new NulsByteBuffer(data.getOldData(),0));
        Alias new_info = new Alias();
        new_info.parse(new NulsByteBuffer(data.getNewData(),0));

        // 用户名不一样则不更新
        if (!StringUtils.equals(old_info.getAlias(),new_info.getAlias()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新用户不一致");
        }
        // 地址不一样则不更新
        if (!Arrays.equals(old_info.getAddress(),new_info.getAddress()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新用户不一致");
        }

        //验证是否转入指定资产到手续费账户
        CoinData coinData = new CoinData();
        coinData.parse(new NulsByteBuffer(tx.getCoinData(),0));
        List<CoinFrom> from = coinData.getFrom();
        //支付账户只能有一个
        if (from.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户有多个");
        }
        CoinFrom cf = from.get(0);
        //验证支付账户是否和申请的账户是同一个
        if(!Arrays.equals(cf.getAddress(),data.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户是否和申请邮箱地址的账户不是同一个");
        }
        List<CoinTo> to = coinData.getTo();
        //收款账户只能有一个
        if (to.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户有多个");
        }
        CoinTo ct = to.get(0);
        //验证收款地址是否是平台手续费地址
        if (!Arrays.equals(ct.getAddress(), AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户不正确");
        }
        //验证支付的申请费用是否正确 出金 = 入金 + 手续费 且入金 = 更改别名费用
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getUpdateUserInfoFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "申请费用不正确");
        }
        //验证余额是否足够支付申请费用和交易手续费
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(config.getChainId(), AddressTool.getStringAddressByBytes(cf.getAddress()), config.getChainId(), config.getAssetId());
        if (BigIntegerUtils.isLessThan(accountBalance.getAvailable(),ct.getAmount().add(tx.getFee()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "余额不足够支付申请费用和交易手续费");
        }

        return true;
    }

    @Override
    public boolean updateUserInfoTxCommit(Transaction tx) throws NulsException {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        // 验证该用户信息是否存在
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }
        Alias new_info = new Alias();
        new_info.parse(new NulsByteBuffer(data.getNewData(),0));
        // 重新保存
        boolean result = aliasStorageService.saveAlias(new_info);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "用户新信息保存失败");
        }
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    @Override
    public boolean updateUserInfoTxRollback(Transaction tx) throws NulsException {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        // 验证该用户信息是否存在
        Alias alias = aliasStorageService.getAliasByAddress(address);
        if (alias == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }
        Alias old_info = new Alias();
        old_info.parse(new NulsByteBuffer(data.getOldData(),0));
        boolean result = aliasStorageService.saveAlias(old_info);
        if (!result)
        {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "用户信息回滚失败");
        }
        result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }
        return true;
    }
}
