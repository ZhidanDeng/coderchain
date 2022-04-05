import Vue from 'vue'
import AlertComponent from './Alert.vue'

const AlertConstructor = Vue.extend(AlertComponent)

const alert = (options: any) => {
  if (Vue.prototype.$isServer) {
    console.log('> Env is server')
    return
  }

  if (typeof options === 'string') {
    options = {
      content: options
    }
  }
  const instance: any = new AlertConstructor({
    propsData: options
  })

  instance.vm = instance.$mount()
  document.body.appendChild(instance.vm.$el)

  return instance.vm
}

export default alert
