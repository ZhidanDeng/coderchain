import Vue from 'vue'
import Vuex from 'vuex'
import { getUserToken } from '@api/user'
import * as types from './mutation-types'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    user: {},
    categoryList: [
      "前端",
      "后台",
      "全栈",
      "运维",
      "区块链",
      "人工智能",
      "其他分类"
    ]
  },
  mutations: {
    [types.SET_USER](state, user) {
      state.user = user
    },
    [types.SET_CATEGORY_LIST](state, categoryList) {
      state.categoryList = categoryList
    }
  },
  actions: {
    setUserToken({ commit, state }) {
      getUserToken()
        .then(data => {
          console.log('获取token到的token:', data)
          const tokenUnit = 100000000
          const token = parseFloat(
            // @ts-ignore
            parseInt(data.dBalance) / tokenUnit
          ).toFixed(2)
          commit(
            'SET_USER',
            Object.assign({}, state.user, {
              token: token
            })
          )
        })
        .catch(err => {
          console.log('获取Token出错啦：', err)
        })
    }
  },

  getters: {
    user: state => state.user,
    isLogin: state => !!(state.user && (state.user as any).sId),
    categoryList: state => state.categoryList
  }
})
