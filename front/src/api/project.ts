import fetch from '@utils/request'
import {IPFS_URL} from '@config/url'

export const getProjectDir = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getDir',
    data: {
      sUserName: userName,
      sProjectName: projectName
    }
  })
}
export const pullProject = (data: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/api/Api/createAndSynchronizeRepo',
    data: data,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function(data) {
        return data
      }
    ]
  })
}

export const synchronizeRepo = (data: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/api/Api/synchronizeRepo',
    data: data,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function(data) {
        return data
      }
    ]
  })
}
export const generateDocumentByCoderChain = (data: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/api/Api/generateDocumentByCoderChain',
    data: data,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function(data) {
        return data
      }
    ]
  })
}
// export const download = (
//   spath: string
// ): Promise<any> => {
//   return fetch({
//     method: 'get',
//     url: '/api/Api/generateDocument',
//     data: {
//       path: download
//     }
//   })
// }

export const generateDocument = (data: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/api/Api/generateDocument',
    data: data,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function(data) {
        return data
      }
    ]
  })
}


export const getAllProject = (): Promise<any> => {
  // @ts-ignore
  // @todo 暂时找不到改写AxiosRequestConfig的接口，要自定义retry属性
  return fetch({
    method: 'get',
    url: '/project/Project/getAllProject',
    retry: 5
  })
}

export const getProjectRank = (): Promise<any> => {
  // @ts-ignore
  // @todo 暂时找不到改写AxiosRequestConfig的接口，要自定义retry属性
  return fetch({
    method: 'get',
    url: '/project/Project/getAllRank',
    retry: 5
  })
}

export const getProjectDetail = (
  userName: string,
  projectName: string,
  path: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getProjectDetail',
    data: {
      sUserName: userName,
      sProjectName: projectName,
      sPath: path
    }
  })
}

export const getFileContent = (hash: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/file/File/getContent',
    data: {
      sHash: hash
    }
  })
}

export const createNewDir = (
  projectName: string,
  path: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/createDir',
    data: {
      sProjectName: projectName,
      sPath: path
    }
  })
}

export const saveFile = (
  projectName: string,
  path: string,
  content: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/file/File/updateContent',
    data: {
      sProjectName: projectName,
      sPath: path,
      sData: content
    }
  })
}

export const uploadFile = (formData: any): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/file/File/upload',
    data: formData,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      function(data) {
        return data
      }
    ]
  })
}

export const deleteFile = (projectName: string, path: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/file/File/delete',
    data: {
      sProjectName: projectName,
      sPath: path
    }
  })
}

export const createProject = (sdata: FormData): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/create',
    data: sdata,
    // formData格式的数据不转换成表单格式
    transformRequest: [
      // tslint:disable-next-line: only-arrow-functions
      function(rdata) {
        return rdata
      }
    ]
  })
}

export const getReportList = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/detect/Detect/getAllDetectReport',
    data: {
      sUserName: userName,
      sProjectName: projectName
    }
  })
}

export const addDetectTask = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/detect/Detect/addDetectByName',
    data: {
      sUserName: userName,
      sProjectName: projectName
    },
    timeout: 600000 // ten minute
  })
}

export const getDetectStatus = (detectTaskId: string): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/detect/Detect/detectStatus',
    data: {
      sDetectTaskId: detectTaskId
    }
  })
}

export const getProjectSupportCount = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getSupportCountByName',
    data: {
      sUserName: userName,
      sProjectName: projectName
    }
  })
}

export const voteProject = (
  userName: string,
  projectName: string,
  count: number,
  spassword: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/voteByName',
    data: {
      sUserName: userName,
      sProjectName: projectName,
      iSupportCount: count,
      password: spassword
    },
    timeout: 600000 // ten minute
  })
}
export const getSupportDetailList = (): Promise<any> => {
  return fetch({
    method: 'get',
    url: '/project/Project/getSupportDetailList'
  })
}

export const getSupportDetail = (
  tx: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getSupportDetail',
    data: {
      hash: tx
    }
  })
}

export const getUserProjects = (userName: string): Promise<any> => {
  return fetch({
    method: 'get',
    url:
      '/project/Project/get?sUserName=' + userName
  })
}

export const getProjectInfo = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getProjectInfo',
    data: {
      sUserName: userName,
      sProjectName: projectName
    }
  })
}

export const updateProjectInfo = (
  projectName: string,
  categoryName: string,
  description: string,
  spassword: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/updateProjectInfo',
    data: {
      sProjectName: projectName,
      sCategoryName: categoryName,
      sDescription: description,
      password: spassword
    }
  })
}

export const downloadProject = (
  userName: string,
  projectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/download',
    data: {
      sUserName: userName,
      sProjectName: projectName
    },
    responseType: 'arraybuffer'
  })
}

export const deleteProject = (
  projectName: string,
  spassword: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/delete',
    data: {
      sProjectName: projectName,
      password: spassword
    }
  })
}

export const getChainInfo = (
  saddress: string,
  sprojectName: string
): Promise<any> => {
  return fetch({
    method: 'post',
    url: '/project/Project/getChainInfo',
    data: {
      address: saddress,
      projectName: sprojectName
    }
  })
}
