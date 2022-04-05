package io.nuls.controller;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
import io.nuls.controller.core.BaseController;
import io.nuls.controller.core.Result;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.BigIntegerUtils;
import io.nuls.core.model.StringUtils;
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.vo.AccountBalance;
import io.nuls.service.TxService;
import io.nuls.service.UserService;


import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import java.math.BigInteger;
import java.util.Map;
@Path("/account")
@Component
public class AccountController implements BaseController {

    @Autowired
    Config config;

    @Autowired
    AccountTools accountTools;

    @Autowired
    LegderTools legderTools;


    @Autowired
    UserService userService;

    @Autowired
    TxService txService;

    /**
     * 快速初始化（链初始化，涉及密码不开源）
     */
    @Path("init")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result init(Map<String,String> params)  {
        return call(() -> {
            // 导入种子出块节点地址（改待定）
            // 私钥为 b54db432bba7e13a6c4a28f65b925b18e63bcb79143f7b894fa735d5d3d09db5
            // 密码nuls123456
            String result;
            int chainID = config.getChainId();
            result = accountTools.importAccountByPriKey(chainID,"b54db432bba7e13a6c4a28f65b925b18e63bcb79143f7b894fa735d5d3d09db5","nuls123456");
            if (!StringUtils.equals(result, Constant.BLOCK_SEED_ADDRESS))
            {
                return new Result(false,"种子出块节点初始化失败");
            }
            // 导入创世块地址1
            // 私钥为 670f55396c61d0445d80be86c988aa733501e34f6add7e9b0d6c6f74a5ded45d
            result = accountTools.importAccountByPriKey(chainID,"670f55396c61d0445d80be86c988aa733501e34f6add7e9b0d6c6f74a5ded45d","denglu1Dev");
            if (!StringUtils.equals(result, Constant.BLOCK_INIT_ADDRESS1))
            {
                return new Result(false,"创世块地址1初始化失败");
            }
            // 导入创世块地址2
            // 私钥为 4b1f67bdc7d55936ce4278862aa346a225d529b3fb6e6d27377b6f4c549d69db
            result = accountTools.importAccountByPriKey(chainID,"4b1f67bdc7d55936ce4278862aa346a225d529b3fb6e6d27377b6f4c549d69db","denglu1Dev");
            if (!StringUtils.equals(result, Constant.BLOCK_INIT_ADDRESS2))
            {
                return new Result(false,"创世块地址2初始化失败");
            }

            // 导入平台管理地址
            // 私钥为 09cdc565d51b871717b76da3d7a4f0a7986a1e2753967161c96cc6f68646795c
            result = accountTools.importAccountByPriKey(chainID,"09cdc565d51b871717b76da3d7a4f0a7986a1e2753967161c96cc6f68646795c","denglu1Dev");
            if (!StringUtils.equals(result, Constant.BLOCK_CODER_ADDRESS))
            {
                return new Result(false,"平台管理地址初始化失败");
            }
            // 暂时不为管理地址设置别名

            return new Result(true,"初始化成功");
        });
    }

    /**
     * 通过地址快速转账
     */
    @Path("transferByAddress")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result transferByAddress(Map<String,String> params)  {
        return call(() -> {

            // 获取并验证地址
            String address = params.get("address");
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "发款用户地址格式不合理");
            }
            String to_address = params.get("toAddress");
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), to_address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "收款用户地址格式不合理");
            }

            if (!userService.validPassword(params.get("password"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"发款用户密码格式错误");
            }
            // 验证密码是否正确
            accountTools.accountValid(config.getChainId(),address, params.get("password"));



            if (BigIntegerUtils.isEqualOrLessThan(BigIntegerUtils.stringToBigInteger(params.get("amount")),BigInteger.ZERO)) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"转账金额小于0");
            }

            if (!StringUtils.isNotBlank(params.get("remark"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易备注必填");
            }
            AccountBalance accountBalance = legderTools.getBalanceAndNonce(config.getChainId(), address, config.getChainId(), config.getAssetId());
            //检查余额是否充足
            BigInteger mainAsset = accountBalance.getAvailable();
            //余额不足
            if (BigIntegerUtils.isLessThan(mainAsset, BigIntegerUtils.stringToBigInteger(params.get("amount")))) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户余额不足");
            }

            boolean result = accountTools.transfer(config.getChainId(),config.getAssetId(),address,to_address,params.get("password"),BigIntegerUtils.stringToBigInteger(params.get("amount")),params.get("remark"));
            if (result)
            {
                return new Result(true,"转账成功");
            }
            else
            {
                return new Result(false,"转账失败");
            }
        });
    }

    /**
     * 通过用户快速转账
     */
    @Path("transferByName")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result transferByName(Map<String,String> params)  {
        return call(() -> {
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validUserName(params.get("toUserName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"收款用户名格式错误");
            }
            if (StringUtils.equals(params.get("userName"),params.get("toUserName")))
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"收款用户与发款用户不能一致");
            }
            // 获取并验证地址
            String address = userService.getAddressByAlias(params.get("userName"));
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "发款用户地址格式不合理");
            }
            String to_address = userService.getAddressByAlias(params.get("toUserName"));
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), to_address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "收款用户地址格式不合理");
            }

            if (!userService.validPassword(params.get("password"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"发款用户密码格式错误");
            }
            // 验证密码是否正确
            accountTools.accountValid(config.getChainId(),address, params.get("password"));
            if (BigIntegerUtils.isEqualOrLessThan(BigIntegerUtils.stringToBigInteger(params.get("amount")),BigInteger.ZERO)) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"转账金额小于0");
            }

            if (!StringUtils.isNotBlank(params.get("remark"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易备注必填");
            }
            AccountBalance accountBalance = legderTools.getBalanceAndNonce(config.getChainId(), address, config.getChainId(), config.getAssetId());
            //检查余额是否充足
            BigInteger mainAsset = accountBalance.getAvailable();
            //余额不足
            if (BigIntegerUtils.isLessThan(mainAsset, BigIntegerUtils.stringToBigInteger(params.get("amount")).add(config.getTransferFee()))) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户余额不足");
            }
            // 发起转账交易
            Transaction transaction = txService.transfer(address,to_address,params.get("password"),BigIntegerUtils.stringToBigInteger(params.get("amount")),params.get("remark"));
            if (transaction != null && transaction.getHash() != null) {
                return new Result(true,"转账成功");
            }
            else
            {
                return new Result(false,"转账失败");
            }
        });
    }
}

