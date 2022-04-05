package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;


public class Alias extends BaseNulsData {

    private byte[] address;

    private String alias;

    private String sex;

    private String avatar;

    private String description;


    public Alias() {
        this.avatar = "";
        this.description = "";
        this.sex = "-1";
    }

    public Alias(byte[] address, String alias) {
        this.address = address;
        this.alias = alias;
        this.avatar = "";
        this.description = "";
        this.sex = "-1";
    }

    public Alias(byte[] address, String alias, String sex, String avatar, String description) {
        this.address = address;
        this.alias = alias;
        this.sex = sex;
        this.description = description;
        this.avatar = avatar;
    }

    public byte[] getAddress() {
        return address;
    }

    public void setAddress(byte[] address) {
        this.address = address;
    }

    public String getAlias() {
        return alias;
    }

    public void setAlias(String alias) {
        this.alias = alias;
    }

    public String getSex() {
        return sex;
    }

    public void setSex(String sex) {
        this.sex = sex;
    }

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public String getDescription() {
        return description;
    }

    @Override
    public int size() {
        int s = 0;
        s += SerializeUtils.sizeOfBytes(address);
        s += SerializeUtils.sizeOfString(alias);
        s += SerializeUtils.sizeOfString(sex);
        s += SerializeUtils.sizeOfString(avatar);
        s += SerializeUtils.sizeOfString(description);
        return s;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(address);
        stream.writeString(alias);
        stream.writeString(sex);
        stream.writeString(avatar);
        stream.writeString(description);
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.address = byteBuffer.readByLengthByte();
        this.alias = byteBuffer.readString();
        this.sex = byteBuffer.readString();
        this.avatar = byteBuffer.readString();
        this.description = byteBuffer.readString();
    }
}