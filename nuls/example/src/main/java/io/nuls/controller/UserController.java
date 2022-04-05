package io.nuls.controller;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
import io.nuls.controller.core.BaseController;
import io.nuls.controller.core.Result;
import io.nuls.core.crypto.AESEncrypt;
import io.nuls.core.crypto.ECKey;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.CryptoException;
import io.nuls.core.model.BigIntegerUtils;
import io.nuls.core.model.ObjectUtils;
import io.nuls.core.model.StringUtils;
import io.nuls.model.po.Alias;
import io.nuls.model.po.UserTx;
import io.nuls.model.vo.UpdatePasswordReq;
import io.nuls.model.vo.UpdateUserImgReq;
import io.nuls.model.vo.UpdateUserInfoReq;
import io.nuls.model.vo.UserLoginReq;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.rpc.TransactionTools;
import io.nuls.rpc.vo.Account;
import io.nuls.service.TxService;
import io.nuls.service.UserService;

import javax.ws.rs.GET;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import java.math.BigInteger;
import java.util.*;

@Path("/user")
@Component
public class UserController implements BaseController {
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

    @Autowired
    TransactionTools transactionTools;

    /**
     * 注册
     */
    @Path("register")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<Map<String, String>> register(UserLoginReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            // 判断别名是否可用
            if (userService.isAliasUsable(req.getUserName())){
                int chainId = config.getChainId();
                // 根据密码创建地址
                String address = accountTools.createAccount(chainId, req.getPassword());
                // 发起为该地址赋予别名的交易
                Transaction transaction = userService.setAlias(address, req.getUserName(),req.getPassword());
                if (transaction != null && transaction.getHash() != null) {
                    // 给这个地址一定的费用
                    if (req.getUserName().startsWith("2022_"))
                    {
                        boolean result = accountTools.transfer(config.getChainId(),config.getAssetId(),Constant.BLOCK_INIT_ADDRESS1,address,"denglu1Dev",BigIntegerUtils.stringToBigInteger("50000000000"),"初始化用户");
                    }
                    String txHash = transaction.getHash().toHex();
                    Map<String, String> result = new HashMap<>();
                    result.put("address",address);
                    result.put("username",req.getUserName());
                    result.put("txHash",txHash);
                    return new Result<>(result);
                }
                else
                {
                    throw new NulsRuntimeException(CommonCodeConstanst.FAILED,"用户注册交易失败");
                }
            }
            else
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名已被注册");
            }
        });
    }
    @Path("isUserNameUsable")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result isUserNameUsable(Map<String,String> params)
    {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (userService.isAliasUsable(params.get("userName"))){
                return new Result(true,"用户名可用");
            }
            else
            {
                return new Result(false,"用户名已被注册");
            }
        });
    }
    /**
     * 登录
     */
    @Path("login")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<Map<String, Object>> login(UserLoginReq req)  {
        return call(() -> {

            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            int chainId = config.getChainId();
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            accountTools.accountValid(chainId,address,req.getPassword());

            // 密码正确，登录成功，返回用户信息
            Alias user = userService.getUserInfoByAddress(address);
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            HashMap<String, String> result = new HashMap<>();
            result.put("address",AddressTool.getStringAddressByBytes(user.getAddress()));
            result.put("userName",user.getAlias());
            result.put("sex",user.getSex()+"");
            result.put("avatar",user.getAvatar());
            result.put("description",user.getDescription());
            return new Result<>(result);

        });
    }

    /**
     * 获取用户私钥
     */
    @Path("getUserPriKey")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<String> getUserPriKey(UserLoginReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }

            int chainId = config.getChainId();

            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            // 校验账号密码是否正确
            accountTools.accountValid(chainId,address,req.getPassword());

            // 密码正确，登录成功，返回用户私钥
            String priKey = accountTools.getAddressPriKey(chainId,address,req.getPassword());
            return new Result<>(priKey);

        });
    }

    /**
     * 获取用户余额
     */
    @Path("getUserBalance")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<BigInteger> getUserBalance(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            // 密码正确，登录成功，返回用户私钥
            BigInteger balance = legderTools.getBalanceAndNonce(config.getChainId(),address,config.getChainId(),config.getAssetId()).getAvailable();
            return new Result<>(balance);
        });
    }

    @Path("getUserBalanceByAddress")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<BigInteger> getUserBalanceByAddress(Map<String,String> params)  {
        return call(() -> {

            if (!AddressTool.validAddress(config.getChainId(), params.get("address"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            // 密码正确，登录成功，返回用户私钥
            BigInteger balance = legderTools.getBalanceAndNonce(config.getChainId(),params.get("address"),config.getChainId(),config.getAssetId()).getAvailable();
            return new Result<>(balance);
        });
    }

    /**
     * 修改用户密码
     */
    @Path("updatePassword")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result updatePassword(UpdatePasswordReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getOldPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"旧密码格式错误");
            }
            if (!userService.validPassword(req.getNewPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"新密码格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            if (address == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            // 验证旧密码是否正确
            accountTools.accountValid(config.getChainId(),address, req.getOldPassword());


            // 开始更新密码
            boolean result = accountTools.updateAccountPassword(config.getChainId(),address,req.getOldPassword(),req.getNewPassword());
            if (result)
            {
                return new Result(true,"修改密码成功");
            }
            else
            {
                return new Result(false,"修改密码失败");
            }
        });
    }

    /**
     * 头像路径更新，做一个交易处理
     */
    @Path("updateUserImage")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result updateUserImage(UpdateUserImgReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            Alias user = userService.getUserInfoByAddress(address);
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            accountTools.accountValid(config.getChainId(),address, req.getPassword());
            // 发起为该地址赋予别名的交易
            Transaction transaction = userService.updateUserImage(address, req.getPassword(), req.getImgName());
            if (transaction != null && transaction.getHash() != null) {
                return new Result(true,"图像修改成功");
            }
            else
            {
                return new Result(true,"图像修改交易失败");
            }
        });
    }


    /**
     * 修改用户信息
     */
    @Path("updateUserInfo")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result updateUserInfo(UpdateUserInfoReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            if (!userService.validUserSex(req.getSex())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户性别格式错误");
            }
            Alias user = userService.getUserInfoByUserName(req.getUserName());
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }

            Alias userInfo = new Alias(AddressTool.getAddress(address),req.getUserName(),req.getSex(),req.getAvatar(),req.getDescription());
            // 发起为该地址赋予别名的交易
            Transaction transaction = userService.updateUserInfo(userInfo,address, req.getPassword());
            if (transaction != null && transaction.getHash() != null) {
                return new Result(true,"用户信息修改成功");
            }
            else
            {
                return new Result(true,"用户信息修改交易失败");
            }
        });
    }

    /**
     * 获取用户信息
     */
    @Path("getUserInfoByAddress")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, String>> getUserInfoByAddress(Map<String,String> params)  {
        return call(() -> {
            // 判断是不是有效地址
            if (!AddressTool.validAddress(config.getChainId(), params.get("address"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            Alias user = userService.getUserInfoByAddress(params.get("address"));
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            HashMap<String, String> result = new HashMap<>();
            result.put("address",AddressTool.getStringAddressByBytes(user.getAddress()));
            result.put("userName",user.getAlias());
            result.put("sex",user.getSex());
            result.put("avatar",user.getAvatar());
            result.put("description",user.getDescription());
            return new Result<>(result);
        });
    }

    /**
     * 获取用户信息
     */
    @Path("getUserInfoByName")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, String>> getUserInfoByName(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            Alias user = userService.getUserInfoByUserName(params.get("userName"));
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }

            HashMap<String, String> result = new HashMap<>();
            result.put("address",AddressTool.getStringAddressByBytes(user.getAddress()));
            result.put("userName",user.getAlias());
            result.put("sex",user.getSex()+"");
            result.put("avatar",user.getAvatar());
            result.put("description",user.getDescription());
            return new Result<>(result);
        });
    }

    /**
     * 获取用户信息
     */
    @Path("getUserTx")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, Object>> getUserTx(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!StringUtils.equals(params.get("type") ,"0") && !StringUtils.equals(params.get("type") ,"1"))
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易类型格式错误");
            }
            Alias user = userService.getUserInfoByUserName(params.get("userName"));
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            // 根据别名获取地址
            String address = AddressTool.getStringAddressByBytes(user.getAddress());
            UserTx tx = txService.getUserTx(address, params.get("type"));
            HashMap<String,Object> res = new HashMap<>();
            if (tx != null)
            {
                List<HashMap<String,Object> > hashList = new ArrayList<>();
                for (int i = 0 ;i <tx.getTxCount();i++)
                {
                    HashMap<String,Object> hashMap = new HashMap<>();
                    String hash = tx.getTxHashList().get(i).toHex();
                    hashMap.put("hash",hash);
                    hashMap.put("createTime",transactionTools.getTxTime(hash));
                    hashList.add(hashMap);
                }
                res.put("txCount",tx.getTxCount());
                res.put("hashList",hashList);
            }
            else
            {
                res.put("txCount",BigInteger.ZERO);
                res.put("hashList",new ArrayList<>());
            }
            return new Result<>(res);
        });
    }

    /**
     * 获取用户列表
     */
    @Path("checkPassword")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result checkPassword(UserLoginReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            int chainId = config.getChainId();
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            accountTools.accountValid(chainId,address,req.getPassword());
            return new Result(true,"密码正确");
        });
    }

    /**
     * 获取用户列表
     */
    @Path("getUserList")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String, Object>>> getUserList()  {
        return call(() -> {
            // 根据别名获取地址
            List<HashMap<String, Object>> userList = userService.getAliasList();
            return new Result<>(userList);
        });
    }

    /**
     * 获取用户私钥
     */
    @Path("getKeysPair")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, Object>> getKeysPair(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(params.get("password"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            boolean isCompressed = true;
            if (!StringUtils.equals(params.get("isCompressed") ,"0"))
            {
                isCompressed = false;
            }

            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));

            Account account = accountTools.getAccountByAddress(address);


            ECKey eckey = null;
            byte[] unencryptedPrivateKey;
            //判断当前账户是否存在私钥，如果不存在私钥这为加密账户
            BigInteger newPriv = null;
            ObjectUtils.canNotEmpty(params.get("password"), "the password can not be empty");
            try {
                unencryptedPrivateKey = AESEncrypt.decrypt(HexUtil.decode(account.getEncryptedPrikeyHex()), params.get("password"));
                newPriv = new BigInteger(1, unencryptedPrivateKey);
            } catch (CryptoException e) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "password is wrong");
            }
            eckey = ECKey.fromPrivate(newPriv,isCompressed);

            HashMap<String, Object> res = new HashMap<>();
            res.put("ac_pri",account.getEncryptedPrikeyHex());
            res.put("ac_pub",HexUtil.decode(account.getPubkeyHex()));
            res.put("unencryptedPrivateKey",unencryptedPrivateKey);
            res.put("newPriv",newPriv);
            res.put("pri",HexUtil.encode(eckey.getPrivKeyBytes()));
            res.put("pub",HexUtil.encode(eckey.getPubKey()));
            res.put("pubAsHex",eckey.getPrivateKeyAsHex());
            return new Result<>(res);
        });
    }
}
