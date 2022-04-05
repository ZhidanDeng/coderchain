<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-toolbar md-elevation="0"
                    class="md-transparent">
          <div>
            <md-icon class="fa fa-user"></md-icon>
            <router-link :to="`/user/${userName}`">{{ userName }}</router-link>&nbsp;
            <span>的个人主页</span>
          </div>
        </md-toolbar>
        <md-divider></md-divider>
        <md-card v-if="detail"
                 class="no-shadow">
          <md-card-header>
            <md-avatar>
              <img :src="avatar"
                   alt="avatar">
            </md-avatar>

            <div class="md-title">{{ userName }}</div>
            <div class="md-subhead">{{ detail.iSex | filterSex}}</div>
          </md-card-header>

          <md-card-content>
            <div class="viewport">
              <md-list>
                <md-list-item>
                  <md-icon class="fa fa-user-circle"></md-icon>
                    <span class="md-list-item-text">{{ detail.sUserName}}</span>
                </md-list-item>

                <md-list-item>
                  <md-icon class="fa fa-phone"></md-icon>
                  <span class="md-list-item-text">{{ detail.sWalletAddress }}</span>
                </md-list-item>
                <md-list-item v-if="isOwner">
                  <md-icon class="fa fa-file-text"></md-icon>
                  <span class="md-list-item-text" v-if="this.prikey">
                    {{ this.prikey }}
                  </span>
                  <span v-else class="md-list-item-text"><md-button class="md-raised md-primary" @click="onPriKeyDialog">获取私钥</md-button></span>
                </md-list-item>
                <md-list-item>
                  <md-icon class="fa fa-file-text"></md-icon>
                  <span class="md-list-item-text">{{ detail.sDescription ? detail.sDescription : '暂无简介'}}</span>
                </md-list-item>
              </md-list>
            </div>
          </md-card-content>

        </md-card>
      <md-dialog :md-active.sync="showPriKeyDiaLog"  class="pri_dialog">
      <md-dialog-title>私钥获取</md-dialog-title>
      <md-field>
        <label>用户名</label>
        <md-input v-model="detail.sUserName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
      </md-field>
      <md-dialog-actions>
        <md-button class="md-primary" @click="showPriKeyDiaLog = false">取消</md-button>
        <md-button class="md-primary" @click="onConfirmPriKey">获取私钥</md-button>
      </md-dialog-actions>
    </md-dialog>
        <md-divider></md-divider>
        <md-tabs md-sync-route>
          <md-tab id="tab-code"
                  md-label="我的项目"
                  :to="`/user/${userName}/project`"></md-tab>
          <md-tab id="tab-fromTx"
                  md-label="发款交易"
                  :to="`/user/${userName}/fromTx`"></md-tab>
          <md-tab id="tab-toTx"
                  md-label="收款交易"
                  :to="`/user/${userName}/toTx`"></md-tab>
          <!-- <md-tab id="tab-report"
              md-label="支持过的项目"
              :to="`/user/${userName}/support`"></md-tab> -->
          <!-- <md-tab id="tab-vote"
              md-label="投票情况"
              :to="`/detail/vote?userName=${userName}`"></md-tab>
      <md-tab id="tab-settings"
              md-label="设置"
              :to="`/detail/setting?userName=${userName}`"></md-tab> -->
        </md-tabs>
        
          <router-view :userName="userName"
                       :sAvatar="detail ? detail.sAvatar: ''"
                       :key="activeDate" />
        
      </md-card-content>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject, Watch } from 'vue-property-decorator'
import { Getter } from 'vuex-class'
import { UserInterface } from '@utils/interface'
import { getUserInfoByName, getUserPriKey } from '@api/user'
import { RouteConfig } from 'vue-router'
import { RoutePropsFunction, RouteRecord } from 'vue-router/types/router'
import baseURL from '@utils/api-url'

const user = {
  sId: '',
  sUserName: '',
  sDisplayName: '',
  sAvatar: '',
  sWalletAddress: '',
  iSex: '-1',
  sDescription: ""
}

@Component
export default class DetailIndex extends Vue {
  userName: string = ''
  $loadingInstance: any = null
  detail: UserInterface = user
  activeDate: string = ''
  showPriKeyDiaLog: boolean = false;
  prikey: string = ''
  password: string =  ""

  @Inject('onShowLoginBox') onShowLoginBox: any

  @Getter user!: UserInterface

  created(): void {
    console.log('detail-index created')
    this.userName = this.$route.params.userName
    this.fetchUserInfo()
  }
  

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
  // 获取图像路径
  get avatar(): string {
    if (this.detail && this.detail.sAvatar) {
      return `${baseURL}/user/User/getImage?sImagePath=${
        this.detail.sAvatar
      }`
    }
    else
    {
      return `${baseURL}/user/User/getImage?sImagePath=""`
    }
  }

  onPriKeyDialog(): void{
    this.showPriKeyDiaLog = true
  }

  onConfirmPriKey(): void {
    /*
    iSex: (...)
    sAvatar: (...)
    sDescription: (...)
    sDisplayName: (...)
    sUserName: (...)
    */
    getUserPriKey(this.detail.sUserName, this.password)
      .then(data => {
        this.prikey = data
        this.showPriKeyDiaLog = false
      })
      .catch(err => {
        this.$alert(err)
      })
  }


  // 获取用户信息
  fetchUserInfo(): void {
    /*
    iSex: (...)
    sAvatar: (...)
    sDescription: (...)
    sDisplayName: (...)
    sUserName: (...)
    */
    getUserInfoByName(this.userName)
      .then(data => {
        this.detail = data
        console.log('获取到的用户信息：', data)
      })
      .catch(err => {
        this.$alert(err)
      })
  }

  @Watch('$route')
  handleRouteChange(to: RouteConfig, from: RouteConfig): void {
    const userName = this.$route.params.userName
    if (
      this.userName &&
      userName &&
      this.userName !== userName &&
      to.name === 'user-project'
    ) {
      this.userName = userName
      this.fetchUserInfo()
      this.activeDate = new Date() + '--'
    }
  }
}
</script>


<style lang="stylus" scoped>
.md-primary
  width 100px
.pri_dialog
  width 400px
  padding 20px
</style>