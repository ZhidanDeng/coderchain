package io.nuls.storge;

import io.nuls.base.data.Transaction;
import io.nuls.core.exception.NulsException;
import io.nuls.model.po.TxDetail;
import io.nuls.model.po.UserTx;

import java.util.HashMap;
import java.util.List;

public interface TxStorageService {
    /**
     * 交易存储
     * @param
     * @return
     */
    boolean saveTransaction(Transaction tx) throws NulsException;

    /**
     * 交易删除
     * @param tx
     * @return
     * @throws Exception
     */
    boolean removeTransaction(Transaction tx) throws NulsException;

    UserTx getTx(String tx, String type);

    List<HashMap<String,Object>> getTransferList();

    TxDetail getTransferDetail(byte[] tx);

    boolean deleteTransferDetail(byte[] key) throws NulsException;

    boolean saveTransferDetail(byte[] key, TxDetail project) throws NulsException;
}
