package io.nuls.txhander;

import io.nuls.base.data.BlockHeader;
import io.nuls.base.data.NulsHash;
import io.nuls.base.data.Transaction;
import io.nuls.constant.Constant;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.core.annotation.Component;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.log.Log;
import io.nuls.service.TxService;

@Component
public class TransferProcessor implements TransactionProcessor {
    @Autowired
    TxService txService;

    @Override
    public int getType() {
        return Constant.TX_TYPE_TRANSFER;

    }

    @Override
    public boolean validate(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.debug("validate Transfer tx");
        boolean result;
        //验证交易hash是否一致
        try {
            NulsHash nulsHash = NulsHash.calcHash(tx.serializeForHash());
            if (!nulsHash.equals(tx.getHash())) {
                return false;
            }
            result = txService.transferTxValidate(tx);
        } catch (NulsException e) {
            Log.error(e.getErrorCode().getMsg());
            Log.error(e);
            result = false;
        }
        catch (NulsRuntimeException e)
        {
            Log.error(e.getMessage());
            Log.error(e);
            result = false;
        }
        catch (Exception e)
        {
            Log.error(e);
            result = false;
        }
        if (!result) {
            Log.warn("ac_validateTx Transfer tx validate error");
        }
        return result;
    }

    @Override
    public boolean commit(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("commit Transfer tx");
        boolean result;
        try {
            result = txService.transferTxCommit(tx);
        } catch (NulsException e) {
            Log.error(e.getErrorCode().getMsg());
            Log.error(e);
            result = false;
        }
        catch (NulsRuntimeException e)
        {
            Log.error(e.getMessage());
            Log.error(e);
            result = false;
        }
        catch (Exception e)
        {
            Log.error(e);
            result = false;
        }
        if (!result) {
            Log.warn("ac_commitTx Transfer tx commit error");
        }
        return result;
    }

    @Override
    public boolean rollback(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("rollback vote project tx");
        boolean result;
        try {
            result = txService.transferTxRollback(tx);
        } catch (NulsException e) {
            Log.error(e.getErrorCode().getMsg());
            Log.error(e);
            result = false;
        }
        catch (NulsRuntimeException e)
        {
            Log.error(e.getMessage());
            Log.error(e);
            result = false;
        }
        catch (Exception e)
        {
            Log.error(e);
            result = false;
        }
        if (!result) {
            Log.warn("ac_rollbackTx vote project tx rollback error");
        }
        return result;
    }
}
