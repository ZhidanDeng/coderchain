package io.nuls.constant;

/**
 * @Author: zhoulijun
 * @Time: 2019-06-11 20:06
 * @Description: 常量定义
 */
public class Constant {

    /**
     * 创建邮箱地址的交易类型
     */
    public static final int TX_TYPE_CREATE_MAIL_ADDRESS = 200;

    /**
     * 发送邮件的交易类型
     */
    public static final int TX_TYPE_SEND_MAIL = 201;

    /**
     * 设置用户别名交易类型（用户注册）
     */
    public static final int TX_TYPE_REGISTER_USER_ALIAS = 202;

    /**
     * 更新用户信息交易类型
     */
    public static final int TX_TYPE_UPDATE_USER_INFO = 203;

    /**
     * 更新用户图片路径交易类型
     */
    public static final int TX_TYPE_UPDATE_USER_IMG = 204;

    /**
     * 创建项目交易类型
     */
    public static final int TX_TYPE_CREATE_PROJECT = 205;

    /**
     * 项目更新交易类型
     */
    public static final int TX_TYPE_UPDATE_PROJECT = 206;

    /**
     * 项目投票交易类型
     */
    public static final int TX_TYPE_SUPPORT_PROJECT = 207;

    /**
     * 项目删除交易类型
     */
    public static final int TX_TYPE_DELETE_PROJECT = 208;

    /**
     * 项目转账交易类型
     */
    public static final int TX_TYPE_TRANSFER = 209;


    /**
     * 黑洞地址
     * 用于接收系统收费
     */
    public static final String BLACK_HOLE_ADDRESS = "tNULSeBaMtsumpXhfEZBU2pMEz7SHLcx5b2TQr";

    /**
     * 种子出块地址
     */

     public static final String BLOCK_SEED_ADDRESS = "tNULSeBaMkrt4z9FYEkkR9D6choPVvQr94oYZp";


    /**
     * 创世块地址1
     */
    public static final String BLOCK_INIT_ADDRESS1 = "tNULSeBaMsbtL8fVibmjWPLZ9VYVC5gtAA3xwm";

    /**
     * 创世块地址2
     */
    public static final String BLOCK_INIT_ADDRESS2 = "tNULSeBaMkiGp7FqtKfmCDj7SfwEw9xhV3q2WL";

    /**
     * 平台公共地址
     */
    public static final String BLOCK_CODER_ADDRESS = "tNULSeBaMuYnzktpzTPAedaXSSm1zMtPd9SVbg";

}
