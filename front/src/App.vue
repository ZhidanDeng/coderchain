<template>
  <div id="app">
    <div class="view">
      <main class="main">
        <div id="nav">
          <div class="container clearfix">
            <md-list class="nav-list nav-list-left md-transparent">
              <md-list-item class="nav-item-logo">
                <router-link to="/">
                  <img src="./assets/images/logo-en-min.png"
                       alt="CoderChain"
                       class="logo">
                </router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/project">发现项目</router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/rank">排行榜</router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/transfer">在线转账</router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/tx">交易动态</router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/feedback">反馈</router-link>
              </md-list-item>
              <md-list-item>
                <router-link to="/about">关于</router-link>
              </md-list-item>
              <!-- <md-list-item>
                <a @click.prevent="getId">获取session</a>
              </md-list-item> -->
            </md-list>

            <md-list class="nav-list nav-list-right">
              <!-- <md-list-item>
                <md-field md-clearable
                          class="md-toolbar-section-end">
                  <md-input placeholder="搜索项目名称..."
                            v-model="search"
                            @input="handleSearchOnTable" />
                </md-field>
              </md-list-item> -->
              <template v-if="this.user && this.user.sId">
                <md-list-item>
                  <md-menu md-size="auto"
                           md-align-trigger
                           md-direction="bottom-end"
                           class="cursor-pointer">
                    <md-icon class="fa fa-plus"
                             md-menu-trigger></md-icon>
                    <md-menu-content>
                      <md-menu-item to="/project/create">
                        创建项目
                      </md-menu-item>
                    </md-menu-content>
                  </md-menu>
                </md-list-item>

                <md-list-item>
                  <md-menu md-size="auto"
                           md-align-trigger
                           md-direction="bottom-end">
                    <md-avatar md-menu-trigger
                               class="cursor-pointer">
                      <img :src="avatar"
                           :alt="user.sUserName">
                    </md-avatar>

                    <md-menu-content class="collapse-list">
                      <md-menu-item class="md-list-item-text">
                        <span class="md-list-item-text">用户：{{ this.user.sUserName }}</span>
                      </md-menu-item>
                      <md-menu-item>代币：{{ this.user.token !== undefined ? this.user.token : '---' }}CDB</md-menu-item>
                      <md-divider></md-divider>
                      <md-menu-item to="/setting/profile">我的设置</md-menu-item>
                      <md-menu-item to="/setting/password">修改密码</md-menu-item>
                      <md-menu-item :to="`/user/${this.user.sUserName}`">
                        我的项目
                      </md-menu-item>
                      <md-divider></md-divider>
                      <md-menu-item @click="onLogout">
                        退出
                      </md-menu-item>
                    </md-menu-content>
                  </md-menu>
                </md-list-item>
              </template>

              <template v-else>
                <md-list-item>
                  <a @click.stop="onShowLoginBox">登录</a>
                </md-list-item>
                <md-list-item>
                  <a @click.stop="onShowRegisterBox">注册</a>
                </md-list-item>
              </template>

            </md-list>
          </div>
        </div>
        <keep-alive>
          <router-view v-if="$route.meta && $route.meta.keepAlive"
                       v-wechat-title="$route.meta.title"></router-view>
        </keep-alive>

        <router-view v-if="!($route.meta && $route.meta.keepAlive) && isRouterAlive"
                     v-wechat-title="$route.meta.title" ></router-view>
        <!-- <router-view /> -->
        <template v-if="!isUsedOpenLogin">
          <login :showDialog="showLogin"
                 @close="onCloseLoginBox"
                 :activeTabId="activeTabId"></login>
        </template>

        <return-to-top></return-to-top>
      </main>
      <footer class="footer">
        <md-toolbar class="md-transparent">
          <div class="copyright align-center">
            <span class="md-subhead">©Copyright - CoderChain. All Rights Reserved.</span></div>
        </md-toolbar>
      </footer>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Provide, Watch } from 'vue-property-decorator'
import Login from './views/user/Login.vue'
import ReturnToTop from '@components/return-to-top/ReturnToTop.vue'
import { Getter, Mutation, Action } from 'vuex-class'
import { UserInterface } from '@utils/interface'
import {
  logout,
  getSession,
  getUserToken,
  getLoginState,
  loginByToken
} from '@api/user'
import bus from '@utils/bus'
import baseURL from '@utils/api-url'
import isDev from '@utils/isDev'

const LOCAL_KEY: string = '__CODERCHAIN__'

//获取url中的参数
function getUrlParam(name: any) {
  var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)') //构造一个含有目标参数的正则表达式对象
  var r = window.location.search.substr(1).match(reg) //匹配目标参数
  if (r != null) return unescape(r[2])
  return null //返回参数值
}

@Component({
  components: {
    Login,
    ReturnToTop
  }
})
export default class App extends Vue {
  showLogin: boolean = false
  activeTabId: string = 'login'
  isRouterAlive: boolean = true
  isUsedOpenLogin: boolean = !isDev
  
  

  @Getter user!: UserInterface
  @Getter isLogin!: boolean
  @Mutation('SET_USER') setUser: any
  @Action('setUserToken') setUserToken: any

  get avatar(): string {
    if (!this.user) return ''
    return `${baseURL}/user/User/getImage?sImagePath=${this.user.sAvatar}`
  }

  created(): void {
    
    // 页面刷新前存储数据
    window.addEventListener('beforeunload', () => {
      localStorage.setItem(LOCAL_KEY, JSON.stringify(this.$store.state))
    })

    if (localStorage.getItem(LOCAL_KEY)) {
      this.$store.replaceState(
        Object.assign(
          {},
          this.$store.state,
          // @ts-ignore
          JSON.parse(localStorage.getItem(LOCAL_KEY))
        )
      )
    }

    getLoginState()
    .then(data => {
      if(!this.isLogin)
      {
        this.setUser(data)
      }
      this.setUserToken()
      // 这里如果已经登录
      // console.log('data: ', data)
      // this.setUser(data)
      // if (this.isLogin && this.user.token === undefined) {
      //   console.log('还没有token数，拉取token')
      //   this.setUserToken()
      // }
    })
    .catch(err => {
      // error: 没有登录，清空信息
      console.log('error: ', err)
      this.setUser({})
      localStorage.removeItem(LOCAL_KEY)
    })

    // 登录易扫描登录获取到的token
    // let token = getUrlParam('sToken')
    // if (token) {
    //   // 根据token查询信息
    //   const newUrl = window.location.href.replace(/\?(.*)/g, '')
    //   loginByToken(token)
    //     .then(data => {
    //       this.setUser(data)
    //       // 清空查询参数
    //       window.history.pushState({}, document.title, newUrl)

    //       if (this.isLogin && this.user.token === undefined) {
    //         console.log('还没有token数，拉取token')
    //         this.setUserToken()
    //       }
    //     })
    //     .catch(err => {
    //       console.log('查看token状态：', err)
    //       this.$alert(err)
    //       // 清空查询参数
    //       window.history.pushState({}, document.title, newUrl)
    //     })
    // } else {
    //   getLoginState()
    //     .then(data => {
    //       if(!this.isLogin)
    //       {
    //         this.setUser(data)
    //       }
    //       this.setUserToken()
    //       // 这里如果已经登录
    //       // console.log('data: ', data)
    //       // this.setUser(data)
    //       // if (this.isLogin && this.user.token === undefined) {
    //       //   console.log('还没有token数，拉取token')
    //       //   this.setUserToken()
    //       // }
    //     })
    //     .catch(err => {
    //       // error: 没有登录，清空信息
    //       console.log('error: ', err)
    //       this.setUser({})
    //       localStorage.removeItem(LOCAL_KEY)
    //     })
    // }

    // 订阅未登录事件
    bus.$on('unLogin', () => {
      console.log('bus 收到了未登录事件')
      this.onShowLoginBox()
    })

    // 如果登录的话拉取Token数
    // if (this.isLogin && this.user.token === undefined) {
    //   console.log('还没有token数，拉取token')
    //   this.setUserToken()
    // }
  }
  @Provide('reloadPage')
  reloadPage(): void {
    this.isRouterAlive = false;
    this.$nextTick(function() {
      this.isRouterAlive = true;
    })
  }
  

  @Provide('onShowLoginBox')
  onShowLoginBox(): void {
    if (!this.isUsedOpenLogin) {
      this.activeTabId = 'login'
      this.showLogin = true
    } else {
      // 打开open
      const url =
        'https://qrconnect.denglu.net.cn/connect.php?sAppId=e7d5623bd29f1a4aaa1238cedc8af61b&sUrl=http://coderchain.cn&sType=login&sResType=web'
      window.location.href = url
    }
  }

  getId(): void {
    getSession()
      .then(data => {
        console.log('获取session的结果：', data)
      })
      .catch(err => {
        console.log('获取session错误：', err)
      })
  }

  onShowRegisterBox(): void {
    if (!this.isUsedOpenLogin) {
      this.activeTabId = 'register'
      this.showLogin = true
    } else {
      const url =
        'https://qrconnect.denglu.net.cn/connect.php?sAppId=e7d5623bd29f1a4aaa1238cedc8af61b&sUrl=http://coderchain.cn&sType=register&sResType=web'
      window.location.href = url
    }
  }

  onCloseLoginBox(): void {
    this.showLogin = false
  }

  onLogout(): void {
    logout()
      .then(() => {
        this.setUser({})
        const instance = this.$snackbar({
          content: '退出成功'
        })
        setTimeout(() => {
          instance.close()
        }, 1000)
      })
      .catch(err => {
        console.log('退出失败：', err)
      })
  }

  @Watch('$route')
  handleRouteChange(): void {
    const userName = this.$route.params.userName
    console.log('路由发生变化>>>')
    // 获取用户Token
    if (this.isLogin) {
      getUserToken()
        .then(data => {
          // iCode, dBalance
          console.log('获取到的token:', data)
          const tokenUnit = 100000000
          const token = parseFloat(
            // @ts-ignore
            parseInt(data.dBalance) / tokenUnit
          ).toFixed(2)

          this.setUser(
            Object.assign({}, this.user, {
              token: token
            })
          )
        })
        .catch(err => {
          if (err === '没有登录') {
            this.setUser({})
          }
          console.log('获取Token出错啦：', err)
        })
    } else {
    }
  }
}
</script>


<style lang="stylus" scoped>
#app
  font-family Consolas, 'Avenir', Helvetica, Arial, sans-serif
  -webkit-font-smoothing antialiased
  -moz-osx-font-smoothing grayscale
.container
  max-width 1024px
#nav
  margin-bottom 20px
  background-color #fff
  border-bottom 2px solid #eee
.nav-list
  flex-flow row wrap
.nav-list-left
  float left
.nav-list-right
  float right
.logo
  width 150px
.view
  min-height 100vh
  display flex
  flex-direction column
  justify-content space-between
.footer
  margin-top 20px
.copyright
  flex 1
</style>

<style>
.collapse-list .md-list-item-content {
  min-height: 30px;
}
</style>

