package io.nuls.model.vo;


public class UpdatePasswordReq {

    public String userName;

    public String oldPassword;

    public String newPassword;

    public String getUserName() {
        return userName;
    }

    public String getNewPassword() {
        return newPassword;
    }

    public String getOldPassword() {
        return oldPassword;
    }
}
