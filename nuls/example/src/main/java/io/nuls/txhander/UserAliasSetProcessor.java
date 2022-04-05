package io.nuls.txhander;
import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.data.*;
import io.nuls.constant.Constant;
import io.nuls.core.core.annotation.Autowired;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.log.Log;
import io.nuls.model.po.Alias;
import io.nuls.core.core.annotation.Component;
import io.nuls.service.UserService;


@Component
public class UserAliasSetProcessor implements TransactionProcessor {
    @Autowired
    UserService userService;

    @Override
    public int getType() {
        return Constant.TX_TYPE_REGISTER_USER_ALIAS;
    }

    @Override
    public boolean validate(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.debug("validate alias tx");
        boolean result;
        //验证交易hash是否一致
        try {
            NulsHash nulsHash = NulsHash.calcHash(tx.serializeForHash());
            if (!nulsHash.equals(tx.getHash())) {
                return false;
            }
            result = userService.aliasTxValidate(tx);
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
            Log.warn("ac_validateTx alias tx validate error");
        }
        return result;
    }

    @Override
    public boolean commit(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("commit alias tx");
        boolean result;
        Alias alias = new Alias();
        try {
            alias.parse(new NulsByteBuffer(tx.getTxData()));
            result = userService.aliasTxCommit(alias,tx);
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
            Log.warn("ac_commitTx alias tx commit error");
        }
        return result;
    }

    @Override
    public boolean rollback(int chainId, Transaction tx, BlockHeader blockHeader) {
        Log.info("rollback alias tx");
        Alias alias = new Alias();
        boolean result;
        try {
            alias.parse(new NulsByteBuffer(tx.getTxData()));
            result = userService.rollbackAlias(alias, tx);
        }
        catch (NulsException e) {
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
            Log.warn("ac_rollbackTx alias tx rollback error");
        }
        return result;
    }
}
