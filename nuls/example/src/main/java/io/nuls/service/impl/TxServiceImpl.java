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
import io.nuls.model.po.Alias;
import io.nuls.model.po.TxDetail;
import io.nuls.model.po.UserTx;
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.TransactionTools;
import io.nuls.rpc.vo.Account;
import io.nuls.rpc.vo.AccountBalance;
import io.nuls.service.TransactionService;
import io.nuls.service.TxService;
import io.nuls.storge.AliasStorageService;
import io.nuls.storge.TxStorageService;
import io.nuls.utils.Utils;

import java.io.IOException;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
@Service
public class TxServiceImpl implements TxService {

    @Autowired
    Config config;

    @Autowired
    AliasStorageService aliasStorageService;

    @Autowired
    TxStorageService txStorageService;

    @Autowired
    AccountTools accountTools;

    @Autowired
    TransactionTools transactionTools;
    @Autowired
    TransactionService transactionService;
    @Autowired
    LegderTools legderTools;

    public boolean validTxType(int tx) {
        int[] result ={
                Constant.TX_TYPE_REGISTER_USER_ALIAS,
                Constant.TX_TYPE_UPDATE_USER_INFO,
                Constant.TX_TYPE_UPDATE_USER_IMG,
                Constant.TX_TYPE_CREATE_PROJECT,
                Constant.TX_TYPE_SUPPORT_PROJECT,
                Constant.TX_TYPE_UPDATE_PROJECT,
                Constant.TX_TYPE_DELETE_PROJECT};
        return  Arrays.asList(result).contains(tx);
    }

    public Transaction signTransaction(Transaction transaction, Account account, String password) throws IOException {
        return Utils.signTransaction(transaction, account.getEncryptedPrikeyHex(), account.getPubkeyHex(), password);
    }

    @Override
    public List<HashMap<String, Object>> getTransferList() {
        return txStorageService.getTransferList();
    }

    @Override
    public TxDetail getTransferDetail(byte[] tx) {
        return txStorageService.getTransferDetail(tx);
    }

    @Override
    public Transaction transfer(String address, String to_address, String password, BigInteger amount, String remark) throws NulsRuntimeException, NulsException, IOException{
        Account account = accountTools.getAccountByAddress(address);
        if (null == account) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址账户信息不存在");
        }
        //创建别名交易
        Transaction tx = transactionService.createTransferTxWithoutSign(address, to_address, amount ,remark);
        //签名别名交易
        signTransaction(tx, account, password);
        if (!transactionTools.newTx(tx)) {
            throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "创建转账交易异常");
        }
        return tx;
    }

    @Override
    public boolean transferTxRollback(Transaction tx) throws NulsException {
        // 回滚交易详情
        TxDetail detail = txStorageService.getTransferDetail(tx.getHash().getBytes());
        if (detail != null)
        {
            boolean result = txStorageService.deleteTransferDetail(tx.getHash().getBytes());
            if (!result)
            {
                throw new NulsException(CommonCodeConstanst.FAILED, "转账交易详情回滚删除失败");
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
    public boolean transferTxCommit(Transaction tx) throws NulsException {
        TxDetail txDetail = new TxDetail();
        txDetail.parse(new NulsByteBuffer(tx.getTxData(),0));
        boolean result = txStorageService.saveTransferDetail(tx.getHash().getBytes(),txDetail);
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
    public boolean transferTxValidate(Transaction tx) throws NulsException {
        TxDetail txDetail = new TxDetail();
        txDetail.parse(new NulsByteBuffer(tx.getTxData(),0));

        String from_address = AddressTool.getStringAddressByBytes(txDetail.getFromAddress());
        String to_address = AddressTool.getStringAddressByBytes(txDetail.getToAddress());
        // 判断是不是有效地址
        if (!AddressTool.validAddress(config.getChainId(), from_address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "发款地址格式不合理");
        }
        if (!AddressTool.validAddress(config.getChainId(), to_address)) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款地址格式不合理");
        }

//        Alias user = aliasStorageService.getAliasByAddress(from_address);
//        if (user == null)
//        {
//            // 发款用户不存在，看看是不是初始化账户，不是的话报错
//            if (!StringUtils.equals(from_address,Constant.BLOCK_INIT_ADDRESS1)) {
//                throw new NulsException(CommonCodeConstanst.FAILED, "发款用户不存在");
//            }
//        }
//        user = aliasStorageService.getAliasByAddress(to_address);
//        if (user == null)
//        {
//            throw new NulsException(CommonCodeConstanst.FAILED, "收款用户不存在");
//        }


        if (BigIntegerUtils.isEqualOrLessThan(txDetail.getTxCount(), BigInteger.ZERO)) {
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
        //验证收款地址
        if (!Arrays.equals(ct.getAddress(), txDetail.getToAddress())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款账户不正确");
        }
        if (Arrays.equals(ct.getAddress(), cf.getAddress())) {
            throw new NulsException(CommonCodeConstanst.FAILED, "收款与发款账户不能一致");
        }
        //验证支付的申请费用是否正确 出金 = 入金 + 手续费 且 入金 = 更改费用
        if (!((ct.getAmount().add(tx.getFee())).equals(cf.getAmount()) && ct.getAmount().equals(txDetail.getTxCount()))) {
            throw new NulsException(CommonCodeConstanst.FAILED, "申请费用不正确");
        }

        return true;
    }

    @Override
    public UserTx getUserTx(String address, String type) {
        return txStorageService.getTx(address, type);
    }
}
