import Vue from 'vue'
import ConfirmComponent from './Confirm.vue'

const ConfirmConstructor = Vue.extend(ConfirmComponent)

const confirm = (options: any) => {
  if (Vue.prototype.$isServer) {
    console.log('> Env is server')
    return
  }
  if (typeof options === 'string') {
    options = {
      content: options
    }
  }
  
  const instance: any = new ConfirmConstructor({
    propsData: options
  })

  instance.vm = instance.$mount()
  document.body.appendChild(instance.vm.$el)

  return instance.vm
}

export default confirm
