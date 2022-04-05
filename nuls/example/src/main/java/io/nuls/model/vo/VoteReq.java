package io.nuls.model.vo;

import java.math.BigInteger;

public class VoteReq {
    public String userName;

    public String password;

    public String projectAuthor;

    public String projectName;

    public BigInteger voteCount;

    public BigInteger getVoteCount() {
        return voteCount;
    }

    public String getPassword() {
        return password;
    }

    public String getProjectName() {
        return projectName;
    }

    public String getUserName() {
        return userName;
    }

    public String getProjectAuthor() {
        return projectAuthor;
    }
}
