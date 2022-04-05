package io.nuls;

import io.nuls.constant.Constant;
import io.nuls.constant.ProjectStorgeConstant;
import io.nuls.constant.TxStorageConstant;
import io.nuls.controller.core.WebServerManager;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.rpc.model.ModuleE;
import io.nuls.core.rpc.modulebootstrap.Module;
import io.nuls.core.rpc.modulebootstrap.RpcModuleState;
import io.nuls.rpc.TransactionTools;
import io.nuls.core.log.Log;
import io.nuls.constant.UserStorageConstant;
import io.nuls.core.rockdb.service.RocksDBService;


/**
 * @Author: zhoulijun
 * @Time: 2019-06-10 20:54
 * @Description: 功能描述
 */
@Component
public class MyModule {

    @Autowired
    Config config;

    @Autowired
    TransactionTools transactionTools;

    public RpcModuleState startModule(String moduleName){

        try {
            initDb();
        } catch (Exception e) {
            Log.error("BlockBootstrap init error!");

        }

        //注册交易
        transactionTools.registerTx(moduleName,
                Constant.TX_TYPE_CREATE_MAIL_ADDRESS,
                Constant.TX_TYPE_SEND_MAIL,
                Constant.TX_TYPE_REGISTER_USER_ALIAS,
                Constant.TX_TYPE_UPDATE_USER_INFO,
                Constant.TX_TYPE_UPDATE_USER_IMG,
                Constant.TX_TYPE_CREATE_PROJECT,
                Constant.TX_TYPE_SUPPORT_PROJECT,
                Constant.TX_TYPE_UPDATE_PROJECT,
                Constant.TX_TYPE_DELETE_PROJECT,
                Constant.TX_TYPE_TRANSFER
        );
        //初始化web server
        WebServerManager.getInstance().startServer("0.0.0.0",9999);
        return RpcModuleState.Running;
    }

    public Module[] declareDependent() {
        return new Module[]{
                Module.build(ModuleE.AC),
                Module.build(ModuleE.LG),
                Module.build(ModuleE.TX),
                Module.build(ModuleE.NW)
        };
    }
    /**
     * 初始化数据库
     * Initialization database
     */
    private void initDb() throws Exception {
        //读取配置文件,数据存储根目录,初始化打开该目录下所有表连接并放入缓存
        RocksDBService.init(config.getDataPath());
        RocksDBService.createTableIfNotExist(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS);
        RocksDBService.createTableIfNotExist(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS);
        RocksDBService.createTableIfNotExist(ProjectStorgeConstant.DB_NAME_PROJECT_INFO);
        RocksDBService.createTableIfNotExist(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT);
        RocksDBService.createTableIfNotExist(ProjectStorgeConstant.DB_NAME_PROJECT_SUPPORT_DETAIL);
        RocksDBService.createTableIfNotExist(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM);
        RocksDBService.createTableIfNotExist(TxStorageConstant.DB_NAME_USER_TX_INFO_TO);
        RocksDBService.createTableIfNotExist(TxStorageConstant.DB_NAME_USER_TF_INFO);
    }

}
