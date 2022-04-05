package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.base.data.NulsHash;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;
import java.math.BigInteger;


public class VoteDetail extends BaseNulsData {

    private byte[] fromAddress;

    private byte[] toAddress;

    private BigInteger voteCount;

    private String projectName;

    private Long createTime;


    public VoteDetail(){

    }

    public VoteDetail( byte[] fromAddress, byte[] toAddress, BigInteger voteCount, String projectName, Long createTime) {
        this.fromAddress = fromAddress;
        this.toAddress = toAddress;
        this.voteCount = voteCount;
        this.projectName = projectName;
        this.createTime = createTime;

    }


    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {

        stream.writeBytesWithLength(fromAddress);
        stream.writeBytesWithLength(toAddress);
        stream.writeBigInteger(voteCount);
        stream.writeString(projectName);
        stream.writeUint48(createTime);
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.fromAddress = byteBuffer.readByLengthByte();
        this.toAddress = byteBuffer.readByLengthByte();
        this.voteCount = byteBuffer.readBigInteger();
        this.projectName = byteBuffer.readString();
        this.createTime = byteBuffer.readUint48();
    }

    @Override
    public int size() {
        int size = 0;
        size += SerializeUtils.sizeOfBytes(fromAddress);
        size += SerializeUtils.sizeOfBytes(toAddress);
        size += SerializeUtils.sizeOfBigInteger();
        size += SerializeUtils.sizeOfString(projectName);
        // 创建时间
        size += SerializeUtils.sizeOfUint48();
        return size;
    }

    public String getProjectName() {
        return projectName;
    }

    public BigInteger getVoteCount() {
        return voteCount;
    }

    public byte[] getFromAddress() {
        return fromAddress;
    }


    public byte[] getToAddress() {
        return toAddress;
    }

    public Long getCreateTime() {
        return createTime;
    }

    public void setProjectName(String projectName) {
        this.projectName = projectName;
    }

    public void setVoteCount(BigInteger voteCount) {
        this.voteCount = voteCount;
    }

    public void setFromAddress(byte[] fromAddress) {
        this.fromAddress = fromAddress;
    }

    public void setToAddress(byte[] toAddress) {
        this.toAddress = toAddress;
    }
}
