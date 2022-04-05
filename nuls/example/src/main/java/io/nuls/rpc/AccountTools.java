package io.nuls.rpc;

import io.nuls.Config;
import io.nuls.base.data.CoinFrom;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.StringUtils;
import io.nuls.core.parse.MapUtils;
import io.nuls.core.rpc.info.Constants;
import io.nuls.core.rpc.model.ModuleE;
import io.nuls.core.rpc.model.message.Response;
import io.nuls.core.rpc.netty.processor.ResponseMessageProcessor;
import io.nuls.rpc.vo.Account;

import java.math.BigInteger;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import java.util.function.Function;
import java.util.List;
/**
 * @Author: zhoulijun
 * @Time: 2019-06-12 14:06
 * @Description: 功能描述
 */
@Component
public class AccountTools implements CallRpc {

    @Autowired
    Config config;

    public Account getAccountByAddress(String address) throws NulsRuntimeException {
        Map<String, Object> param = new HashMap<>(2);
        param.put("chainId", config.getChainId());
        param.put("address", address);
        return callRpc(ModuleE.AC.name, "ac_getAccountByAddress", param, (Function<Map<String, Object>, Account>) res -> {
                    if (res == null) {
                        return null;
                    }
                    return MapUtils.mapToBean(res, new Account());
                }
        );
    }


    /**
     * 账户验证
     * account validate
     *
     * @param chainId
     * @param address
     * @param password
     * @return validate result
     */
    public boolean accountValid(int chainId, String address, String password) throws NulsRuntimeException {
        Map<String, Object> callParams = new HashMap<>(4);
        callParams.put(Constants.CHAIN_ID, chainId);
        callParams.put("address", address);
        callParams.put("password", password);
        return callRpc(ModuleE.AC.abbr, "ac_getPriKeyByAddress", callParams, (Function<Map<String, Object>, Boolean>) res -> StringUtils.isNotBlank((String)res.get("priKey")));
    }


    /**
     * 获取账户私钥
     * account validate
     *
     * @param chainId
     * @param address
     * @param password
     * @return validate result
     */
    public String getAddressPriKey(int chainId, String address, String password) throws NulsRuntimeException {
        Map<String, Object> callParams = new HashMap<>(4);
        callParams.put(Constants.CHAIN_ID, chainId);
        callParams.put("address", address);
        callParams.put("password", password);
        return callRpc(ModuleE.AC.abbr, "ac_getPriKeyByAddress", callParams, (Function<Map<String, Object>, String>) res -> (String) res.get("priKey"));
    }

    /**
     * 创建账户
     * @param chainId
     * @param password
     * @return
     */
    public String createAccount(int chainId, String password) throws NulsRuntimeException{
        Map<String, Object> params = new HashMap<>();
        params.put(Constants.CHAIN_ID, chainId);
        params.put("count", 1);
        params.put("password", password);
        return callRpc(ModuleE.AC.abbr, "ac_createAccount", params, (Function<Map<String, Object>, String>) res ->{
            List<String> list = (List<String>) res.get("list");
            return list.get(0);
        });
    }


    /**
     * 账户修改密码
     * @param chainId
     * @param address
     * @param oldPassword
     * @param newPassword
     * @return
     * @throws NulsRuntimeException
     */
    public Boolean updateAccountPassword(int chainId, String address, String oldPassword, String newPassword) throws NulsRuntimeException{
        Map<String, Object> params = new HashMap<>();
        params.put(Constants.CHAIN_ID, chainId);
        params.put("address", address);
        params.put("password", oldPassword);
        params.put("newPassword", newPassword);
        return callRpc(ModuleE.AC.abbr, "ac_updatePassword", params, (Function<Map<String, Object>, Boolean>) res ->{
            boolean value = (boolean) res.get("value");
            return value;
        });
    }

    /**
     * 转账
     * @return
     * @throws NulsRuntimeException
     */
    public Boolean transfer(int chainId,int assetsId, String from_address,String to_address,String password, BigInteger amount,String remark) throws NulsRuntimeException{
        Map<String, Object> params = new HashMap<>();
        params.put(Constants.CHAIN_ID, chainId);
        List<HashMap<String,Object>> inputs = new ArrayList<>();
        HashMap<String,Object> from = new HashMap<>();
        from.put("address",from_address);
        from.put("assetsChainId", chainId);
        from.put("assetsId", assetsId);
        from.put("amount", amount);
        from.put("password", password);
        inputs.add(from);
        params.put("inputs",inputs);
        List<HashMap<String,Object>> outputs = new ArrayList<>();
        HashMap<String,Object> to = new HashMap<>();
        to.put("address",to_address);
        to.put("assetsChainId", chainId);
        to.put("assetsId", assetsId);
        to.put("amount", amount);
        outputs.add(to);
        params.put("outputs",outputs);
        params.put("remark",remark);
        return callRpc(ModuleE.AC.abbr, "ac_transfer", params, (Function<Map<String, Object>, Boolean>) res -> StringUtils.isNotBlank((String)res.get("value")));
    }

    public String importAccountByPriKey(int chainId, String priKey, String password) throws NulsRuntimeException {
        Map<String, Object> callParams = new HashMap<>(4);
        callParams.put(Constants.CHAIN_ID, chainId);
        callParams.put("priKey", priKey);
        callParams.put("password", password);
        callParams.put("overwrite", false);
        return callRpc(ModuleE.AC.abbr, "ac_importAccountByPriKey", callParams, (Function<Map<String, Object>, String>) res -> (String) res.get("address"));
    }



}
