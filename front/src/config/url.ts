// @ts-ignore
import isDev from '@utils/isDev'
const baseApiUrl: string = 'http://coderchain.cn/back/index.php'
// const testBaseApiUrl: string = 'https://coderchain.cn'
const testBaseApiUrl: string = ''
// const baseApiUrl: string = 'https://api.coderchain.cn'
// const testBaseApiUrl: string = 'https://api.coderchain.cn'
export const IPFS_URL: string = isDev ? 'http://119.91.150.124:8080/ipfs' : 'http://119.91.150.124:8080/ipfs'
export const API_URL: string = isDev ? testBaseApiUrl : baseApiUrl
export const BASE_URL: string = baseApiUrl
export const STATIC_URL: string = isDev ? 'http://119.91.150.124' : 'http://119.91.150.124'
