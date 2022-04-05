// 根据填充字符在原字符开头补齐目标字符到理想长度
const padding = (
  str: number | string,
  length: number = 2,
  fillChar: string = '0'
): string => {
  str = str.toString()
  let strLength = str.length

  if (strLength >= 2) {
    return str
  }

  return fillChar.repeat(length - strLength) + str
}

// 解析时间格式
const filterFullTime = (
  time: string | number | Date,
  isPHP: boolean = true
): string => {
  if (!time || time < 0) {
    return 'UNKNOWN'
  }

  if (isPHP && typeof time === 'string') {
    time = parseInt(time) * 1000
  }

  const date = new Date(time)
  let year = date.getFullYear()
  let month = padding(date.getMonth() + 1)
  let day = padding(date.getDate())
  let hour = padding(date.getHours())
  let minute = padding(date.getMinutes())
  let second = padding(date.getSeconds())

  // return year + ' 年 ' + month + ' 月 ' + day + ' 日 ' + hour + ' : ' + minute + ' : ' + second
  return `${year}-${month}-${day} ${hour}:${minute}:${second}`
}

const filterPartTime = (
  time: string | number | Date,
  isPHP: boolean = true
): string => {
  if (!time || time < 0) {
    return 'UNKNOWN'
  }

  if (isPHP && typeof time === 'string') {
    time = parseInt(time) * 1000
  }
  
  const date = new Date(time)
  
  let year = date.getFullYear()
  let month = padding(date.getMonth() + 1)
  let day = padding(date.getDate())
  // let hour = padding(date.getHours())
  // let minute = padding(date.getMinutes())
  // let second = padding(date.getSeconds())
  
  // return year + ' 年 ' + month + ' 月 ' + day + ' 日 ' + hour + ' : ' + minute + ' : ' + second
  return `${year}-${month}-${day}`
}

// 获取文件的后缀
const getFileExtension = (filename: string): string => {
  const arr = filename.split('.')
  if (arr.length > 1) {
    return arr[arr.length - 1].toLowerCase()
  } else {
    return ''
  }
}

// 资源类型
const isResourceType = (ext: string) => {
  const RESOURCE_EXT_ARR = [
    'jpg',
    'jpeg',
    'gif',
    'png',
    'ico',
    'pdf',
    'doc',
    'docx',
    'woff',
    // resource
    'ppt',
    'pptx',
    'xlsx',
    'xls',
    'mp4',
    'flv',
    'mp3'
  ]
  return RESOURCE_EXT_ARR.indexOf(ext.toLowerCase()) > -1
}

const isWebpageType = (ext: string) => {
  const RESOURCE_EXT_ARR = [
    'html',
    'htm'
  ]
  return RESOURCE_EXT_ARR.indexOf(ext.toLowerCase()) > -1
}

// office类型
const isOfficeType = (ext: string) => {
  const RESOURCE_EXT_ARR = [
    'doc',
    'docx',
    'ppt',
    'pptx',
    'xlsx',
    'xls'
  ]
  return RESOURCE_EXT_ARR.indexOf(ext.toLowerCase()) > -1
}
// 不支持上传的文件类型
const isUnSupportedType = (ext: string) => {
  const UNSUPPORTED_EXT_ARR = [
    // 'xlsx',
    // 'ppt',
    // 'xls',
    // 'pptx',
    'war',
    'exe',
    'wav',
    'crx'
    // 'mp3'
  ]
  return UNSUPPORTED_EXT_ARR.indexOf(ext.toLowerCase()) > -1
}

// 是不是压缩包
const isPackageType = (ext: string) => {
  const PACKAGE_ARR = ['gz', 'tar', 'rar', 'zip']
  return PACKAGE_ARR.indexOf(ext.toLowerCase()) > -1
}

// 过滤字符两边空格
const trim = (str: any) => {
  if (str == null) {
    return ''
  }

  str = str.toString()
  return str.replace(/^[\s]+|[\s]+$/g, '')
}

// 不支持的压缩包类型
const isUnSupportedPackageType = (ext: string) => {
  const PACKAGE_ARR = ['gz', 'tar']
  return PACKAGE_ARR.indexOf(ext.toLowerCase()) > -1
}

// 检测字符串是否包含空格
const containsBlank = (str: string) => /(\s)/g.test(str)

// 判断是否是合法的项目名
const isValidFileName = (str: string) => {
  if (str == null) {
    return false
  }
  // 非法字符 & ` = * / \ ? " < > |
  const reg = /[&`=*/\\?"<>|]/
  return !reg.test(str)
}

const isValidPwd = (str: string) => {
  if (str == null) {
    return false
  }
  const reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,20}$/
  return reg.test(str)
}

// 判断是否是合法的手机号
const isPhone = function(phone: string) {
  const reg = /^0?1[3|4|5|6|7|8][0-9]\d{8}$/
  return reg.test(phone)
}

// 判断是否是合法的身份证号
const isID = function(sId: string) {
  var aCity = {
    11: '北京',
    12: '天津',
    13: '河北',
    14: '山西',
    15: '内蒙古',
    21: '辽宁',
    22: '吉林',
    23: '黑龙江',
    31: '上海',
    32: '江苏',
    33: '浙江',
    34: '安徽',
    35: '福建',
    36: '江西',
    37: '山东',
    41: '河南',
    42: '湖北',
    43: '湖南',
    44: '广东',
    45: '广西',
    46: '海南',
    50: '重庆',
    51: '四川',
    52: '贵州',
    53: '云南',
    54: '西藏',
    61: '陕西',
    62: '甘肃',
    63: '青海',
    64: '宁夏',
    65: '新疆',
    71: '台湾',
    81: '香港',
    82: '澳门',
    91: '国外'
  }
  var iSum = 0
  var info = ''
  var sBirthday = ''
  if (!/^\d{17}(\d|x)$/i.test(sId)) return '你输入的身份证长度或格式错误'
  sId = sId.replace(/x$/i, 'a')
  // @ts-ignore
  if (aCity[parseInt(sId.substr(0, 2))] == null) return '你的身份证地区非法'
  sBirthday =
    sId.substr(6, 4) +
    '-' +
    Number(sId.substr(10, 2)) +
    '-' +
    Number(sId.substr(12, 2))
  var d = new Date(sBirthday.replace(/-/g, '/'))
  if (
    sBirthday !=
    d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate()
  )
    return '身份证上的出生日期非法'
  for (var i = 17; i >= 0; i--)
    iSum += (Math.pow(2, i) % 11) * parseInt(sId.charAt(17 - i), 11)
  if (iSum % 11 != 1) return '你输入的身份证号非法'
  //aCity[parseInt(sId.substr(0,2))]+","+sBirthday+","+(sId.substr(16,1)%2?"男":"女");//此次还可以判断出输入的身份证号的人性别
  return true
}

// 判断是否是图片后缀
const isImage = function(ext: string) {
  var IMG_EXT_ARR = ['jpg', 'jpeg', 'gif', 'png', 'ico']
  return IMG_EXT_ARR.indexOf(ext.toLowerCase()) > -1
}

// 判断是否是邮箱
const isEmail = function(email: string) {
  var re = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
  return re.test(email)
}

export {
  filterFullTime,
  filterPartTime,
  getFileExtension,
  isResourceType,
  isOfficeType,
  trim,
  isUnSupportedType,
  isPackageType,
  isUnSupportedPackageType,
  isWebpageType,
  containsBlank,
  isValidFileName,
  isPhone,
  isID,
  isImage,
  isEmail,
  isValidPwd
}
