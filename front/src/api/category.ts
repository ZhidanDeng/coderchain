import fetch from '@utils/request'

export const getCategories = (): Promise<any> => {
  return fetch({
    method: 'GET',
    url: '/category/Category/getAllCategory'
  })
}
