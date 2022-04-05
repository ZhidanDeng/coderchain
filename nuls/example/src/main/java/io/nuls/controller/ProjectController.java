package io.nuls.controller;

import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.data.NulsHash;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
import io.nuls.controller.core.BaseController;
import io.nuls.controller.core.Result;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.BigIntegerUtils;

import io.nuls.core.model.StringUtils;
import io.nuls.model.po.Alias;
import io.nuls.model.po.Project;
import io.nuls.model.po.Vote;
import io.nuls.model.po.VoteDetail;
import io.nuls.model.vo.ProjectInfoReq;
import io.nuls.model.vo.ProjectListReq;
import io.nuls.model.vo.VoteReq;
import io.nuls.rpc.AccountTools;
import io.nuls.service.ProjectService;
import io.nuls.service.UserService;

import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import java.math.BigInteger;
import java.util.*;

@Path("/project")
@Component
public class ProjectController implements BaseController {
    @Autowired
    Config config;

    @Autowired
    AccountTools accountTools;

    @Autowired
    UserService userService;

    @Autowired
    ProjectService projectService;

    /**
     * 新建项目
     */
    @Path("createProject")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result createProject(ProjectInfoReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            if (!projectService.validProjectName(req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            if (!projectService.validProjectType(req.getProjectType())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目类型格式错误");
            }
            Alias user = userService.getUserInfoByUserName(req.getUserName());
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            // 校验项目是否已经存在
            if (projectService.isProjectExist(address,req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名已存在");
            }
            int chainId = config.getChainId();
            accountTools.accountValid(chainId,address,req.getPassword());


            // 发起创建项目的交易
            Transaction transaction = projectService.createProject(address, req.getPassword(), req.getProjectName(), req.getDescription(), req.getProjectType());
            if (transaction != null && transaction.getHash() != null) {
                return new Result<>(true,"项目创建成功");
            }
            else
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED,"创建项目交易失败");
            }

        });
    }

    /**
     * 获取项目信息
     */
    @Path("getProjectInfo")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String, Object>> getProjectInfo(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!projectService.validProjectName(params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            Project project = projectService.getProject(address, params.get("projectName"));
            if (project == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "项目不存在");
            }
            HashMap<String, Object> result = new HashMap<>();
            result.put("projectName",project.getProjectName());
            result.put("projectType",project.getProjectType());
            result.put("createTime",project.getCreateTime());
            result.put("description",project.getDescription());
            return new Result<>(result);

        });
    }


    /**
     * 更新项目
     */
    @Path("updateProject")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result updateProject(ProjectInfoReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            if (!projectService.validProjectName(req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            if (!projectService.validProjectType(req.getProjectType())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目类型格式错误");
            }
            Alias user = userService.getUserInfoByUserName(req.getUserName());
            if (user == null)
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(req.getUserName());
            // 校验项目是否已经存在
            if (!projectService.isProjectExist(address,req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目不存在");
            }
            int chainId = config.getChainId();

            accountTools.accountValid(chainId,address,req.getPassword());

            // 发起更新项目的交易
            Transaction transaction = projectService.updateProject(address, req.getPassword(), req.getProjectName(), req.getDescription(), req.getProjectType());
            if (transaction != null && transaction.getHash() != null) {
                return new Result(true,"项目更新成功");
            }
            else
            {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED,"更新项目交易失败");
            }


        });
    }

    /**
     * 删除项目
     */
    @Path("deleteProject")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result deleteProject(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(params.get("password"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            if (!projectService.validProjectName(params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            // 校验项目是否存在
            if (!projectService.isProjectExist(address,params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目不存在");
            }
            int chainId = config.getChainId();

            accountTools.accountValid(chainId,address,params.get("password"));

            // 发起删除项目的交易
            Transaction transaction = projectService.deleteProject(address, params.get("password"),params.get("projectName"));
            if (transaction != null && transaction.getHash() != null) {
                // 删除成功
                return new Result(true,"删除成功");
            }
            else
            {
                // 删除失败
                return new Result(false,"删除失败");
            }
        });
    }



    /**
     * 支持项目
     */
    @Path("voteProject")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result voteProject(VoteReq req)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(req.getUserName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validUserName(req.getProjectAuthor())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!userService.validPassword(req.getPassword())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"密码格式错误");
            }
            if (!projectService.validProjectName(req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            // 根据别名获取地址
            Alias from_user = userService.getUserInfoByUserName(req.getUserName());
            if (from_user == null) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            Alias to_user = userService.getUserInfoByUserName(req.getProjectAuthor());
            if (to_user == null) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "用户不存在");
            }
            int chainId = config.getChainId();
            String from_address = AddressTool.getStringAddressByBytes(from_user.getAddress());
            String to_address = AddressTool.getStringAddressByBytes(to_user.getAddress());
            //验证收款地址是否是平台地址
            if (StringUtils.equals(from_address,to_address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "不能给自己的项目投票");
            }
            // 校验项目是否存在
            if (!projectService.isProjectExist(to_address,req.getProjectName())) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目不存在");
            }
            if (BigIntegerUtils.isEqualOrLessThan(req.getVoteCount(), BigInteger.ZERO)) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"转账金额小于或等于0");
            }
            accountTools.accountValid(chainId,from_address,req.getPassword());

            // 发起删除项目的交易
            Transaction transaction = projectService.voteProject(from_address,req.getPassword(),to_address,req.getProjectName(),req.getVoteCount());
            if (transaction != null && transaction.getHash() != null) {
                // 删除成功
                return new Result(true,"投票成功");
            }
            else
            {
                // 删除失败
                return new Result(false,"投票失败");
            }

        });
    }


    /**
     * 获取项目投票结果
     */
    @Path("getProjectSupport")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String,Object>> getProjectSupport(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!projectService.validProjectName(params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));
            if (!AddressTool.validAddress(config.getChainId(), address)) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }
            // 校验项目是否存在
            if (!projectService.isProjectExist(address,params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目不存在");
            }
            BigInteger vote_count = BigInteger.ZERO;
            List<HashMap<String,Object>> vote_list = new ArrayList<>();
            int tx_count = 0;
            Vote vote = projectService.getProjectSupport(address,params.get("projectName"));
            if (vote != null)
            {
                vote_count = vote.getVoteCount();
                tx_count = vote.getTxCount();
                for (int i = 0; i < tx_count; i++) {
                    HashMap<String,Object> detail = new HashMap<>();
                    NulsHash hash = vote.getTxHashList().get(i);
                    VoteDetail v = projectService.getProjectSupportDetail(hash.getBytes());
                    detail.put("tx",hash.toHex());
                    detail.put("supportCount",v.getVoteCount());
                    detail.put("createTime",v.getCreateTime());
                    vote_list.add(detail);
                }
            }
            HashMap<String,Object> result = new HashMap<>();
            result.put("supportCount",vote_count);
            result.put("supportList",vote_list);
            result.put("txCount",tx_count);
            return new Result<>(result);
        });
    }

    /**
     * 获取交易列表
     */
    @Path("getSupportDetailList")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String, Object>>> getSupportDetailList()  {
        return call(() -> {
            // 参数校验
            List<HashMap<String, Object>> txList = projectService.getSupportDetailList();
            return new Result<>(txList);
        });
    }

    /**
     * 获取项目投票结果
     */
    @Path("getSupportDetail")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<HashMap<String,Object>> getSupportDetail(Map<String,String> params)  {
        return call(() -> {
            // 参数校验
            if (!NulsHash.validHash(params.get("tx")))
            {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"交易hash格式错误");
            }
            VoteDetail v = projectService.getProjectSupportDetail(HexUtil.decode(params.get("tx")));
            if (v != null)
            {
                HashMap<String,Object> detail = new HashMap<>();
                detail.put("hash",params.get("tx"));
                detail.put("supportCount",v.getVoteCount());
                detail.put("createTime",v.getCreateTime());
                detail.put("projectName",v.getProjectName());
                detail.put("toAddress", AddressTool.getStringAddressByBytes(v.getToAddress()));
                detail.put("fromAddress",AddressTool.getStringAddressByBytes(v.getFromAddress()));
                Alias user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(v.getFromAddress()));
                detail.put("fromUser",user.getAlias());
                Alias to_user = userService.getUserInfoByAddress(AddressTool.getStringAddressByBytes(v.getToAddress()));
                detail.put("toUser",to_user.getAlias());
                return new Result<>(detail);
            }
            else
            {
                return new Result(false,"交易不存在");
            }

        });
    }



    @Path("isProjectExist")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result isProjectExist(Map<String,String> params)
    {
        return call(() -> {
            // 参数校验
            if (!userService.validUserName(params.get("userName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"用户名格式错误");
            }
            if (!projectService.validProjectName(params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }
            // 根据别名获取地址
            String address = userService.getAddressByAlias(params.get("userName"));
            if (projectService.isProjectExist(address,params.get("projectName"))){
                return new Result(true,"项目存在");
            }
            else
            {
                return new Result(false,"项目不存在");
            }
        });
    }

    @Path("isProjectExistByAddress")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result isProjectExistByAddress(Map<String,String> params)
    {
        return call(() -> {
            // 参数校验
            if (!AddressTool.validAddress(config.getChainId(), params.get("address"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.FAILED, "地址格式不合理");
            }

            if (!projectService.validProjectName(params.get("projectName"))) {
                throw new NulsRuntimeException(CommonCodeConstanst.PARAMETER_ERROR,"项目名格式错误");
            }

            if (projectService.isProjectExist(params.get("address"),params.get("projectName"))){
                return new Result(true,"项目存在");
            }
            else
            {
                return new Result(false,"项目不存在");
            }
        });
    }

    @Path("getUserProject")
    @Produces(MediaType.APPLICATION_JSON)
    @POST
    public Result<List<HashMap<String,Object>>> getUserProject(ProjectListReq req)
    {
        return call(() -> {
            List<byte[]> keys = new ArrayList<>();
            for (int i =0 ; i < req.getList().size();i++)
            {
                keys.add(StringUtils.bytes(req.getList().get(i)));
            }
            return new Result<>(projectService.getUserProject(keys));
        });
    }

    @Path("getAllProject")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String,Object>>> getAllProject()
    {
        return call(() -> {
            List<HashMap<String, Object>> projectList = projectService.getAllProject();
            return new Result<>(projectList);
        });
    }

    /**
     * 获取项目列表
     */
    @Path("getProjectList")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String, Object>>> getProjectList()  {
        return call(() -> {
            List<HashMap<String, Object>> projectList = projectService.getProjectList();
            return new Result<>(projectList);
        });
    }

    /**
     * 获取所有项目投票结果
     */
    @Path("getAllProjectSupport")
    @Produces(MediaType.APPLICATION_JSON)
    @GET
    public Result<List<HashMap<String, Object>>> getAllProjectSupport()  {
        return call(() -> {
            List<HashMap<String, Object>> projectList = projectService.getAllProjectSupport();
            return new Result<>(projectList);
        });
    }

}
