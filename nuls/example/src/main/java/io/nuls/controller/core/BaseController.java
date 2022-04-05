package io.nuls.controller.core;

import io.nuls.utils.Utils;
import io.nuls.base.data.Transaction;
import io.nuls.core.exception.NulsException;
import io.nuls.core.exception.NulsRuntimeException;
import io.nuls.core.log.Log;
import io.nuls.rpc.vo.Account;

import java.io.IOException;

/**
 * @Author: zhoulijun
 * @Time: 2019-06-13 16:35
 * @Description: 功能描述
 */
public interface BaseController {

    public interface Caller<T> {
         Result<T> exec() throws Throwable;
    }

    default <T> Result<T> call(Caller caller){
        try{
           return caller.exec();
        }catch (NulsException e){
            Log.error(e);
            return new Result<>(false,e.getErrorCode().getMsg());
        }catch (NulsRuntimeException e){
            Log.error(e);
            return new Result<>(false,e.getMessage());
        }
        catch (Throwable e){
            Log.error(e);
            return new Result<>(false,"system error " + e.getMessage());
        }
    }


    default Transaction signTransaction(Transaction transaction, Account account, String password) throws IOException {
        return Utils.signTransaction(transaction,account.getEncryptedPrikeyHex(),account.getPubkeyHex(),password);
    }


}
