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
import io.nuls.service.ProjectService;

@Component
public class ProjectCreateProcessor implements TransactionProcessor {
    @Autowired
    ProjectService projectService;


    @Override
    public int getType() {
        return Constant.TX_TYPE_CREATE_PROJECT;
    }

    @Override
    public boolean validate(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.debug("validate createProject tx");
        boolean result;
        //验证交易hash是否一致
        try {
            NulsHash nulsHash = NulsHash.calcHash(tx.serializeForHash());
            if (!nulsHash.equals(tx.getHash())) {
                return false;
            }
            result = projectService.createProjectTxValidate(tx);
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
            Log.warn("ac_validateTx createProject tx validate error");
        }
        return result;
    }

    @Override
    public boolean commit(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("commit createProject tx");
        boolean result;
        try {
            result = projectService.createProjectTxCommit(tx);
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
            Log.warn("ac_commitTx createProject tx commit error");
        }
        return result;
    }

    @Override
    public boolean rollback(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("rollback createProject tx");
        boolean result;
        try {
            result = projectService.createProjectTxRollback(tx);
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
            Log.warn("ac_rollbackTx createProject tx rollback error");
        }
        return result;
    }
}
