package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.base.data.NulsHash;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

public class UserTx  extends BaseNulsData {
    // 项目所属用户地址
    private byte[] address;

    // 含有的交易数
    private int txCount;

    // 投票hash列表
    private List<NulsHash> txHashList;

    public UserTx(){

    }

    // 初始化
    public UserTx(byte[] address)
    {
        this.address = address;
        this.txCount = 0;
        this.txHashList = new ArrayList<>();
    }

    // 添加新交易
    public void addTx(NulsHash newTx)
    {
        this.txHashList.add(newTx);
        this.txCount += 1;
    }

    // 删除交易
    public void removeTx(NulsHash removeTx)
    {
        this.txHashList.remove(removeTx);
        this.txCount -= 1;
    }

    public boolean contains(NulsHash Tx)
    {
        return  this.txHashList.contains(Tx);
    }


    public int getTxCount() {
        return txCount;
    }

    public byte[] getAddress() {
        return address;
    }

    public List<NulsHash> getTxHashList() {
        return txHashList;
    }


    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(address);
        stream.writeUint32(txCount);
        for (NulsHash hash : txHashList) {
            stream.write(hash.getBytes());
        }
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.address = byteBuffer.readByLengthByte();
        this.txCount = byteBuffer.readInt32();
        this.txHashList = new ArrayList<>();
        for (int i = 0; i < txCount; i++) {
            this.txHashList.add(byteBuffer.readHash());
        }
    }

    @Override
    public int size() {
        int size = 0;
        size += SerializeUtils.sizeOfBytes(address);
        size += SerializeUtils.sizeOfUint32();
        size += NulsHash.HASH_LENGTH * txHashList.size();
        return size;
    }
}
