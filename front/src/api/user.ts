import fetch from '@utils/request'

export const login = (userName: string, password: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/login',
    data: {
      sUserName: userName,
      sPwd: password
    }
  })
}

export const register = (userName: string, password: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/register',
    data: {
      sUserName: userName,
      sPwd: password
    }
  })
}

export const getUserPriKey = (userName: string, password: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getUserPriKey',
    data: {
      sUserName: userName,
      sPassword: password
    }
  })
}

export const logout = (): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/logout'
  })
}

export const getUserInfo = (): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getInfo'
  })
}

export const loginByToken = (token: any): Promise<any> => {
  return fetch({
    method: 'get',
    url: '/user/User/openLoginByToken?sToken=' + token
  })
}


export const getSession = (): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getSessionId'
  })
}

export const getUserToken = (): Promise<any> => {
  return fetch({
    method: 'get',
    url: '/user/User/getToken',
    timeout: 600000 // ten minute
  })
}

export const getLoginState = (): Promise<any> => {
  // @ts-ignore
  return fetch({
    method: 'get',
    url: '/user/User/getLoginState',
    timeout: 600000 // ten minute
  })
}

export const getUserInfoByName = (userName: string): Promise<any> => {
  return fetch({
    method: 'get',
    url:
      '/user/User/getInfoByName?sUserName=' + userName
  })
}

export const updateUserInfo = (userInfo: any): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/update',
    data: userInfo
  })
}

export const updateUserPassword = (userInfo: any): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/updatePassword',
    data: userInfo
  })
}

export const uploadFile = (formData: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/uploadFile',
    data: formData,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function (data) {
        return data
      }
    ]
  })
}

// export const addFeedback = (userName: string, contact: string, content: string): Promise<any> => {
//   return fetch({
//     method: 'post',
//     url: '/user/User/addFeedback',
//     data: {
//       sUserName: userName,
//       sContact: contact,
//       sContent: content
//     }
//   })
// }
export const addFeedback = (scontent: string, spassword: string, saddress: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/addFeedback',
    data: {
      content: scontent,
      password: spassword,
      saddress: saddress
    }
  })
}

export const getFeedbackList = (): Promise<any> => {
  // @ts-ignore
  // @todo 暂时找不到改写AxiosRequestConfig的接口，要自定义retry属性
  return fetch({
    method: 'get',
    url: '/user/User/getFeedbackList',
    retry: 5
  })
}

export const getAllUser = (): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getAllUser'
  })
}

export const transferByName = (transferInfo: any): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/transfer',
    data: transferInfo
  })
}

export const getTransferList = (): Promise<any> => {
  return fetch({
    method: 'get',
    url: '/user/User/getTransferList'
  })
}

export const getTransferDetail = (tx: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getTransferDetail',
    data: {
      hash: tx
    }
  })
}

export const getUserTxDetail = (tx: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getUserTxDetail',
    data: {
      hash: tx
    }
  })
}
export const getUserTx = (user: string, txType: number): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/user/User/getUserTx',
    data: {
      userName: user,
      type: txType
    }
  })
}
