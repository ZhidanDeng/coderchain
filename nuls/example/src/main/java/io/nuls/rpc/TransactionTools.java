package io.nuls.rpc;

import io.nuls.Config;
import io.nuls.base.RPCUtil;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.data.CoinData;
import io.nuls.base.data.CoinFrom;
import io.nuls.base.data.CoinTo;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.NulsException;
import io.nuls.core.log.Log;
import io.nuls.core.rpc.info.Constants;
import io.nuls.core.rpc.model.ModuleE;
import io.nuls.model.po.Alias;
import io.nuls.rpc.vo.TxRegisterDetail;
import io.nuls.service.UserService;

import java.io.IOException;
import java.util.*;
import java.util.function.Function;

/**
 * @Author: zhoulijun
 * @Time: 2019-06-12 17:57
 * @Description: 功能描述
 */
@Component
public class TransactionTools implements CallRpc {

    @Autowired
    Config config;

    @Autowired
    UserService userService;

    /**
     * 发起新交易
     */
    public Boolean newTx(Transaction tx) throws NulsException, IOException {
        Map<String, Object> params = new HashMap<>(2);
        params.put("chainId", config.getChainId());
        params.put("tx", RPCUtil.encode(tx.serialize()));
        return callRpc(ModuleE.TX.abbr, "tx_newTx", params, res -> true);
    }

    /**
     * 向交易模块注册交易
     * Register transactions with the transaction module
     */
    public boolean registerTx(String moduleName,int... txTyps) {
        try {
            List<TxRegisterDetail> txRegisterDetailList = new ArrayList<>();
            Arrays.stream(txTyps).forEach(txType->{
                TxRegisterDetail detail = new TxRegisterDetail();
                detail.setSystemTx(false);
                detail.setTxType(txType);
                detail.setUnlockTx(false);
                detail.setVerifySignature(true);
                detail.setVerifyFee(false);
                txRegisterDetailList.add(detail);
            });
            //向交易管理模块注册交易
            Map<String, Object> params = new HashMap<>();
            params.put(Constants.VERSION_KEY_STR, "1.0");
            params.put(Constants.CHAIN_ID, config.getChainId());
            params.put("moduleCode", moduleName);
            params.put("list", txRegisterDetailList);
            params.put("delList",List.of());
            return callRpc(ModuleE.TX.abbr, "tx_register", params,(Function<Map<String,Object>, Boolean>)  res -> (Boolean) res.get("value"));
        } catch (Exception e) {
            Log.error("", e);
        }
        return true;
    }

    public HashMap<String, Object> getTx(String txHash){
        Map<String, Object> params = new HashMap<>(2);
        params.put("chainId", config.getChainId());
        params.put("txHash", txHash);
        return callRpc(ModuleE.TX.abbr, "tx_getTx", params, (Function<Map<String, Object>, HashMap<String, Object>>) res ->{
            if (res == null || res.get("tx") == null)
            {
                return null;
            }
            String txStr = (String) res.get("tx");
            Transaction tx = new Transaction();
            try {
                tx.parse(new NulsByteBuffer(HexUtil.decode(txStr),0));
                CoinData coinData = new CoinData();
                coinData.parse(new NulsByteBuffer(tx.getCoinData(),0));
                CoinFrom cf = coinData.getFrom().get(0);
                CoinTo ct = coinData.getTo().get(0);
                byte[] from_address = cf.getAddress();
                byte[] to_address = ct.getAddress();
                HashMap<String, Object> result = new HashMap<>();
                result.put("txCount",cf.getAmount());
                result.put("txType",tx.getType());
                result.put("blockHeight",tx.getBlockHeight());
                result.put("createTime",tx.getTime());
                result.put("remark",tx.getRemark());
                result.put("hash",tx.getHash().toHex());
                result.put("size",tx.getSize());
                if (!Arrays.equals(from_address, AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS))  && !Arrays.equals(from_address, AddressTool.getAddress(Constant.BLOCK_INIT_ADDRESS1))) {
                    Alias user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(from_address));
                    result.put("fromUser",user.getAlias());
                    result.put("fromAddress",AddressTool.getStringAddressByBytes(from_address));
                }
                else
                {
                    result.put("fromUser","coderChain");
                    result.put("fromAddress","");
                }
                if (!Arrays.equals(to_address, AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS)) && !Arrays.equals(to_address, AddressTool.getAddress(Constant.BLOCK_INIT_ADDRESS1))) {
                    Alias user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(to_address));
                    result.put("toUser",user.getAlias());
                    result.put("toAddress", AddressTool.getStringAddressByBytes(to_address));
                }
                else
                {
                    result.put("toUser","coderChain");
                    result.put("toAddress", "");
                }
                return result;
            } catch (NulsException e) {
                return null;
            }
        });
    }

    public Long getTxTime(String txHash){
        Map<String, Object> params = new HashMap<>(2);
        params.put("chainId", config.getChainId());
        params.put("txHash", txHash);
        return callRpc(ModuleE.TX.abbr, "tx_getTx", params, (Function<Map<String, Object>, Long>) res ->{
            if (res == null || res.get("tx") == null)
            {
                return null;
            }
            String txStr = (String) res.get("tx");
            Transaction tx = new Transaction();
            try {
                tx.parse(new NulsByteBuffer(HexUtil.decode(txStr),0));
                return tx.getTime();
            } catch (NulsException e) {
                return null;
            }
        });
    }

}
