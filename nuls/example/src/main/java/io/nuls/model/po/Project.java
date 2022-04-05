package io.nuls.model.po;

import io.nuls.base.basic.NulsByteBuffer;
import io.nuls.base.basic.NulsOutputStreamBuffer;
import io.nuls.base.data.BaseNulsData;
import io.nuls.core.exception.NulsException;
import io.nuls.core.parse.SerializeUtils;

import java.io.IOException;

public class Project extends BaseNulsData {

    // 项目所属用户地址
    private byte[] address;

    // 项目描述
    private String description;

    // 项目名
    private String projectName;

    // 项目类型
    private String projectType;

    // 项目创建时间
    private Long createTime;

    public Project(){

    }
    public Project(byte[] address, String projectName, String projectType, String description, Long createTime)
    {
        this.address = address;
        this.projectType = projectType;
        this.projectName = projectName;
        this.description = description;
        this.createTime = createTime;
    }

    public void setProjectName(String projectName) {
        this.projectName = projectName;
    }

    public void setAddress(byte[] address) {
        this.address = address;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public void setCreateTime(Long createTime) {
        this.createTime = createTime;
    }

    public void setProjectType(String projectType) {
        this.projectType = projectType;
    }

    public String getProjectName() {
        return projectName;
    }

    public byte[] getAddress() {
        return address;
    }

    public String getDescription() {
        return description;
    }

    public Long getCreateTime() {
        return createTime;
    }

    public String getProjectType() {
        return projectType;
    }

    @Override
    protected void serializeToStream(NulsOutputStreamBuffer stream) throws IOException {
        stream.writeBytesWithLength(address);
        stream.writeString(description);
        stream.writeString(projectName);
        stream.writeString(projectType);
        stream.writeUint48(createTime);
    }

    @Override
    public void parse(NulsByteBuffer byteBuffer) throws NulsException {
        this.address = byteBuffer.readByLengthByte();
        this.description = byteBuffer.readString();
        this.projectName = byteBuffer.readString();
        this.projectType  = byteBuffer.readString();
        this.createTime = byteBuffer.readUint48();
    }

    @Override
    public int size() {
        int size = 0;
        size += SerializeUtils.sizeOfBytes(address);
        size += SerializeUtils.sizeOfString(description);
        size += SerializeUtils.sizeOfString(projectName);
        size += SerializeUtils.sizeOfString(projectType);
        // 创建时间
        size += SerializeUtils.sizeOfUint48();
        return size;
    }
}
