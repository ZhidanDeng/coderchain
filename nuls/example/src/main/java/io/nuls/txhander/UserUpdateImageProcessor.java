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
import io.nuls.service.UserService;


@Component
public class UserUpdateImageProcessor implements TransactionProcessor {

    @Autowired
    UserService userService;

    @Override
    public int getType() {
        return Constant.TX_TYPE_UPDATE_USER_IMG;
    }

    @Override
    public boolean validate(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.debug("validate update image tx");
        boolean result;
        //验证交易hash是否一致
        try {
            NulsHash nulsHash = NulsHash.calcHash(tx.serializeForHash());
            if (!nulsHash.equals(tx.getHash())) {
                return false;
            }
            result = userService.updateUserImageTxValidate(tx);
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
            Log.warn("ac_validateTx update image tx validate error");
        }
        return result;
    }

    @Override
    public boolean commit(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("commit update image tx");
        boolean result;
        try {
            result = userService.updateUserImageTxCommit(tx);
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
            Log.warn("ac_commitTx update image tx commit error");
        }
        return result;
    }

    @Override
    public boolean rollback(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("rollback update image tx");
        boolean result;
        try {
            result = userService.updateUserImageTxRollback(tx);
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
            Log.warn("ac_rollbackTx update image tx rollback error");
        }
        return result;
    }
}
