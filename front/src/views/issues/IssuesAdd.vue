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
          
        </md-toolbar>
        <md-divider></md-divider>
        <div class="md-layout">
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
                    <md-select v-model="selectType"  id="type">
                        <md-option
                          v-for="dir in issueType"
                          :value="typeObject[dir]"
                          :key="dir"
                        >{{ dir }}</md-option>
                        
                    </md-select>
                </md-field>
                <md-field>
                    <label for="status">当前状态</label>
                    <md-select v-model="selectStatus"  id="status">
                        <md-option
                          v-for="dir in issueStatus"
                          :value="statusObject[dir]"
                          :key="dir"
                        >{{ dir }}</md-option>
                        
                    </md-select>
                </md-field>
                <md-field>
                    <label for="level">优先级</label>
                    <md-select v-model="selectLevel"  id="level">
                        <md-option
                          v-for="dir in issueLevel"
                          :value="levelObject[dir]"
                          :key="dir"
                        >{{ dir }}</md-option>
                        
                    </md-select>
                </md-field>
                <div>
                    <md-button  class="md-raised md-accent" @click="addIssues">保存</md-button>
                    <md-button  class="md-raised md-primary" @click="resetIssues">重置</md-button>
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
import { UserInterface } from "@utils/interface";
import {addIssues} from "@api/issues"
import { openLoading, closeLoading } from "@utils/share-variable";
import {
  trim
} from '@utils/index'

@Component
export default class IssueDetail extends Vue {
  userName: any = ''
  projectName: any = ''

  selectType: number = -1
  issueType: Array<string> = [
      "未设置","bug","enhancement","feature","duplicate","invalid","question","wontfix"
  ]
  typeObject: object = {
      "未设置":-1,"bug":0,"enhancement":1,"feature":2,"duplicate":3,"invalid":4,"question":5,"wontfix":6
  }

  selectStatus: number = 0
  issueStatus: Array<string> = [
      "开启的","待办的","进行中","已完成","已拒绝"
  ]
  statusObject: object = {
      "开启的":0,"待办的":1,"进行中":2,"已完成":3,"已拒绝":4
  }

  selectLevel: number = -1
  issueLevel: Array<string> = [
      "不指定","严重","主要","次要","不重要"
  ]
  levelObject: object = {
      "不指定":-1,"严重":0,"主要":1,"次要":2,"不重要":3
  }

  title: string = ""
  content: string = ""
  createUserName:string = ""


  @Inject('onShowLoginBox') onShowLoginBox: any

  @Getter user!: UserInterface
  @Getter isLogin!: boolean;
  // 是否有权编辑项目
  get isOwner(): boolean {
    
    if (
      this.user &&
      this.user.sUserName &&
      this.user.sUserName === this.userName
    ) {
      return true
    }
    return false
  }

  created(): void {
    
    if(!this.isLogin)
    {
        this.onShowLoginBox();
    }
    // 判断是否有权限

    this.userName = this.$route.query.userName
    this.projectName = this.$route.query.projectName
    if(!this.userName || !this.projectName )
    {
      this.$router.push("/");
    }
    this.createUserName = this.user.sUserName
    
  }

  addIssues():void{
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
    openLoading("正在创建中...")

    addIssues(this.projectName,this.userName,this.title,this.content,this.selectType,this.selectStatus,this.selectLevel)
      .then(data => {
        closeLoading();
        const url = `/issues/detail?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}&issueID=${data.issuesID}`;
      
        this.$router.push(url);
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
    
  }
  resetIssues():void{
    this.title = ""
    this.content = ""
    this.selectStatus = 0
    this.selectType = -1
    this.selectLevel = -1
  }
  
}
</script>

<style>
.add-button{
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
.md-count{
    display: none;
}
</style>