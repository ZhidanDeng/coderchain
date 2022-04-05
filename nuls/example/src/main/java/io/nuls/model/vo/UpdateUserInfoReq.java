package io.nuls.model.vo;

public class UpdateUserInfoReq {

    private String userName;

    private String password;

    private String sex;

    private String avatar;

    private String description;

    public String getUserName() {
        return userName;
    }

    public String getDescription() {
        return description;
    }

    public String getAvatar() {
        return avatar;
    }

    public String getSex() {
        return sex;
    }

    public String getPassword() {
        return password;
    }
}
