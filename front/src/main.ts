import Vue from 'vue'
import App from './App.vue'
import router from './router/router'
// import Router from 'vue-router'
import store from './store/store'
import VueMaterial from 'vue-material'
import Loading from './components/loading/Loading.vue'
import 'vue-material/dist/vue-material.min.css'
// import 'vue-material/dist/theme/black-green-light.css'
import 'vue-material/dist/theme/default.css'
import './assets/styles/common.styl'
import './assets/styles/font-awesome.min.css'
import './filters/index'
import Alert from './components/alert/'
import Snackbar from './components/snackbar/'
import FullLoading from './components/loading/'
import Confirm from './components/confirm/'
import eventBus from '@utils/bus'
import VueWechatTitle from 'vue-wechat-title'
// @ts-ignore
import isDev from '@utils/isDev'

// 禁止console.log
if (!isDev) {
  console.log = () => { }
}

// require('@utils/share-variable')

Vue.config.productionTip = false

// 解决vue-material无法找到router-link和router-view的bug
Vue.component('router-link', Vue.options.components.RouterLink)
Vue.component('router-view', Vue.options.components.RouterView)

Vue.component('g-loading', Loading)

Vue.use(VueMaterial)
Vue.use(Alert)
Vue.use(Snackbar)
Vue.use(FullLoading)
Vue.use(Confirm)
Vue.use(VueWechatTitle)

// 权限认证
// '/about', '/feedback'
const BLACK_LIST: Array<string> = []
router.beforeEach((to, from, next) => {
  const path = to.path
  if (
    BLACK_LIST.includes(path) &&
    // @ts-ignore
    (!store.state.user || !store.state.user.sId) &&
    from.path !== '/'
  ) {
    console.log('需要进行权限控制')
    eventBus.$emit('unLogin')
    eventBus.$on('loginSuccess', () => {
      console.log('登录成功，允许通过')
      next()
    })
  } else {
    next()
  }
})

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
