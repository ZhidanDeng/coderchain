import Vue from 'vue'
import { filterFullTime, filterPartTime } from '@utils/index'

Vue.filter('decodeURIComponent', decodeURIComponent)

Vue.filter('filterSize', (size: number) => {
  // B
  if (size < 1024) {
    return size + ' B'
  }

  // K
  if (size < 1024 * 1024) {
    return (size / 1024).toFixed(2) + ' KB'
  }

  // M
  return (size / 1024 / 1024).toFixed(2) + ' MB'
})

Vue.filter('filterSex', (sex: any) => {
  const SEX_MAP: any = {
    '1': '男',
    '0': '女',
    '-1': '性别未知'
  }

  return SEX_MAP[sex]
})

Vue.filter('filterFullTime', filterFullTime)

Vue.filter('filterPartTime', filterPartTime)
