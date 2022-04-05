package io.nuls.service.impl;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.data.CoinData;
import io.nuls.base.data.CoinFrom;
import io.nuls.base.data.CoinTo;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
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
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.TransactionTools;
import io.nuls.rpc.vo.Account;
import io.nuls.rpc.vo.AccountBalance;
import io.nuls.service.ProjectService;
import io.nuls.service.TransactionService;
import io.nuls.storge.AliasStorageService;
import io.nuls.storge.ProjectStorageService;
import io.nuls.storge.TxStorageService;
import io.nuls.utils.Utils;

import java.io.IOException;
import java.math.BigInteger;
import java.util.*;

@Service
public class ProjectServiceImpl implements ProjectService {
    @Autowired
    Config config;
    @Autowired
    LegderTools legderTools;
    @Autowired
    AccountTools accountTools;
    @Autowired
    ProjectStorageService projectStorageService;
    @Autowired
    TxStorageService txStorageService;
    @Autowired
    TransactionTools transactionTools;
    @Autowired
    TransactionService transactionService;
    @Autowired
    AliasStorageService aliasStorageService;

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

    @Override
    public boolean validProjectName(String projectName) {
        return StringUtils.isNotBlank(projectName) && projectName.trim().length() < 50;
    }

    @Override
    public boolean validProjectType(String projectType) {
        String[] result ={"前端","后台","全栈", "运维", "区块链", "人工智能", "其他分类"};
        return  Arrays.asList(result).contains(projectType);
    }

    @Override
    public boolean isProjectExist(String address, String projectName) {
        return null != projectStorageService.getProject(address,projectName);
    }

    @Override
    public Project getProject(String address, String projectName)
    {
        return projectStorageService.getProject(address,projectName);
    }

    @Override
    public Vote getProjectSupport(String address, String projectName) {
        return projectStorageService.getProjectSupport(address,projectName);
    }

    @Override
    public Transaction createProject(String address, String password, String projectName, String description, String projectType) throws NulsRuntimeException, NulsException, IOException {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createProjectTxWithoutSign(address, projectName, description, projectType);
        //签名别名交易
        signTransaction(tx, account, password);
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "创建项目交易异常");
        }
        return tx;
    }

    @Override
    public boolean createProjectTxValidate(Transaction tx) throws NulsException {

        Project project = new Project();
        project.parse(new NulsByteBuffer(tx.getTxData(),0));
        byte[] addrByte = project.getAddress();
        String address = AddressTool.getStringAddressByBytes(addrByte);
        // 判断是不是有效地址
        if (!AddressTool.validAddress(config.getChainId(), address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "地址格式不合理");
        }
        if (!validProjectName(project.getProjectName())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目名格式不合理");
        }
        if (!validProjectType(project.getProjectType())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目类型格式不合理");
        }
        if (isProjectExist(address,project.getProjectName())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目名已存在");
        }
        Alias user = aliasStorageService.getAliasByAddress(address);
        if (user == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
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
        //验证支付账户是否和申请邮箱地址的账户是同一个
        if(!Arrays.equals(cf.getAddress(),project.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户和申请创建项目的账户不是同一个");
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
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getCreateProjectFee()))) {
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
    public boolean createProjectTxCommit(Transaction tx) throws NulsException {

        Project project = new Project();
        project.parse(new NulsByteBuffer(tx.getTxData(),0));
        boolean result = projectStorageService.saveProject(project);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目信息保存失败");
        }
        // 初始化支持信息
        Vote vote = new Vote(project.getAddress(),project.getProjectName());
        result = projectStorageService.saveProjectSupport(vote);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息保存失败");
        }
        // 保存交易记录
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    @Override
    public boolean createProjectTxRollback(Transaction tx) throws NulsException {
        Project project = new Project();
        project.parse(new NulsByteBuffer(tx.getTxData(),0));
        // 判断是否已经保存了
        Project po = projectStorageService.getProject(AddressTool.getStringAddressByBytes(project.getAddress()),project.getProjectName());
        if (po != null && Arrays.equals(po.getAddress(), project.getAddress())) {
            boolean result = projectStorageService.removeProject(project);
            if (!result)
            {
                throw new NulsException(CommonCodeConstanst.FAILED, "项目信息回滚删除失败");
            }
        }
        Vote vote = projectStorageService.getProjectSupport(AddressTool.getStringAddressByBytes(project.getAddress()),project.getProjectName());
        if (vote != null && Arrays.equals(vote.getAddress(), project.getAddress()) && StringUtils.equals(vote.getProjectName(),project.getProjectName())) {
            boolean result = projectStorageService.deleteProjectSupport(vote);
            if (!result)
            {
                throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息回滚删除失败");
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

    @Override
    public Transaction updateProject(String address, String password, String projectName, String description, String projectType) throws NulsRuntimeException, NulsException, IOException {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createUpdateProjectTxWithoutSign(address, projectName, description, projectType);
        //签名别名交易
        signTransaction(tx, account, password);
        // 发送交易
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "项目信息更新交易异常");
        }
        return tx;
    }

    @Override
    public boolean updateProjectTxValidate(Transaction tx) throws NulsException {

        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Project new_project = new Project();
        Project old_project = new Project();
        new_project.parse(new NulsByteBuffer(data.getNewData(),0));
        old_project.parse(new NulsByteBuffer(data.getOldData(),0));
        if (!isProjectExist(address,old_project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
        }
        // 用户名不一样则不更新
        if (!StringUtils.equals(new_project.getProjectName(),old_project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新项目不一致");
        }
        // 地址不一样则不更新
        if (!Arrays.equals(new_project.getAddress(),old_project.getAddress()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "更新项目不一致");
        }
        // 判断是不是有效地址
        if (!AddressTool.validAddress(config.getChainId(), address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "地址格式不合理");
        }
        if (!validProjectName(new_project.getProjectName())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目名格式不合理");
        }
        if (!validProjectType(new_project.getProjectType())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目类型格式不合理");
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
        //验证支付账户是否和申请地址的账户是同一个
        if(!Arrays.equals(cf.getAddress(),data.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户和申请更新项目的账户不是同一个");
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
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getUpdateProjectInfoFee()))) {
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
    public boolean updateProjectTxCommit(Transaction tx) throws NulsException {

        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());

        Project project = new Project();
        project.parse(new NulsByteBuffer(data.getNewData(),0));
        if (!isProjectExist(address,project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
        }

        // 重新保存
        boolean result = projectStorageService.saveProject(project);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "项目新信息保存失败");
        }
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    @Override
    public boolean updateProjectTxRollback(Transaction tx) throws NulsException {
        UpdateModel data = new UpdateModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Project project = new Project();
        project.parse(new NulsByteBuffer(data.getOldData(),0));
        if (!isProjectExist(address,project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
        }
        // 重新保存
        boolean result = projectStorageService.saveProject(project);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "项目新信息保存失败");
        }
        result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }

        return true;
    }


    @Override
    public Transaction deleteProject(String address, String password, String projectName) throws NulsRuntimeException, NulsException, IOException {
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.deleteProjectTxWithoutSign(address, projectName);
        //签名别名交易
        signTransaction(tx, account, password);
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "删除项目交易异常");
        }
        return tx;
    }

    @Override
    public boolean deleteProjectTxValidate(Transaction tx) throws NulsException {

        DeleteModel data = new DeleteModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Project project = new Project();
        project.parse(new NulsByteBuffer(data.getDeleteData(),0));
        if (!isProjectExist(address,project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
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
        //验证支付账户是否和申请地址的账户是同一个
        if(!Arrays.equals(cf.getAddress(),data.getAddress())){
            throw new NulsException(CommonCodeConstanst.FAILED, "支付账户和申请更新项目的账户不是同一个");
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
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(config.getDeleteProjectFee()))) {
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
    public boolean deleteProjectTxCommit(Transaction tx) throws NulsException {

        DeleteModel data = new DeleteModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());

        Project project = new Project();
        project.parse(new NulsByteBuffer(data.getDeleteData(),0));
        if (!isProjectExist(address,project.getProjectName()))
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
        }
        // 项目删除
        boolean result = projectStorageService.removeProject(project);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "项目删除失败");
        }
        // 删除支持信息
        Vote vote = new Vote();
        vote.parse(new NulsByteBuffer(data.getOtherData(),0));
        result = projectStorageService.deleteProjectSupport(vote);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息删除失败");
        }
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    @Override
    public boolean deleteProjectTxRollback(Transaction tx) throws NulsException {

        DeleteModel data = new DeleteModel();
        data.parse(new NulsByteBuffer(tx.getTxData(),0));
        String address = AddressTool.getStringAddressByBytes(data.getAddress());
        Project project = new Project();
        project.parse(new NulsByteBuffer(data.getDeleteData(),0));
        if (isProjectExist(address,project.getProjectName()))
        {
            // 已经存在，不回滚
            throw new NulsException(CommonCodeConstanst.FAILED, "项目已存在，未删除");
        }
        // 项目重新保存
        boolean result = projectStorageService.saveProject(project);
        if (!result) {
            // 保存失败
            throw new NulsException(CommonCodeConstanst.FAILED, "项目删除回滚保存失败");
        }
        Vote vote = projectStorageService.getProjectSupport(address,project.getProjectName());
        if (vote == null)
        {
            // 重新保存支持信息
            Vote old_vote = new Vote();
            old_vote.parse(new NulsByteBuffer(data.getOtherData(),0));
            result = projectStorageService.saveProjectSupport(old_vote);
            if (!result) {
                // 保存失败
                throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息回滚保存失败");
            }
        }
        result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }

        return true;
    }

    @Override
    public Transaction voteProject(String from_address, String password, String to_address, String projectName, BigInteger voteCount) throws NulsRuntimeException, NulsException, IOException {
        Account account = accountTools.getAccountByAddress(from_address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.voteProject(from_address,  to_address, projectName, voteCount);
        //签名别名交易
        signTransaction(tx, account, password);
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "支持项目交易异常");
        }
        return tx;
    }

    @Override
    public boolean voteProjectTxValidate(Transaction tx) throws NulsException {

        VoteDetail voteDetail = new VoteDetail();
        voteDetail.parse(new NulsByteBuffer(tx.getTxData(),0));

        String from_address = AddressTool.getStringAddressByBytes(voteDetail.getFromAddress());
        String to_address = AddressTool.getStringAddressByBytes(voteDetail.getToAddress());
        // 判断是不是有效地址
        if (!AddressTool.validAddress(config.getChainId(), from_address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "地址格式不合理");
        }
        if (!AddressTool.validAddress(config.getChainId(), to_address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "地址格式不合理");
        }
        if (!validProjectName(voteDetail.getProjectName())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目名格式不合理");
        }
        Alias user = aliasStorageService.getAliasByAddress(from_address);
        if (user == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }
        user = aliasStorageService.getAliasByAddress(to_address);
        if (user == null)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "用户不存在");
        }

        if (!isProjectExist(to_address,voteDetail.getProjectName())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目不存在");
        }
        if (BigIntegerUtils.isEqualOrLessThan(voteDetail.getVoteCount(), BigInteger.ZERO)) {
            throw new NulsException(CommonCodeConstanst.PARAMETER_ERROR,"转账金额小于或等于0");
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

        List<CoinTo> to = coinData.getTo();
        //收款账户只能有一个
        if (to.size() != 1) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户有多个");
        }
        CoinTo ct = to.get(0);
        //验证收款地址是否是平台地址
        if (!Arrays.equals(ct.getAddress(), voteDetail.getToAddress())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户不正确");
        }
        if (Arrays.equals(ct.getAddress(), cf.getAddress())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款与发款账户不能一致");
        }
        //验证支付的申请费用是否正确 出金 = 入金 + 手续费 且 入金 = 更改费用
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(voteDetail.getVoteCount()))) {
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
    public boolean voteProjectTxCommit(Transaction tx) throws NulsException {

        VoteDetail voteDetail = new VoteDetail();
        voteDetail.parse(new NulsByteBuffer(tx.getTxData(),0));
        // 获取项目支持信息
        Vote vote = projectStorageService.getProjectSupport(AddressTool.getStringAddressByBytes(voteDetail.getToAddress()),voteDetail.getProjectName());
        if (vote == null)
        {
            // 不存在则初始化
            vote = new Vote(voteDetail.getToAddress(),voteDetail.getProjectName());
        }
        vote.addTx(tx.getHash(),voteDetail.getVoteCount());
        boolean result = projectStorageService.saveProjectSupport(vote);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息保存失败");
        }
        result = projectStorageService.saveProjectSupportDetail(tx.getHash().getBytes(),voteDetail);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "投票详细信息保存失败");
        }

        // 保存交易记录
        result = txStorageService.saveTransaction(tx);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录保存失败");
        }
        return true;
    }

    @Override
    public boolean voteProjectTxRollback(Transaction tx) throws NulsException {
        VoteDetail voteDetail = new VoteDetail();
        voteDetail.parse(new NulsByteBuffer(tx.getTxData(),0));
        // 获取项目支持信息
        Vote vote = projectStorageService.getProjectSupport(AddressTool.getStringAddressByBytes(voteDetail.getToAddress()),voteDetail.getProjectName());
        if (vote != null)
        {
            // 存在则恢复
            vote.removeTx(tx.getHash(),voteDetail.getVoteCount());
        }
        else
        {
            // 不存在项目支持信息，初始化
            vote = new Vote(voteDetail.getToAddress(),voteDetail.getProjectName());
        }
        boolean result = projectStorageService.saveProjectSupport(vote);
        if (!result) {
            throw new NulsException(CommonCodeConstanst.FAILED, "项目支持信息回滚保存失败");
        }
        // 回滚交易详情
        VoteDetail detail = projectStorageService.getProjectSupportDetail(tx.getHash().getBytes());
        if (detail != null)
        {
            result = projectStorageService.deleteProjectSupportDetail(tx.getHash().getBytes());
            if (!result)
            {
                throw new NulsException(CommonCodeConstanst.FAILED, "交易详情回滚删除失败");
            }
        }

        // 回滚交易记录
        result = txStorageService.removeTransaction(tx);
        if (!result)
        {
            throw new NulsException(CommonCodeConstanst.FAILED, "交易记录回滚删除失败");
        }

        return true;
    }

    @Override
    public List<HashMap<String,Object>> getProjectList() {
        List<Project> projectList = projectStorageService.getProjectList();
        List<HashMap<String,Object>> res = new ArrayList<>();
        if (projectList.size()>0)
        {
            List<byte[]> user_key_List = new ArrayList<>();
            byte[] address;
            for (int i = 0; i < projectList.size(); i++) {
                address = projectList.get(i).getAddress();
                if (!user_key_List.contains(address))
                {
                    user_key_List.add(address);
                }
            }
            HashMap<String,Alias> userMap = new HashMap<>();
            List<Alias> userList = aliasStorageService.getAliasListByAddress(user_key_List);
            for (int i = 0; i < userList.size(); i++) {
                userMap.put(AddressTool.getStringAddressByBytes(userList.get(i).getAddress()),userList.get(i));
            }
            for (int i = 0; i < projectList.size(); i++) {
                HashMap<String,Object> project = new HashMap<>();
                String address_str = AddressTool.getStringAddressByBytes(projectList.get(i).getAddress());
                project.put("address",address_str);
                project.put("projectName",projectList.get(i).getProjectName());
                project.put("projectType",projectList.get(i).getProjectType());
                project.put("description",projectList.get(i).getDescription());
                project.put("createTime",projectList.get(i).getCreateTime());
                project.put("userName",userMap.get(address_str).getAlias());
                project.put("avatar",userMap.get(address_str).getAvatar());
                res.add(project);
            }
        }
        return res;
    }

    @Override
    public List<HashMap<String,Object>> getUserProject(List<byte[]> keys) {
        List<Project> projectList = projectStorageService.getUserProjectList(keys);
        List<HashMap<String,Object>> res = new ArrayList<>();
        if (projectList.size()>0)
        {
            for (int i = 0; i < projectList.size(); i++) {
                HashMap<String,Object> project = new HashMap<>();
                project.put("projectName",projectList.get(i).getProjectName());
                project.put("projectType",projectList.get(i).getProjectType());
                project.put("description",projectList.get(i).getDescription());
                project.put("createTime",projectList.get(i).getCreateTime());
                res.add(project);
            }
        }
        return res;
    }

    @Override
    public List<HashMap<String, Object>> getAllProject() {
        List<Project> projectList = projectStorageService.getAllProject();
        List<HashMap<String,Object>> res = new ArrayList<>();
        if (projectList.size()>0)
        {
            List<byte[]> user_key_List = new ArrayList<>();
            for (int i = 0; i < projectList.size(); i++) {
                byte[] address = projectList.get(i).getAddress();
                if (!user_key_List.contains(address))
                {
                    user_key_List.add(address);
                }
            }
            HashMap<String,Alias> userMap = new HashMap<>();
            List<Alias> userList = aliasStorageService.getAliasListByAddress(user_key_List);
            for (int i = 0; i < userList.size(); i++) {
                userMap.put(AddressTool.getStringAddressByBytes(userList.get(i).getAddress()),userList.get(i));
            }

            for (int i = 0; i < projectList.size(); i++) {
                HashMap<String,Object> project = new HashMap<>();
                String address_str = AddressTool.getStringAddressByBytes(projectList.get(i).getAddress());
                project.put("address",address_str);
                project.put("projectName",projectList.get(i).getProjectName());
                project.put("projectType",projectList.get(i).getProjectType());
                project.put("description",projectList.get(i).getDescription());
                project.put("createTime",projectList.get(i).getCreateTime());
                project.put("userName",userMap.get(address_str).getAlias());
                project.put("avatar",userMap.get(address_str).getAvatar());
                res.add(project);
            }
        }
        return res;
    }

    @Override
    public List<HashMap<String, Object>> getAllProjectSupport() {
        List<Vote> projectList = projectStorageService.getAllProjectSupport();
        List<HashMap<String,Object>> res = new ArrayList<>();
        if (projectList.size()>0)
        {
            List<byte[]> user_key_List = new ArrayList<>();
            for (int i = 0; i < projectList.size(); i++) {
                byte[] address = projectList.get(i).getAddress();
                if (!user_key_List.contains(address))
                {
                    user_key_List.add(address);
                }
            }
            HashMap<String,Alias> userMap = new HashMap<>();
            List<Alias> userList = aliasStorageService.getAliasListByAddress(user_key_List);
            for (int i = 0; i < userList.size(); i++) {
                userMap.put(AddressTool.getStringAddressByBytes(userList.get(i).getAddress()),userList.get(i));
            }
            for (int i = 0; i < projectList.size(); i++) {
                HashMap<String,Object> project = new HashMap<>();
                String address_str = AddressTool.getStringAddressByBytes(projectList.get(i).getAddress());
                project.put("address",address_str);
                project.put("projectName",projectList.get(i).getProjectName());
                project.put("supportCount",projectList.get(i).getVoteCount());
                project.put("txCount",projectList.get(i).getTxCount());
                project.put("userName",userMap.get(address_str).getAlias());
                project.put("avatar",userMap.get(address_str).getAvatar());
                res.add(project);
            }
        }
        return res;
    }

    @Override
    public VoteDetail getProjectSupportDetail(byte[] key)
    {
        return projectStorageService.getProjectSupportDetail(key);
    }

    @Override
    public List<HashMap<String, Object>> getSupportDetailList() {
        return projectStorageService.getSupportDetailList();
    }
}
