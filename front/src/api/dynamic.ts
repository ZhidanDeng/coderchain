import fetch from '@utils/request'

export const getLatestUser = (): Promise<any> => {
  return fetch({
    method: 'GET',
    url: '/dynamic/Dynamic/getLatestUser'
  })
}

export const getLatestProject = (): Promise<any> => {
  return fetch({
    method: 'GET',
    url: '/dynamic/Dynamic/getLatest'
  })
}

export const getLatest = (): Promise<any> => {
  return fetch({
    method: 'GET',
    url: '/dynamic/Dynamic/getLatest'
  })
}
