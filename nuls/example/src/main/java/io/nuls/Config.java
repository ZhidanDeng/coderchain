package io.nuls;

import io.nuls.core.core.annotation.Configuration;

import java.io.File;
import java.math.BigInteger;

/**
 * @Author: zhoulijun
 * @Time: 2019-06-12 13:53
 * @Description: 功能描述
 */
@Configuration(domain = "coder")
public class Config {

    /**
     * 当前运行的chain id 来自配置文件
     */
    private int chainId;

    /**
     * 默认资产id
     */
    private int assetId;

    /**
     * 申请邮箱地址手续费
     */
    private BigInteger mailAddressFee;

    /**
     * 发送邮件手续费
     */
    private BigInteger sendMailFee;

    /**
     * 设置别名费用（登录时进行的交易）
     */
    private BigInteger userRegisterFee;

    /**
     * 更新用户信息费用
     */
    private BigInteger updateUserInfoFee;

    /**
     * 更新用户图片路径费用
     */
    private BigInteger updateUserImgFee;

    private BigInteger createProjectFee;

    private BigInteger updateProjectInfoFee;

    private BigInteger deleteProjectFee;

    private BigInteger transferFee;

    private String dataPath;


    public int getChainId() {
        return chainId;
    }

    public void setChainId(int chainId) {
        this.chainId = chainId;
    }

    public int getAssetId() {
        return assetId;
    }


    public BigInteger getMailAddressFee() {
        return mailAddressFee;
    }


    public String getDataPath() {
        return dataPath + File.separator + "coder";
    }


    public BigInteger getSendMailFee() {
        return sendMailFee;
    }


    public BigInteger getUserRegisterFee() {
        return userRegisterFee;
    }


    public BigInteger getUpdateUserImgFee() {
        return updateUserImgFee;
    }

    public BigInteger getUpdateUserInfoFee() {
        return updateUserInfoFee;
    }

    public BigInteger getCreateProjectFee() {
        return createProjectFee;
    }

    public BigInteger getUpdateProjectInfoFee() {
        return updateProjectInfoFee;
    }

    public BigInteger getDeleteProjectFee() {
        return deleteProjectFee;
    }

    public BigInteger getTransferFee() {
        return transferFee;
    }
}
