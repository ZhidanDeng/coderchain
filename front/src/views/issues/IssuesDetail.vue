<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-toolbar md-elevation="0" class="md-transparent">
          <div>
            <md-icon class="fa fa-inbox"></md-icon>
            <router-link :to="`/user/${userName}`">{{ userName }}</router-link>
            <span>&nbsp;/&nbsp;</span>
            <router-link
              :to="`/detail/code?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"
              active-class="active-class"
            >{{ projectName | decodeURIComponent }}</router-link>
          </div>
          <div v-if="!isEdit && ( isIssueCreator || isOwner)" class="add-button">
            <md-button class="md-raised md-accent" @click="editIssues">编辑issues</md-button>
          </div>
        </md-toolbar>
        <md-divider></md-divider>
        <div v-if="!isEdit">
          <p class="title">{{showTitle}}</p>
          <div class="tip">
            <span>
              ID:
              <b>{{issueID}}&nbsp;&nbsp;</b>
            </span>
            <span v-if="showSelectType!=-1">
              <b>{{typeObject[showSelectType]}}</b>
            </span>
            <br />
            <span>
              由&nbsp;
              <b>{{showCreateUserName}}</b>&nbsp;创建于&nbsp;
              <b>{{showCreateTime}}</b>
            </span>
          </div>
          <md-divider></md-divider>
        </div>

        <div v-if="!isEdit" class="md-layout">
          <div class="md-layout-item md-size-60">
            <div style="text-align:left;padding-bottom:16px">
              <md-icon class="fa fa-book"></md-icon>
            </div>
            
            <div class="md-title" v-if="showContent"><md-content>{{showContent}}</md-content></div>
            <div class="md-subhead" v-else><md-content>issue内容为空</md-content></div>
            
            <div v-if="isLogin" class="comment-title">评论({{commentNum}})</div>
            <div v-if="commentNum>0">
              <md-card
                md-with-hover
                style="margin-bottom:40px;"
                v-for="dir in commentList"
                :key="dir.commentID"
              >
                <md-ripple>
                  <md-card-header>
                    <div class="md-title">{{dir.content}}</div>
                  </md-card-header>
                  <md-card-actions>
                    <div class="md-subhead">Created By {{dir.createUser}} At {{dir.createTime}}</div>
                    <md-button
                      v-if="isCommentCreator(dir) || isIssueCreator || isOwner "
                      @click="removeComment(dir.commentID)"
                    >删除</md-button>
                  </md-card-actions>
                </md-ripple>
              </md-card>
            </div>
            <div v-if="isLogin">
              <md-card>
                <md-card-content>
                  <md-field style="margin:auto">
                    <label>请输入您对该issue的看法</label>
                    <md-textarea v-model="commentText"></md-textarea>
                  </md-field>
                </md-card-content>
                <md-card-actions>
                  <md-button class="md-raised md-accent" @click="newComment">评论</md-button>
                  <md-button class="md-raised md-primary" @click="resetComment">重置</md-button>
                </md-card-actions>
              </md-card>
            </div>
          </div>
          <div class="md-layout-item"  style="line-height:30px;padding-top:20px;">
            <p><md-icon class="fa fa-inbox"></md-icon>&nbsp;&nbsp;关联项目：<span><b>{{ userName }}</b></span><span>&nbsp;/&nbsp;</span><span><b>{{ projectName | decodeURIComponent }}</b></span></p>
            <p><md-icon class="fa fa-user-circle"></md-icon>&nbsp;&nbsp;issues提出者：<b>{{showCreateUserName}}</b></p>
            <p><md-icon class="fa fa-bell"></md-icon>&nbsp;&nbsp;当前状态：<b>{{statusObject[showSelectStatus]}}</b></p>
            <p><md-icon class="fa fa-exclamation-circle"></md-icon>&nbsp;&nbsp;优先级：<b>{{levelObject[showSelectLevel]}}</b></p>
            <md-button class="md-raised" style="margin:20px 0;width:100%;" @click="removeIssue">删除ISSUE</md-button>
          </div>
        </div>
        <div v-else-if="isEdit && ( isIssueCreator || isOwner)" class="md-layout">
          <div class="md-layout-item md-size-60">
            <md-field>
              <label>标题</label>
              <md-input v-model="title" maxlength="60"></md-input>
            </md-field>
            <md-field>
              <label>内容</label>
              <md-textarea v-model="content" style="min-height:230px"></md-textarea>
            </md-field>
          </div>
          <div class="md-layout-item">
            <md-field>
              <label>创建者</label>
              <md-input v-model="createUserName" readonly></md-input>
            </md-field>
            <md-field>
              <label for="type">标签</label>
              <md-select v-model="selectType" id="type">
                <md-option v-for="dir in issueType" :value="typeObject[dir]" :key="dir">{{ dir }}</md-option>
              </md-select>
            </md-field>
            <md-field>
              <label for="status">当前状态</label>
              <md-select v-model="selectStatus" id="status">
                <md-option
                  v-for="dir in issueStatus"
                  :value="statusObject[dir]"
                  :key="dir"
                >{{ dir }}</md-option>
              </md-select>
            </md-field>
            <md-field>
              <label for="level">优先级</label>
              <md-select v-model="selectLevel" id="level">
                <md-option v-for="dir in issueLevel" :value="levelObject[dir]" :key="dir">{{ dir }}</md-option>
              </md-select>
            </md-field>
            <div v-if="isEdit && ( isIssueCreator || isOwner) ">
            <md-button class="md-raised md-accent" @click="saveIssues">保存</md-button>
            <md-button class="md-raised md-primary" @click="cancleIssues">取消</md-button>
          </div>
          </div>
        </div>
      </md-card-content>
    </md-card>
  </div>
</template>
<script lang="ts">
import { Vue, Component, Prop, Watch, Inject } from "vue-property-decorator";
import { Getter } from "vuex-class";
import {
  UserInterface,
  IssueInfoInterface,
  CommentInfoInterface
} from "@utils/interface";
import {
  trim
} from '@utils/index'

import {
  editIssues,
  getIssuesInfo,
  addComment,
  editComment,
  deleteComment,
  deleteIssues
} from "@api/issues";
import { openLoading, closeLoading } from "@utils/share-variable";
@Component
export default class IssueDetail extends Vue {
  userName: any = "";
  projectName: any = "";
  issueID: any = "";

  isEdit: boolean = false;
  isIssueCreator = false;

  commentList: CommentInfoInterface[] = [];

  // 展示部分
  showTitle: string = "";
  showContent: string = "";
  showSelectLevel: number = -1;
  showSelectStatus: number = 0;
  showSelectType: number = -1;
  showCreateUserName: string = "";
  showCreateTime: string = "";

  // 编辑部分
  selectType: number = -1;
  issueType: Array<string> = [
    "未设置",
    "bug",
    "enhancement",
    "feature",
    "duplicate",
    "invalid",
    "question",
    "wontfix"
  ];
  typeObject: object = {
    未设置: -1,
    bug: 0,
    enhancement: 1,
    feature: 2,
    duplicate: 3,
    invalid: 4,
    question: 5,
    wontfix: 6,
    "-1": "未设置",
    "0": "bug",
    "1": "enhancement",
    "2": "feature",
    "3": "duplicate",
    "4": "invalid",
    "5": "question",
    "6": "wontfix"
  };
  selectStatus: number = 0;
  issueStatus: Array<string> = [
    "开启的",
    "待办的",
    "进行中",
    "已完成",
    "已拒绝"
  ];
  statusObject: object = {
    开启的: 0,
    待办的: 1,
    进行中: 2,
    已完成: 3,
    已拒绝: 4,
    "0": "开启的",
    "1": "待办的",
    "2": "进行中",
    "3": "已完成",
    "4": "已拒绝"
  };
  selectLevel: number = -1;
  issueLevel: Array<string> = ["不指定", "严重", "主要", "次要", "不重要"];
  levelObject: object = {
    不指定: -1,
    严重: 0,
    主要: 1,
    次要: 2,
    不重要: 3,
    "-1": "未指定",
    "0": "严重",
    "1": "主要",
    "2": "次要",
    "3": "不重要"
  };
  title: string = "";
  content: string = "";
  createUserName: string = "";
  commentText: string = "";

  @Inject("onShowLoginBox") onShowLoginBox: any;
  @Inject('reloadPage') reloadPage: any
  @Getter user!: UserInterface;
  @Getter isLogin!: boolean;
  // 是否有权编辑项目
  get isOwner(): boolean {
    if (
      this.user &&
      this.user.sUserName &&
      this.user.sUserName === this.userName
    ) {
      return true;
    }
    return false;
  }

  // 判断是否是评论的创建者
  isCommentCreator(comment: CommentInfoInterface): boolean {
    if (
      this.user &&
      comment.isCreator &&
      comment.createUser === this.user.sUserName
    ) {
      return true;
    }
    return false;
  }

  get commentNum(): number {
    return this.commentList.length;
  }

  updateIssuesInfo(): void {
    openLoading("加载中...");

    getIssuesInfo(this.projectName, this.userName, this.issueID)
      .then(data => {
        closeLoading();
        let issueInfo = data.issueInfo;
        this.commentList = data.issueInfo.comments;
        if (
          this.user &&
          issueInfo.isCreator &&
          issueInfo.createUser === this.user.sUserName
        ) {
          this.isIssueCreator = true;
        }
        this.isIssueCreator = false;

        this.title = issueInfo.title;
        this.content = issueInfo.content;
        this.selectLevel = issueInfo.level;
        this.selectStatus = issueInfo.status;
        this.selectType = issueInfo.type;
        this.createUserName = issueInfo.createUser;

        this.showTitle = issueInfo.title;
        this.showContent = issueInfo.content;
        this.showSelectLevel = issueInfo.level;
        this.showSelectStatus = issueInfo.status;
        this.showSelectType = issueInfo.type;
        this.showCreateUserName = issueInfo.createUser;
        this.showCreateTime = issueInfo.createTime;
      })
      .catch(err => {
        closeLoading();
        const url = `/issues/list/all?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}`;
      
        this.$router.push(url)
      });
  }

  created(): void {
    // 判断是否有权限
    this.userName = this.$route.query.userName;
    this.projectName = this.$route.query.projectName;
    this.issueID = this.$route.query.issueID;

    if (!this.userName || !this.projectName || !this.issueID) {
      this.$router.push("/");
    }
    this.updateIssuesInfo();
  }

  editIssues(): void {
    this.isEdit = true;
  }
  cancleIssues(): void {
    this.isEdit = false;
  }

  resetComment(): void {
    this.commentText = "";
  }

  // 发送编辑issue请求
  saveIssues(): void {
    this.title = trim(this.title)
    this.content = trim(this.content)
    if(!this.title)
    {
      return this.$alert("标题不能为空")
    }
    if(!this.content)
    {
      this.content = ""
    }
    openLoading("正在编辑中...")
    editIssues(this.projectName,this.userName,this.title,this.content,this.selectType,this.selectStatus,this.selectLevel,this.issueID)
    .then(data => {
        closeLoading();
        const url = `/issues/list/all?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}`;
      
        this.$router.push(url)
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
    
  }

  // 发送添加评论请求
  newComment(): void {
    this.commentText = trim(this.commentText)
    if (this.commentText === "") {
      return this.$alert("评论不能为空");
    }
    openLoading("评论中...");
    addComment(this.commentText, this.issueID)
      .then(data => {
        closeLoading();
        this.reloadPage();
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
  }

  // 删除评论请求
  removeComment(id: number): void {
    if (!id) {
      return this.$alert("操作错误");
    } else {
      openLoading("删除中...");
      deleteComment(this.projectName, this.userName, this.issueID, id)
        .then(data => {
          closeLoading();
          this.reloadPage();
        })
        .catch(err => {
          closeLoading();
          this.$alert(err);
        });
    }
  }

  removeIssue(): void{
    openLoading("删除中...");
      deleteIssues(this.projectName, this.userName, this.issueID)
        .then(data => {
          closeLoading();
          this.reloadPage();
        })
        .catch(err => {
          closeLoading();
          this.$alert(err);
        });
  }
}
</script>

<style>
.add-button {
  position: absolute;
  bottom: 12px;
  right: 16px;
}
.md-layout {
  min-height: 500px;
  padding: 0 16px;
}
.md-layout-item {
  padding: 16px;
}
.md-count {
  display: none;
}
.title {
  padding: 16px 25px;
  font-weight: 700;
  font-size: 24px;
  line-height: 32px;
  margin: 0;
}
.comment-title {
  font-size: 24px;
  font-weight: 700;
  text-align: left;
  margin: 25px 0;
}
.tip {
  padding: 0 0 16px 25px;
}
</style>