<template>
  <div class="bg">
    <md-dialog :md-active="showDialog"
               class="bg-dialog">
      <md-dialog-title>
        <img src="@assets/images/logo-en-min.png"
             alt="CoderChain"
             class="logo">
      </md-dialog-title>

      <md-tabs md-dynamic-height
               :md-active-tab="activeTabId"
               @md-changed="changeTab">
        <!-- 只使用登录易登录注册 -->
        <template v-if="isOnlyUsedDenglu1">
          <md-tab md-label="登录"
                  id="login">
            <!-- 图片 -->
            <div id="DengLu1_QRCode"
                 class="qrcode"></div>
            <div class="align-center">
              <md-button class="md-primary md-raised"
                         @click="onLoginByDenglu1">刷新登录二维码</md-button>
              <p>
                # 使用登录易APP扫码登录
              </p>
            </div>
          </md-tab>

          <md-tab md-label="注册"
                  id="register">
            <div id="DengLu1_QRCode_Register"
                 class="qrcode"></div>
            <div class="align-center">
              <md-button class="md-primary md-raised"
                         @click="onRegisterByDenglu1">刷新注册二维码</md-button>
              <p>
                # 使用登录易APP扫码注册
              </p>
            </div>
          </md-tab>
        </template>
        <template v-else>
          <!-- 不使用登录易 -->
          <md-tab md-label="登录"
                  id="login">

            <div v-if="!showLoginQRCode">
              <md-field md-clearable>
                <label>账户名</label>
                <md-input v-model="loginName"></md-input>
              </md-field>

              <md-field>
                <label>密码</label>
                <md-input v-model="loginPwd"
                          type="password"></md-input>
              </md-field>

              <div class="align-center">
                <md-button class="md-primary md-raised"
                           @click="onLogin">登录</md-button>
                <p>
                  <a @click="onLoginByDenglu1">登录易扫码登录</a>
                </p>
              </div>
            </div>

            <div v-if="showLoginQRCode">
              <div id="DengLu1_QRCode"
                   class="qrcode"></div>
              <div class="align-center">
                <p>
                  <a @click="showLoginQRCode = false">传统方式登录</a>
                </p>
              </div>
            </div>

          </md-tab>

          <md-tab md-label="注册"
                  id="register">

            <div v-if="!showRegisterQRCode">
              <md-field md-clearable>
                <label>账户名</label>
                <md-input v-model="registerName"></md-input>
              </md-field>

              <md-field>
                <label>密码</label>
                <md-input v-model="registerPwd"
                          type="password"></md-input>
              </md-field>
              <md-field>
                <label>确认密码</label>
                <md-input v-model="registerPwd2"
                          type="password"></md-input>
              </md-field>
              <div class="align-center">
                <md-button class="md-primary md-raised"
                           @click="onRegister">注册</md-button>
                <p>
                  <a @click="onRegisterByDenglu1">登录易扫码注册</a>
                </p>
              </div>
            </div>
            <div v-if="showRegisterQRCode">
              <div id="DengLu1_QRCode_Register"
                   class="qrcode"></div>
              <div class="align-center">
                <p>
                  <a @click="showRegisterQRCode = false">传统方式注册</a>
                </p>
              </div>
            </div>

          </md-tab>
        </template>

      </md-tabs>

      <md-dialog-actions>
        <md-button class="md-primary"
                   @click="close">Close</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator'
import { login, register } from '@api/user'
import { trim } from '@utils/index'
import { State, Mutation, Action, Getter } from 'vuex-class'
import { SET_USER } from '@/store/mutation-types'
import {
  openLoading,
  closeLoading,
  openBar,
  closeBar
} from '@utils/share-variable'
// @ts-ignore
import DengLu1 from './DengLu1'
import eventBus from '@utils/bus'
import isDev from '@utils/isDev'

const isOnlyUsedDenglu1 = !isDev

@Component
export default class Login extends Vue {
  isOnlyUsedDenglu1: boolean = isOnlyUsedDenglu1
  showLoginQRCode: boolean = isOnlyUsedDenglu1
  showRegisterQRCode: boolean = isOnlyUsedDenglu1
  loginName: string = ''
  loginPwd: string = ''
  registerName: string = ''
  registerPwd: string = ''
  registerPwd2: string = ''


  @Prop({ default: false }) readonly showDialog!: boolean
  @Prop({ default: 'login' }) readonly activeTabId!: string

  @Mutation(SET_USER) setUser: any
  @Action('setUserToken') setUserToken: any

  @Watch('showDialog')
  showDialogActive() {
    if (this.isOnlyUsedDenglu1) {
      if (this.showDialog === true) {
        setTimeout(() => {
          this.refreshCode(this.activeTabId)
        }, 500)
      }
    }
  }




  changeTab(id: string) {
    console.log('change id: ', id)
    if (this.isOnlyUsedDenglu1) {
      this.refreshCode(id)
    }
  }
  refreshCode(activeTabId: string) {
    if (activeTabId === 'login') {
      this.onLoginByDenglu1()
    } else {
      this.onRegisterByDenglu1()
    }
  }
  created() {
    console.log('created')
  }
  activated() {
    console.log('activated')
  }

  close() {
    this.$emit('close')
  }

  onLogin() {
    console.log('登录：', this.loginName, this.loginPwd)
    this.loginName = trim(this.loginName)
    this.loginPwd = trim(this.loginPwd)
    if (this.loginName === '') {
      openBar('用户名不能为空')
      return
    }

    if (this.loginPwd === '') {
      openBar('密码不能为空')
      return
    }

    // 如果提示信息还存在，就清空
    closeBar()
    openLoading('正在登录中...')

    login(this.loginName, this.loginPwd)
      .then(data => {
        console.log('登录成功：', data)
        this.loginName = ''
        this.loginPwd = ''
        this.setUser(data)
        setTimeout(() => {
          closeLoading()
          this.close()
        }, 1000)
        // 关闭登录二维码
        this.showLoginQRCode = false
        // 关闭注册二维码
        this.showRegisterQRCode = false
        // 获取用户代币
        this.setUserToken()
        // 发出登录事件
        eventBus.$emit('loginSuccess')
      })
      .catch(err => {
        console.log('登录失败：', err)
        closeLoading()
        openBar(err)
        // 重新刷新登录易登录二维码
        if (this.showLoginQRCode) {
          this.buildLoginQRCode()
        }
      })
  }
  onLoginByDenglu1() {
    this.showLoginQRCode = true
    this.buildLoginQRCode()
  }
  onRegister() {
    console.log(
      '注册：',
      this.registerName,
      this.registerPwd,
      this.registerPwd2
    )

    this.registerName = trim(this.registerName)
    this.registerPwd = trim(this.registerPwd)
    this.registerPwd2 = trim(this.registerPwd2)
    if (this.registerName === '') {
      openBar('用户名不能为空')
      return
    }

    if (this.registerPwd === '') {
      openBar('密码不能为空')
      return
    }

    if (this.registerPwd !== this.registerPwd2) {
      openBar('两次输入的密码不一致')
      return
    }

    // 如果提示信息还存在，就清空
    closeBar()
    openLoading('正在注册中...')

    register(this.registerName, this.registerPwd)
      .then(data => {
        console.log('注册成功：', data)
        this.loginName = this.registerName
        this.loginPwd = this.registerPwd
        // 重置注册信息
        this.registerName = ''
        this.registerPwd = ''
        this.registerPwd2 = ''
        // 注册成功后自动帮用户登录
        setTimeout(() => {
          closeLoading()
          this.close()
          openBar('注册成功，请开始登录并部署你的项目吧')
        }, 1000)
      })
      .catch(err => {
        console.log('注册失败：', err)
        closeLoading()
        openBar(err)
        // 重新刷新注册码
        if (this.showRegisterQRCode) {
          this.buildRegisterQRCode()
        }
      })
  }
  onRegisterByDenglu1() {
    this.showRegisterQRCode = true
    this.buildRegisterQRCode()
  }

  buildLoginQRCode() {
    DengLu1.login({
      appId: 1, // 接入方企业ID号
      width: 200, // 生成2维码宽度
      height: 200, // 生成2维码高度
      password: 'password', // 密码填充到控件ID
      username: 'username', // 用户名填充到控件ID
      QRCodeImageId: 'DengLu1_QRCode', // 二维码生成到页面的哪个区域
      success: (data: any) => {
        // 登录信息回调 data包含一些登录信息
        this.loginName = data.sUserName
        this.loginPwd = data.sPwd
        // console.log('扫码的:', data)
        this.onLogin()
      }
    })
  }

  buildRegisterQRCode() {
    DengLu1.register({
      appId: 1, // 接入方企业ID号
      width: 200, // 生成2维码宽度
      height: 200, // 生成2维码高度
      password: 'password', // 密码填充到控件ID
      username: 'username', // 用户名填充到控件ID
      QRCodeImageId: 'DengLu1_QRCode_Register', // 二维码生成到页面的哪个区域
      success: (data: any) => {
        console.log('注册返回的数据：', data)
        // 注册信息回调 data包含一些登录信息
        this.registerName = data.sUserName
        this.registerPwd = data.sPwd
        this.registerPwd2 = data.sPwd
        this.onRegister()
      }
    })
  }
}
</script>

<style lang="stylus" scoped>
// .md-dialog-container
// width 350px
.md-dialog-title
  text-align center
.logo
  width 200px
.qrcode
  margin-top 5px
  text-align center
</style>

<style>
.bg-dialog .md-dialog-container {
  width: 350px;
  overflow: hidden;
}

.qrcode img {
  display: inline-block !important;
}
</style>


