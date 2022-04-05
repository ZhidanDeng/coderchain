package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.base.data.NulsHash;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;
import java.math.BigInteger;

public class TxDetail  extends BaseNulsData {
    private byte[] fromAddress;

    private byte[] toAddress;

    private BigInteger txCount;

    private Long createTime;


    public TxDetail(){

    }

    public TxDetail( byte[] fromAddress, byte[] toAddress, BigInteger txCount, Long createTime) {
        this.fromAddress = fromAddress;
        this.toAddress = toAddress;
        this.txCount = txCount;
        this.createTime = createTime;
    }


    public byte[] getToAddress() {
        return toAddress;
    }

    public byte[] getFromAddress() {
        return fromAddress;
    }

    public Long getCreateTime() {
        return createTime;
    }

    public BigInteger getTxCount() {
        return txCount;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {

        stream.writeBytesWithLength(fromAddress);
        stream.writeBytesWithLength(toAddress);
        stream.writeBigInteger(txCount);
        stream.writeUint48(createTime);

    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.fromAddress = byteBuffer.readByLengthByte();
        this.toAddress = byteBuffer.readByLengthByte();
        this.txCount = byteBuffer.readBigInteger();
        this.createTime = byteBuffer.readUint48();

    }

    @Override
    public int size() {
        int size = 0;
        size += SerializeUtils.sizeOfBytes(fromAddress);
        size += SerializeUtils.sizeOfBytes(toAddress);
        size += SerializeUtils.sizeOfBigInteger();
        // 创建时间
        size += SerializeUtils.sizeOfUint48();

        return size;
    }
}
