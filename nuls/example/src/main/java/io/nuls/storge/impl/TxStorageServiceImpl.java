package io.nuls.storge.impl;

import io.nuls.base.basic.AddressTool;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.data.CoinData;
import io.nuls.base.data.CoinFrom;
import io.nuls.base.data.CoinTo;
import io.nuls.base.data.Transaction;
import io.nuls.constant.TxStorageConstant;
import io.nuls.core.constant.CommonCodeConstanst;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.crypto.HexUtil;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.model.StringUtils;
import io.nuls.core.rockdb.model.Entry;
import io.nuls.core.rockdb.service.RocksDBService;
import io.nuls.model.po.TxDetail;
import io.nuls.model.po.UserTx;
import io.nuls.storge.TxStorageService;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;


@Component
public class TxStorageServiceImpl implements TxStorageService {

    @Override
    public boolean saveTransaction(Transaction tx) throws NulsException {
        try {
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM)) {
                boolean result = RocksDBService.createTable(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM);
                if (!result) {
                    return false;
                }
            }
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TX_INFO_TO)) {
                boolean result = RocksDBService.createTable(TxStorageConstant.DB_NAME_USER_TX_INFO_TO);
                if (!result) {
                    return false;
                }
            }
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"交易表数据库创建失败");
        }
        try {
            CoinData coinData = new CoinData();
            coinData.parse(new NulsByteBuffer(tx.getCoinData(),0));
            CoinFrom cf = coinData.getFrom().get(0);
            byte[] address = cf.getAddress();
            byte[] txBytes = RocksDBService.get(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address);
            if (null == txBytes) {
                // 不存在则初始化
                UserTx userTx = new UserTx(address);
                userTx.addTx(tx.getHash());
                // 最后进行保存
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
            else
            {
                // 存在则保存新的交易
                UserTx userTx = new UserTx();
                userTx.parse(txBytes, 0);
                userTx.addTx(tx.getHash());
                // 最后进行保存
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
            // 去向保存
            CoinTo ct = coinData.getTo().get(0);
            byte[] to_address = ct.getAddress();
            byte[] to_txBytes = RocksDBService.get(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address);
            if (null == to_txBytes) {
                // 不存在则初始化
                UserTx userTx = new UserTx(to_address);
                userTx.addTx(tx.getHash());
                // 最后进行保存
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
            else
            {
                // 存在则保存新的交易
                UserTx userTx = new UserTx();
                userTx.parse(to_txBytes, 0);
                userTx.addTx(tx.getHash());
                // 最后进行保存
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
        }
        catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_SAVE_ERROR,"交易数据保存失败");
        }
        return true;
    }

    public boolean removeTransaction(Transaction tx) throws NulsException {
        try {
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM) || !RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TX_INFO_TO)) {
                RocksDBService.createTableIfNotExist(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM);
                RocksDBService.createTableIfNotExist(TxStorageConstant.DB_NAME_USER_TX_INFO_TO);
                return true;
            }
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"交易表数据库创建失败");
        }
        try {
            CoinData coinData = new CoinData();
            coinData.parse(new NulsByteBuffer(tx.getCoinData(),0));
            CoinFrom cf = coinData.getFrom().get(0);
            byte[] address = cf.getAddress();
            byte[] txBytes = RocksDBService.get(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address);
            if (null == txBytes) {
                // 不存在则初始化
                UserTx userTx = new UserTx(address);
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
            else
            {
                // 存在则判断是否存在对应的交易
                UserTx userTx = new UserTx();
                userTx.parse(txBytes, 0);
                if (userTx.contains(tx.getHash()))
                {
                    userTx.removeTx(tx.getHash());
                    boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_FROM, address, userTx.serialize());
                    if (!result) {
                        return false;
                    }
                }
            }

            // 去向保存
            CoinTo ct = coinData.getTo().get(0);
            byte[] to_address = ct.getAddress();
            byte[] to_txBytes = RocksDBService.get(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address);
            if (null == to_txBytes) {
                // 不存在则初始化
                UserTx userTx = new UserTx(to_address);
                boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address, userTx.serialize());
                if (!result) {
                    return false;
                }
            }
            else
            {
                // 存在则判断是否存在对应的交易
                UserTx userTx = new UserTx();
                userTx.parse(to_txBytes, 0);
                if (userTx.contains(tx.getHash()))
                {
                    userTx.removeTx(tx.getHash());
                    boolean result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TX_INFO_TO, to_address, userTx.serialize());
                    if (!result) {
                        return false;
                    }
                }
            }
        }
        catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"交易数据删除失败");
        }
        return true;
    }

    public UserTx getTx(String address, String type) {
        if (address == null) {
            return null;
        }
        String tableName;
        if (StringUtils.equals(type,"0"))
        {
            tableName = TxStorageConstant.DB_NAME_USER_TX_INFO_FROM;
        }
        else
        {
            tableName = TxStorageConstant.DB_NAME_USER_TX_INFO_TO;
        }
        try {

            if (!RocksDBService.existTable(tableName)) {
                boolean result = RocksDBService.createTable(tableName);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"交易表数据库创建失败");
        }

        byte[] coinBytes = RocksDBService.get(tableName, AddressTool.getAddress(address));
        if (null == coinBytes) {
            return null;
        }
        UserTx coinPo = new UserTx();
        try {
            //将byte数组反序列化为AliasPo返回
            coinPo.parse(coinBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"coinData反序列化失败");
        }
        return coinPo;
    }

    @Override
    public List<HashMap<String,Object>> getTransferList() {
        try {
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TF_INFO)) {
                boolean result = RocksDBService.createTable(TxStorageConstant.DB_NAME_USER_TF_INFO);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"转账信息数据库创建失败");
        }
        try
        {
            List<Entry<byte[], byte[]>> result = RocksDBService.entryList(TxStorageConstant.DB_NAME_USER_TF_INFO);
            List<HashMap<String,Object>> res = new ArrayList<>();
            for (int i = 0; i < result.size(); i++) {
                HashMap<String,Object> o = new HashMap<>();
                TxDetail a = new TxDetail();
                a.parse(new NulsByteBuffer(result.get(i).getValue(),0));
                String key = HexUtil.encode(result.get(i).getKey());
                o.put("hash",key);
                o.put("txCount",a.getTxCount());
                o.put("createTime",a.getCreateTime());
                res.add(o);
            }
            return res;

        }
        catch (Exception e)
        {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"用户转账信息获取失败");
        }
    }

    @Override
    public TxDetail getTransferDetail(byte[] tx) {
        try {
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TF_INFO)) {
                boolean result = RocksDBService.createTable(TxStorageConstant.DB_NAME_USER_TF_INFO);
                if (!result) {
                    return null;
                }
            }
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"交易表数据库创建失败");
        }

        byte[] coinBytes = RocksDBService.get(TxStorageConstant.DB_NAME_USER_TF_INFO, tx);
        if (null == coinBytes) {
            return null;
        }
        TxDetail coinPo = new TxDetail();
        try {
            //将byte数组反序列化为AliasPo返回
            coinPo.parse(coinBytes, 0);
        } catch (Exception e) {
            throw new NulsRuntimeException(CommonCodeConstanst.DB_QUERY_ERROR,"反序列化失败");
        }
        return coinPo;
    }

    @Override
    public boolean saveTransferDetail(byte[] key, TxDetail project) throws NulsException {
        try {
            boolean result;
            //check if the table is exist
            if (!RocksDBService.existTable(TxStorageConstant.DB_NAME_USER_TF_INFO)) {
                result = RocksDBService.createTable(TxStorageConstant.DB_NAME_USER_TF_INFO);
                if (!result) {
                    throw new NulsException(CommonCodeConstanst.DB_TABLE_CREATE_ERROR,"转账交易信息数据库创建失败");
                }
            }
            result = RocksDBService.put(TxStorageConstant.DB_NAME_USER_TF_INFO, key, project.serialize());
            if (!result) {
                throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"转账交易信息数据保存失败");
            }
            return true;
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_SAVE_ERROR,"转账交易信息数据保存失败");
        }
    }

    @Override
    public boolean deleteTransferDetail(byte[] key) throws NulsException {
        try {
            return RocksDBService.delete(TxStorageConstant.DB_NAME_USER_TF_INFO, key);
        } catch (Exception e) {
            throw new NulsException(CommonCodeConstanst.DB_DELETE_ERROR,e);
        }
    }
}
