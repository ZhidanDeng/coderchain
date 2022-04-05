<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-toolbar md-elevation="0"
                    class="md-transparent">
          <div>
            <md-icon class="fa fa-inbox"></md-icon>
            <router-link :to="`/user/${userName}`">{{ userName }}</router-link>
            <span>&nbsp;/&nbsp;</span>
            <router-link :to="`/detail/code?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"
                         active-class="active-class">{{ projectName | decodeURIComponent }}</router-link>

          </div>
        </md-toolbar>

        <md-tabs md-sync-route
                 md-alignment="left">
          <md-tab id="tab-code"
                  md-label="代码"
                  :to="`/detail/code?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"></md-tab>
          <!-- <md-tab id="tab-report"
                  md-label="评测报告"
                  :to="`/detail/report?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"></md-tab> -->
          <md-tab id="tab-vote"
                  md-label="投票详情"
                  :to="`/detail/vote?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"></md-tab>
          <md-tab id="tab-settings"
                  md-label="设置"
                  :to="`/detail/setting?projectName=${encodeURIComponent(projectName)}&userName=${userName}`"></md-tab>
          <!-- <md-tab id="tab-more"
                  md-label="更多操作" :to="`/detail/more?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
          </md-tab> -->
        </md-tabs>
        <md-divider></md-divider>
        <keep-alive>
          <router-view :userName="userName"
                       :projectName="projectName"
                       :isOwner="isOwner"
                       :user="user" />
        </keep-alive>
      </md-card-content>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject } from 'vue-property-decorator'
import { Getter } from 'vuex-class'
import { UserInterface } from '@utils/interface'

@Component
export default class DetailIndex extends Vue {
  userName: any = ''
  projectName: any = ''

  @Inject('onShowLoginBox') onShowLoginBox: any

  @Getter user!: UserInterface

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
    console.log('detail-index created')
    console.log(this.$route.query.userName)

    this.userName = this.$route.query.userName
    this.projectName = this.$route.query.projectName
  }
}
</script>

<style lang="stylus" scoped>
a.router-link-exact-active.active-class
  color rgba(0, 0, 0, 0.87) !important
a.router-link-exact-active.active-class:hover
  color #448aff !important

.md-layout-item {
  height: 200px;
}
</style>
