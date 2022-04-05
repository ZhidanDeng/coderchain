package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.base.data.NulsHash;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.List;

public class Vote extends BaseNulsData {
    // 项目所属用户地址
    private byte[] address;

    // 项目名
    private String projectName;

    // 投票总数
    private BigInteger voteCount;

    // 含有的交易数
    private int txCount;

    // 投票hash列表
    private List<NulsHash> txHashList;

    public Vote(){

    }
    // 初始化
    public Vote(byte[] address, String projectName)
    {
        this.address = address;
        this.projectName = projectName;
        this.voteCount = BigInteger.ZERO;
        this.txCount = 0;
        this.txHashList = new ArrayList<>();
    }

    // 添加新交易
    public void addTx(NulsHash newTx,BigInteger amount)
    {
        this.txHashList.add(newTx);
        this.txCount += 1;
        this.voteCount = this.voteCount.add(amount);
    }

    // 删除交易
    public void removeTx(NulsHash removeTx,BigInteger amount)
    {
        this.txHashList.remove(removeTx);
        this.txCount -= 1;
        this.voteCount = this.voteCount.subtract(amount);
    }

    public void setTxCount(int txCount) {
        this.txCount = txCount;
    }

    public int getTxCount() {
        return txCount;
    }

    public void setAddress(byte[] address) {
        this.address = address;
    }

    public void setProjectName(String projectName) {
        this.projectName = projectName;
    }

    public void setTxHashList(List<NulsHash> txHashList) {
        this.txHashList = txHashList;
    }

    public void setVoteCount(BigInteger voteCount) {
        this.voteCount = voteCount;
    }

    public byte[] getAddress() {
        return address;
    }

    public BigInteger getVoteCount() {
        return voteCount;
    }

    public List<NulsHash> getTxHashList() {
        return txHashList;
    }

    public String getProjectName() {
        return projectName;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(address);
        stream.writeString(projectName);
        stream.writeBigInteger(voteCount);
        stream.writeUint32(txCount);
        for (NulsHash hash : txHashList) {
            stream.write(hash.getBytes());
        }
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.address = byteBuffer.readByLengthByte();
        this.projectName = byteBuffer.readString();
        this.voteCount = byteBuffer.readBigInteger();
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
        size += SerializeUtils.sizeOfString(projectName);
        size += SerializeUtils.sizeOfBigInteger();
        size += SerializeUtils.sizeOfUint32();
        size += NulsHash.HASH_LENGTH * txHashList.size();
        return size;
    }
}
