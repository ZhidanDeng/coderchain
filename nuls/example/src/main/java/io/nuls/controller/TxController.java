package io.nuls.controller;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.data.*;
import io.nuls.constant.Constant;
import io.nuls.controller.core.BaseController;
import io.nuls.controller.core.Result;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.StringUtils;
import io.nuls.model.po.Alias;
import io.nuls.model.po.TxDetail;
import io.nuls.rpc.TransactionTools;
import io.nuls.service.TxService;
import io.nuls.service.UserService;
import org.glassfish.grizzly.utils.ArrayUtils;

import javax.ws.rs.GET;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Path("/tx")
@Component
public class TxController implements BaseController {
    @Autowired
    Config config;

    @Autowired
    TxService txService;

    @Autowired
    UserService userService;

    @Autowired
    TransactionTools transactionTools;

    /**
     * 获取交易列表
     */
    @Path("getTransferList")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String, Object>>> getTransferList()  {
        return call(() -> {
            // 参数校验
            List<HashMap<String, Object>> txList = txService.getTransferList();
            return new Result<>(txList);
        });
    }

    @Path("getTransferDetail")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, Object>> getTransferDetail(Map<String,String> params)  {
        return call(() -> {
            // 参数校验

            if (!NulsHash.validHash(params.get("tx")))
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易hash格式错误");
            }
            TxDetail txDetail = txService.getTransferDetail(HexUtil.decode(params.get("tx")));
            if (txDetail != null)
            {
                HashMap<String, Object> res = new HashMap<>();
                res.put("txCount",txDetail.getTxCount());
                res.put("createTime",txDetail.getCreateTime());
                res.put("hash",params.get("tx"));
                res.put("toAddress", AddressTool.getStringAddressByBytes(txDetail.getToAddress()));
                if (!Arrays.equals(txDetail.getFromAddress(), AddressTool.getAddress(Constant.BLOCK_CODER_ADDRESS)) && !Arrays.equals(txDetail.getFromAddress(), AddressTool.getAddress(Constant.BLOCK_INIT_ADDRESS1))) {
                    Alias user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(txDetail.getFromAddress()));
                    res.put("fromUser",user.getAlias());
                    res.put("fromAddress",AddressTool.getStringAddressByBytes(txDetail.getFromAddress()));
                }
                else
                {
                    res.put("fromUser","coderChain");
                    res.put("fromAddress","");
                }
                Alias to_user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(txDetail.getToAddress()));
                res.put("toUser",to_user.getAlias());
                return new Result<>(res);
            }
            else
            {
                return new Result(false,"交易不存在");
            }
        });
    }

    @Path("getTx")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, Object>> getTx(Map<String,String> params)  {
        return call(() -> {
            // 参数校验

            if (!NulsHash.validHash(params.get("tx")))
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易hash格式错误");
            }
            HashMap<String, Object> txRes = transactionTools.getTx(params.get("tx"));
            if (txRes != null)
            {
                return new Result<>(txRes);
            }
            else
            {
                return new Result(false,"交易不存在");
            }

        });
    }
}
