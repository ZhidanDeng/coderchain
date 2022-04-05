import $fullLoading from '@/components/loading/function'
import $snackbar from '@/components/snackbar/function'

// 全局页面loading
export let $loadingInstance: any = null
// 底部信息提示
export let $barInstance: any = null

export const closeLoading = (): void => {
  try {
    $loadingInstance != null && $loadingInstance.close() && ($loadingInstance = null)
  } catch (err) {
    console.log('closeLoading异常：', err)
  }
}

export const openLoading = (str: string): void => {
  closeLoading()
  $loadingInstance = $fullLoading(str)
}

export const closeBar = (): void => {
  try {
    $barInstance != null && $barInstance.close() && ($barInstance = null)
  } catch (err) {
    console.log('closeBar异常：', err)
  }
}

export const openBar = (str: string): void => {
  $barInstance = $snackbar(str)
}
