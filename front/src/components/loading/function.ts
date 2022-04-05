import Vue from 'vue'
import Component from './FullLoading.vue'

const FulllLoadingConstructor = Vue.extend(Component)

const fullLoading = (options: any) => {
  if (Vue.prototype.$isServer) {
    console.log('> Env is server')
    return
  }
  if (typeof options === 'string') {
    options = {
      content: options
    }
  }

  const instance: any = new FulllLoadingConstructor({
    propsData: options
  })

  instance.vm = instance.$mount()
  document.body.appendChild(instance.vm.$el)

  instance.vm.$on('remove', () => {
    console.log('收到移除事件：')
    try {
      // instance.vm.$el这里有可能是注释元素
      // <!---->
      // console.log('元素：', instance.vm.$el)
      document.body.removeChild(instance.vm.$el)
    } catch (err) {
      // console.log('loading remove出错：', err)
    }
  })

  return instance.vm
}

export default fullLoading
