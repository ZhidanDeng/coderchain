import Vue from 'vue'
import Component from './Snackbar.vue'

const SnackbarConstructor = Vue.extend(Component)

const snackbar = (options: any) => {
  if (Vue.prototype.$isServer) {
    console.log('> Env is server')
    return
  }
  if (typeof options === 'string') {
    options = {
      content: options
    }
  }
  
  const instance: any = new SnackbarConstructor({
    propsData: options
  })

  instance.vm = instance.$mount()
  document.body.appendChild(instance.vm.$el)

  return instance.vm
}

export default snackbar
