import VueRouter, { Route } from 'vue-router'
import { Store } from 'vuex'
declare module 'vue/types/vue' {
  // 定义在Vue实例上，this调用
  interface Vue {
    $store: Store<any>
    $route: Route

    // user define
    $fullLoading: any
    $alert: any
    $snackbar: any
    $confirm: any
  }

  // 定义在Vue上，静态属性
  interface VueConstructor {
    options: any
  }
}

declare module 'axios/dist/types' {
  interface AxiosRequestConfig {
    retry: number
  }
}
