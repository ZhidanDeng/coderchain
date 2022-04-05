package io.nuls.storge.impl;
import io.nuls.Config;
import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.constant.UserStorageConstant;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.exception.NulsException;
import io.nuls.core.rockdb.service.RocksDBService;
import io.nuls.core.model.StringUtils;
import io.nuls.model.po.Alias;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.rpc.AccountTools;
import io.nuls.rpc.LegderTools;
import io.nuls.storge.AliasStorageService;

import java.math.BigInteger;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

@Component
public class AliasStorageServiceImpl implements AliasStorageService {

    @Autowired
    Config config;

    @Autowired
    LegderTools legderTools;

    public Alias getAlias(String alias) {
        if (alias == null || "".equals(alias.trim())) {
            return null;
        }
        try {
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS);
                if (!result) {
                    return null;
                }
            }
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"别名-地址数据库创建失败");
        }

        byte[] aliasBytes = RocksDBService.get(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS, StringUtils.bytes(alias));
        if (null == aliasBytes) {
            return null;
        }
        String address = AddressTool.getStringAddressByBytes(aliasBytes);
        return this.getAliasByAddress(address);
    }

    public Alias getAliasByAddress(String address) {
        if (address == null || "".equals(address.trim())) {
            return null;
        }
        try {
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS);
                if (!result) {
                    return null;
                }
            }
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"别名-用户信息数据库创建失败");
        }

        byte[] aliasBytes = RocksDBService.get(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS, AddressTool.getAddress(address));
        if (null == aliasBytes) {
            return null;
        }
        Alias aliasPo = new Alias();
        try {
            //将byte数组反序列化为AliasPo返回
            aliasPo.parse(aliasBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"用户信息反序列化失败");
        }
        return aliasPo;
    }

    public List<HashMap<String, Object>> getAliasList() throws NulsException {
        try {
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS);
                if (!result) {
                    return null;
                }
            }
            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"别名-地址数据库创建失败");
        }
        List<byte[]> value_list = RocksDBService.valueList(UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS);
        List<HashMap<String, Object>> res = new ArrayList<>();
        for (int i = 0; i < value_list.size(); i++) {
            String address = AddressTool.getStringAddressByBytes(value_list.get(i));
            HashMap<String, Object> map = new HashMap<>();
            Alias alias = this.getAliasByAddress(address);
            map.put("userName",alias.getAlias());
            map.put("address", address);
            BigInteger balance = legderTools.getBalanceAndNonce(config.getChainId(),address,config.getChainId(),config.getAssetId()).getAvailable();
            map.put("balance", balance);
            res.add(map);
        }
        return res;
    };

    public boolean saveAlias(Alias alias) throws NulsException {

        String tableNameKeyIsAlias = UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS;
        String tableNameKeyIsAddress = UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS;
        boolean result;
        try {
            //check if the table is exist
            if (!RocksDBService.existTable(tableNameKeyIsAlias)) {
                result = RocksDBService.createTable(tableNameKeyIsAlias);
                if (!result) {
                    return false;
                }
            }
            if (!RocksDBService.existTable(tableNameKeyIsAddress)) {
                result = RocksDBService.createTable(tableNameKeyIsAddress);
                if (!result) {
                    return false;
                }
            }
            result = RocksDBService.put(tableNameKeyIsAlias, StringUtils.bytes(alias.getAlias()), alias.getAddress());
            if (!result) {
                return false;
            }
            result = RocksDBService.put(tableNameKeyIsAddress, alias.getAddress(), alias.serialize());
            if (!result) {
                return false;
            }
            return true;
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"用户数据保存失败");
        }
    }

    public boolean removeAlias(Alias po) throws NulsException {
        String tableNameKeyIsAlias = UserStorageConstant.DB_NAME_USER_INFO_KEY_ALIAS;
        String tableNameKeyIsAddress = UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS;
        try {
            RocksDBService.delete(tableNameKeyIsAddress, po.getAddress());
            return RocksDBService.delete(tableNameKeyIsAlias, StringUtils.bytes(po.getAlias()));
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_DELETE_ERROR,e);
        }
    }

    @Override
    public List<Alias> getAliasListByAddress(List<byte[]> keys) {
        try {

            if (!RocksDBService.existTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS)) {
                boolean result = RocksDBService.createTable(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"别名-地址数据库创建失败");
        }
        try
        {
            List<byte[]> value_list = RocksDBService.multiGetValueList(UserStorageConstant.DB_NAME_USER_INFO_KEY_ADDRESS,keys);
            List<Alias> res = new ArrayList<>();
            for (int i = 0; i < value_list.size(); i++) {
                Alias a = new Alias();
                a.parse(new NulsByteBuffer(value_list.get(i),0));
                res.add(a);
            }
            return res;
        }
        catch (Exception e)
        {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"用户信息获取失败");
        }

    }
}
