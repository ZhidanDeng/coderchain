package io.nuls.model.bo;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;

public class UpdateModel extends BaseNulsData {

    private byte[] oldData;

    private byte[] newData;

    private byte[] address;

    public UpdateModel() {

    }
    public UpdateModel(byte[] oldData, byte[] newData, byte[] address) {
        this.oldData = oldData;
        this.newData = newData;
        this.address = address;
    }

    public byte[] getNewData() {
        return newData;
    }

    public byte[] getOldData() {
        return oldData;
    }

    public void setNewData(byte[] newData) {
        this.newData = newData;
    }

    public void setOldData(byte[] oldData) {
        this.oldData = oldData;
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
        s += SerializeUtils.sizeOfBytes(oldData);
        s += SerializeUtils.sizeOfBytes(newData);
        s += SerializeUtils.sizeOfBytes(address);
        return s;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(oldData);
        stream.writeBytesWithLength(newData);
        stream.writeBytesWithLength(address);
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.oldData = byteBuffer.readByLengthByte();
        this.newData = byteBuffer.readByLengthByte();
        this.address = byteBuffer.readByLengthByte();
    }
}
