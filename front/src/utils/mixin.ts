import { Component, Vue } from 'vue-property-decorator'
import { ProjectInterface } from './interface'
import { getChainInfo } from '@api/project'
import { openLoading, closeLoading } from '@utils/share-variable'

const toLower = (text: string): string => {
  return text.toString().toLowerCase()
}

const searchByName = (items: object[], term: string) => {
  if (term) {
    return items.filter(
      (item: any): boolean =>
        toLower(decodeURIComponent(item.name)).includes(toLower(term))
    )
  }

  return items.map(item => item)
}

// 关于文件
@Component
export class ProjectMixin extends Vue {
  searchList: object[] = []
  resList: object[] = []
  search: string = ''
  showDialog: boolean = false
  chainInfo: object = {}

  created() {
    this.searchList = this.resList
  }

  handleClickWatchChainInfo(project: ProjectInterface) {
    this.chainInfo = {
      name: decodeURIComponent(project.name),
      hash: project.Hash,
      cumulativeSize: project.CumulativeSize,
      blocks: project.Blocks
    }
    console.log(this.chainInfo);
    this.showDialog = true
  }
  handleClickWatchChainInfoAtTime(project: ProjectInterface) {
    openLoading('正在获取项目链上信息')
    getChainInfo(project.sUserId, project.sProjectName).then(data => {
      this.chainInfo = {
        name: decodeURIComponent(project.sProjectName),
        hash: data.Hash,
        cumulativeSize: data.CumulativeSize,
        blocks: data.Blocks
      }
      closeLoading()
    })
    .catch(err => {
      console.log('出错啦：', err)
      this.$alert(err)
      closeLoading()
    })
    this.showDialog = true
  }

  handleSearchOnTable() {
    this.searchList = []
    this.searchList = searchByName(this.resList, this.search)
  }
}
