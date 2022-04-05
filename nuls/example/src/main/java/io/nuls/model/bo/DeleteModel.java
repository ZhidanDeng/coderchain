package io.nuls.model.bo;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;

public class DeleteModel extends BaseNulsData {
    private byte[] deleteData;

    private byte[] otherData;

    private byte[] address;

    public DeleteModel() {

    }
    public DeleteModel(byte[] deleteData, byte[] otherData, byte[] address) {
        this.deleteData = deleteData;
        this.otherData = otherData;
        this.address = address;
    }

    public byte[] getOtherData() {
        return otherData;
    }

    public byte[] getDeleteData() {
        return deleteData;
    }

    public void setOtherData(byte[] otherData) {
        this.otherData = otherData;
    }

    public void setDeleteData(byte[] deleteData) {
        this.deleteData = deleteData;
    }

    public byte[] getAddress() {
        return address;
    }

    public void setAddress(byte[] address) {
        this.address = address;
    }



    @Override
    public int size() {
        int s = 0;
        s += SerializeUtils.sizeOfBytes(deleteData);
        s += SerializeUtils.sizeOfBytes(otherData);
        s += SerializeUtils.sizeOfBytes(address);
        return s;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(deleteData);
        stream.writeBytesWithLength(otherData);
        stream.writeBytesWithLength(address);
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.deleteData = byteBuffer.readByLengthByte();
        this.otherData = byteBuffer.readByLengthByte();
        this.address = byteBuffer.readByLengthByte();
    }
}
