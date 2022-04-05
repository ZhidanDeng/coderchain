package io.nuls.service;


import io.nuls.base.data.Transaction;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.model.po.TxDetail;
import io.nuls.model.po.UserTx;
import io.nuls.rpc.vo.Account;

import java.io.IOException;
import java.math.BigInteger;
import java.util.HashMap;
import java.util.List;

public interface TxService {
    boolean validTxType(int tx);

    Transaction signTransaction(Transaction transaction, Account account, String password) throws IOException;

    List<HashMap<String, Object>> getTransferList();

    TxDetail getTransferDetail(byte[] tx);

    Transaction transfer(String address, String to_address, String password, BigInteger amount, String remark) throws NulsRuntimeException, NulsException, IOException;

    boolean transferTxRollback(Transaction tx) throws NulsException;

    boolean transferTxCommit(Transaction tx) throws NulsException;

    boolean transferTxValidate(Transaction tx) throws NulsException;

    UserTx getUserTx(String address, String type);
}
