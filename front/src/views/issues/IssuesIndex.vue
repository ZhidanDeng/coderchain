<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-toolbar md-elevation="0" class="md-transparent">
          <div>
            <md-icon class="fa fa-inbox"></md-icon>
            <router-link :to="`/user/${userName}`">{{ userName }}</router-link>
            <span>&nbsp;/&nbsp;</span>
            <router-link :to="`/detail/code?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"
                         active-class="active-class">{{ projectName | decodeURIComponent }}</router-link>

          </div>
          <md-button class="md-raised md-accent add-button"  @click="newIssues">新增issues</md-button>
        </md-toolbar>
        <md-divider></md-divider>
        <keep-alive>
          <router-view :userName="userName"
                       :projectName="projectName"
                       :isOwner="isOwner"  />
        </keep-alive>
      </md-card-content>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject,Provide } from 'vue-property-decorator'
import { Getter } from 'vuex-class'
import { UserInterface } from '@utils/interface'

@Component
export default class IssuesIndex extends Vue {
  userName: any = ''
  projectName: any = ''
  
  

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
    this.userName = this.$route.query.userName
    this.projectName = this.$route.query.projectName
    if(!this.userName || !this.projectName)
    {
      this.$router.push("/");
    }
  }

  newIssues():void{
    if (this.isLogin) {
      const url = `/issues/add?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}`;
      
      this.$router.push(url);
    } else {
      this.onShowLoginBox();
    }
  }
}
</script>

<style>
.add-button{
  position: absolute;
  bottom: 12px;
  right: 16px;
}
</style>