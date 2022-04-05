import axios from 'axios'
import $alert from '@/components/alert/function'
import bus from '@utils/bus'
import {API_URL} from '@config/url'
const baseURL = API_URL
// const isDev = process.env.NODE_ENV === 'development'
// console.log('测试环境', isDev, process.env.NODE_ENV)

axios.defaults.headers['Content-Type'] = 'application/x-www-form-urlencoded'
axios.defaults.transformRequest = [
  function(data) {
    let ret = ''
    for (let it in data) {
      ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&'
    }
    return ret
  }
]

// 1 minute
// axios.defaults.timeout = 60000
// axios.defaults.baseURL = 'http://test.bc.wischain.net/'
// axios.defaults.baseURL = 'https://bc.wischain.net/'
// 开启跨域保存session
// axios.defaults.withCredentials = true

const service = axios.create({
  baseURL: baseURL,
  // timeout: 60000,
  timeout: 100000,
  withCredentials: true
})

service.interceptors.request.use(
  config => {
    return config
  },
  err => {
    console.log('请求出错啦：', err)
    return Promise.reject(err)
  }
)

service.interceptors.response.use(
  response => {
    console.log('response success: ', response)
    if (response.status === 200 || response.status === 304) {
      const data = response['data']
      const code = parseInt(data['retCode'])

      // 未登录,暂时不拦截
      // if (code === -10) {
      //   // 触发事件，传递给App组件处理
      //   bus.$emit('unLogin')
      //   return new Promise(() => {})
      // }

      if (code !== 0) {
        return Promise.reject(data['retMsg'])
      }

      return response['data']['oRet']
    } else {
      console.log('状态码不符合：', response)
      console.log('状态码：', response.status)
      return Promise.reject('请求出错啦')
    }
  },
  err => {
    console.log('response 进入error: ', err)
    if (err.response) {
      console.log('响应出错啦, err.response：', err.response)
    } else if (err.request) {
      console.log('响应出错啦, err.request', err.request)
      console.log('响应出错啦, err.request', err.request.code)
    } else {
      console.log('响应出错啦, err.message', err.message)
    }

    const msg = err.message

    if (msg === 'Network Error') {
      // 网络错误
      const NETWORK_ERROR = '您的网络好像出现问题啦，请稍后再试'
      console.log(NETWORK_ERROR)
      $alert({
        content: NETWORK_ERROR
      })
      // 直接中断Promise链
      return new Promise(() => {})
    }

    // 这里可以设置超时重发等等
    return Promise.reject('响应超时啦，请稍后再试')

    // https://github.com/axios/axios/issues/164
    var config = err.config
    // If config does not exist or the retry option is not set, reject
    if (!config || !config.retry) return Promise.reject(err)

    // Set the variable for keeping track of the retry count
    config.__retryCount = config.__retryCount || 0

    // Check if we've maxed out the total number of retries
    if (config.__retryCount >= config.retry) {
      // Reject with the error
      return Promise.reject(err)
    }

    // Increase the retry count
    config.__retryCount += 1

    // Create new promise to handle exponential backoff
    var backoff = new Promise(function(resolve) {
      setTimeout(function() {
        resolve()
      }, config.retryDelay || 1)
    })

    // Return the promise in which recalls axios to retry the request
    return backoff.then(function() {
      console.log('重新发起请求>>>')
      return service(config)
    })
  }
)

export default service
