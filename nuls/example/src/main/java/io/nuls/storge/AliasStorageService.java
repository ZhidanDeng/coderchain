package io.nuls.storge;

import io.nuls.core.exception.NulsException;
import io.nuls.model.po.Alias;

import java.util.HashMap;
import java.util.List;

public interface AliasStorageService {
    /**
     * 根据别名从数据库中获取Alias数据
     * @param alias
     * @return
     */
    Alias getAlias(String alias);

    /**
     * 根据地址获取数据
     * @param address
     * @return
     */
    Alias getAliasByAddress(String address);

    /**
     * 获取别名列表
     * @return
     * @throws NulsException
     */
    List<HashMap<String, Object>> getAliasList() throws NulsException;

    /**
     * 别名存储
     * @param alias
     * @return
     */
    boolean saveAlias(Alias alias) throws NulsException;

    /**
     * 删除别名
     * @param po
     * @return
     */
    boolean removeAlias(Alias po) throws NulsException;

    List<Alias> getAliasListByAddress(List<byte[]> keys);
}
