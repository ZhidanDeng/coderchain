import confirm from './function'

export default (Vue: any) => {
  Vue.prototype.$confirm = confirm
}