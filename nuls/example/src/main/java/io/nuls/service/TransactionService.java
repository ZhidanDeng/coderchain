package io.nuls.service;
import io.nuls.Config;
import io.nuls.base.basic.TransactionFeeCalculator;
import io.nuls.base.signture.P2PHKSignature;
import io.nuls.constant.Constant;
import io.nuls.base.RPCUtil;
import io.nuls.base.basic.AddressTool;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Service;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.BigIntegerUtils;
import io.nuls.core.model.StringUtils;
import io.nuls.model.bo.DeleteModel;
import io.nuls.model.bo.UpdateModel;
import io.nuls.model.po.*;
import io.nuls.base.data.*;
import java.io.IOException;
import java.math.BigInteger;
import java.util.Arrays;
import java.util.List;

import io.nuls.core.rpc.util.NulsDateUtils;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.vo.Account;
import io.nuls.rpc.vo.AccountBalance;
@Service
public class TransactionService {
    @Autowired
    Config config;

    @Autowired
    LegderTools legderTools;

    @Autowired
    UserService userService;

    @Autowired
    ProjectService projectService;

    /**
     * 用户注册的无签名交易
     * @param account
     * @param aliasName
     * @return
     * @throws NulsException
     */
    public Transaction createSetAliasTxWithoutSign(Account account, String aliasName) throws NulsException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_REGISTER_USER_ALIAS);
        tx.setTime(NulsDateUtils.getCurrentTimeSeconds());
        tx.setRemark(StringUtils.bytes("新用户注册"));
        Alias alias = new Alias(AddressTool.getAddress(account.getAddress()), aliasName);
        try {
            tx.setTxData(alias.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"用户信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();

        BigInteger txFee = config.getUserRegisterFee();


        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS，其实费用为0，后续可能会收取手续费
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, account.getAddress(), assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(alias.getAddress(), assetChainId, assetsId, txFee, nonce, locked);
        // 平台不收费，全部作为手续费
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), assetChainId, assetsId, txFee);


        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "insufficient fee");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));

        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }


    /**
     * 用户信息更新的无签名交易
     * @param account
     * @param alias
     * @return
     * @throws NulsException
     */
    public Transaction createUpdateUserInfoTxWithoutSign(Account account, Alias alias) throws NulsException, IOException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_UPDATE_USER_INFO);
        tx.setTime(NulsDateUtils.getCurrentTimeSeconds());
        tx.setRemark(StringUtils.bytes("用户信息更新"));
        // 获取旧的交易信息
        Alias old_alias = userService.getUserInfoByAddress(account.getAddress());
        // 新旧数据进行存储
        UpdateModel data = new UpdateModel(old_alias.serialize(),alias.serialize(),AddressTool.getAddress(account.getAddress()));
        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"更新用户信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = config.getUpdateUserInfoFee();

        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, account.getAddress(), assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(alias.getAddress(), assetChainId, assetsId, txFee, nonce, locked);
        // 收费地址为平台
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), assetChainId, assetsId, txFee);


        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    public Transaction createUpdateImgTxWithoutSign(Account account, String imgName) throws NulsException {
        Transaction tx = new Transaction();
        // 交易类型
        tx.setType(Constant.TX_TYPE_UPDATE_USER_IMG);
        // 交易时间
        tx.setTime(NulsDateUtils.getCurrentTimeSeconds());

        tx.setRemark(StringUtils.bytes("用户更新图片路径"));
        // 交易内容

        // 获取旧的交易信息
        Alias alias = userService.getUserInfoByAddress(account.getAddress());
        // 新旧数据进行存储
        UpdateModel data = new UpdateModel(StringUtils.bytes(alias.getAvatar()),StringUtils.bytes(imgName),AddressTool.getAddress(account.getAddress()));
        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"更新信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = config.getUpdateUserImgFee();

        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, account.getAddress(), assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(alias.getAddress(), config.getChainId(), assetsId, txFee, nonce, locked);
        // 收费地址为平台，全部作为手续费
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), config.getChainId(), assetsId, txFee);

        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    /**
     * 创建项目的无签名交易
     * @param address
     * @param projectName
     * @param description
     * @param projectType
     * @return
     * @throws NulsException
     */
    public Transaction createProjectTxWithoutSign(String address, String projectName, String description, String projectType)  throws NulsException
    {
        Transaction tx = new Transaction();
        // 创建时间
        long createTime = NulsDateUtils.getCurrentTimeSeconds();
        // 交易类型
        tx.setType(Constant.TX_TYPE_CREATE_PROJECT);
        // 交易时间
        tx.setTime(createTime);
        // 交易备注
        tx.setRemark(StringUtils.bytes("用户创建项目"));

        // 交易内容
        Project project = new Project(AddressTool.getAddress(address),projectName, projectType, description, createTime);
        try {
            tx.setTxData(project.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"项目信息序列化失败");
        }

        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = config.getCreateProjectFee();

        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, address, assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(project.getAddress(), assetChainId, assetsId, txFee, nonce, locked);
        // 平台收费
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), assetChainId, assetsId, txFee);

        int txSize = tx.size() + coinFrom.size() + coinTo.size() + P2PHKSignature.SERIALIZE_LENGTH;
        //计算手续费
        BigInteger fee = TransactionFeeCalculator.getNormalTxFee(txSize);

        BigInteger totalAmount = txFee.add(fee);
        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, totalAmount)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户余额不足");
        }
        coinFrom.setAmount(totalAmount);
        CoinData coinData = new CoinData();
        coinData.setFrom(List.of(coinFrom));
        coinData.setTo(List.of(coinTo));

        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    public Transaction createUpdateProjectTxWithoutSign(String address, String projectName, String description, String projectType) throws NulsException, IOException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_UPDATE_PROJECT);
        tx.setTime(NulsDateUtils.getCurrentTimeSeconds());
        tx.setRemark(StringUtils.bytes("用户更新项目"));
        Project old_project = projectService.getProject(address, projectName);
        Project new_project = new Project(AddressTool.getAddress(address),projectName,projectType,description,old_project.getCreateTime());
        UpdateModel data = new UpdateModel(old_project.serialize(),new_project.serialize(),AddressTool.getAddress(address));

        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"更新项目信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = config.getUpdateProjectInfoFee();

        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, address, assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(AddressTool.getAddress(address), assetChainId, assetsId, txFee, nonce, locked);
        // 收费地址为平台
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), assetChainId, assetsId, txFee);


        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    public Transaction deleteProjectTxWithoutSign(String address, String projectName) throws NulsException, IOException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_DELETE_PROJECT);
        tx.setTime(NulsDateUtils.getCurrentTimeSeconds());
        tx.setRemark(StringUtils.bytes("用户删除项目"));
        Project project = projectService.getProject(address, projectName);
        Vote project_support = projectService.getProjectSupport(address,projectName);
        DeleteModel data = new DeleteModel(project.serialize(),project_support.serialize(),AddressTool.getAddress(address));
        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"删除项目信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = config.getDeleteProjectFee();
        //查询账本获取nonce值，由用户付费Constant.BLACK_CODER_ADDRESS
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, address, assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(AddressTool.getAddress(address), assetChainId, assetsId, txFee, nonce, locked);
        // 收费地址为平台
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS), assetChainId, assetsId, txFee);
        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    public Transaction voteProject(String from_address,  String to_address, String projectName, BigInteger voteCount) throws NulsException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_SUPPORT_PROJECT);
        Long time = NulsDateUtils.getCurrentTimeSeconds();
        tx.setTime(time);
        tx.setRemark(StringUtils.bytes("用户支持项目"));
        VoteDetail data = new VoteDetail(AddressTool.getAddress(from_address),AddressTool.getAddress(to_address),voteCount,projectName,time);
        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"支持项目信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = voteCount;
        //查询账本获取nonce值
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, from_address, assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(AddressTool.getAddress(from_address), assetChainId, assetsId, txFee, nonce, locked);
        // 收费地址为平台
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(to_address), assetChainId, assetsId, txFee);
        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }

    public Transaction createTransferTxWithoutSign(String address, String to_address, BigInteger amount, String remark) throws NulsException, IOException {
        Transaction tx = new Transaction();
        tx.setType(Constant.TX_TYPE_TRANSFER);
        Long time = NulsDateUtils.getCurrentTimeSeconds();
        tx.setTime(time);
        tx.setRemark(StringUtils.bytes(remark));
        TxDetail data = new TxDetail(AddressTool.getAddress(address),AddressTool.getAddress(to_address),amount,time);
        try {
            tx.setTxData(data.serialize());
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"转账信息序列化失败");
        }
        int assetChainId = config.getChainId();
        int assetsId = config.getAssetId();
        BigInteger txFee = amount.add(config.getTransferFee());
        //查询账本获取nonce值
        AccountBalance accountBalance = legderTools.getBalanceAndNonce(assetChainId, address, assetChainId, assetsId);
        byte[] nonce = RPCUtil.decode(accountBalance.getNonce());
        byte locked = 0;
        CoinFrom coinFrom = new CoinFrom(AddressTool.getAddress(address), assetChainId, assetsId, txFee, nonce, locked);
        // 收费地址
        CoinTo coinTo = new CoinTo(AddressTool.getAddress(to_address), assetChainId, assetsId, amount);
        //检查余额是否充足
        BigInteger mainAsset = accountBalance.getAvailable();
        //余额不足
        if (BigIntegerUtils.isLessThan(mainAsset, txFee)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "余额不足");
        }
        CoinData coinData = new CoinData();
        coinData.setFrom(Arrays.asList(coinFrom));
        coinData.setTo(Arrays.asList(coinTo));
        try {
            tx.setCoinData(coinData.serialize());
            //计算交易数据摘要哈希
            tx.setHash(NulsHash.calcHash(tx.serializeForHash()));
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.SERIALIZE_ERROR,"交易费用信息序列化失败");
        }
        return tx;
    }
}
