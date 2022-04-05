<template>
  <div>
    <div v-wechat-title="`${projectName} - CoderChain`">
      <md-toolbar class="md-transparent new"
                  md-elevation="0">
        <h3 class="md-title">
          <a v-if="!isRoot" @click.stop="onPrevDirectory"
             class="back-link md-primary">
            <md-icon class="fa fa-angle-left"></md-icon>
            <md-tooltip md-direction="top">上一个目录</md-tooltip>
          </a>
          <!-- <a href="http://121.201.35.141:18080/ipfs/QmNuisR95UkkzqdaxpVnYDhYyrEVsgQ58D4cLjtfCV1YpP" download="666.docx">下载</a> -->
          <span>导航：/&nbsp;</span>
          <template v-if="breadList.length">
            <span v-for="(item, index) in breadList"
                  :key="index">
              <span v-if="index === breadList.length - 1">{{ item }}</span>
              <a @click.prevent="handleClickJumpDir(index)"
                 class="bread-item"
                 v-else>{{ item }}</a> /
            </span>
          </template>

        </h3>
        <div class="text-right"
             v-show="!showFileContent">
          <span>
            <!-- /dev/controller/exProjectController.php?action=download -->

            <md-button class="md-primary"
                       :href="`${baseURL}/project/Project/download?sProjectName=${encodeURIComponent(projectName)}&sUserName=${userName}`">
              <md-icon class="fa fa-cloud-download"></md-icon>
            </md-button>
            <md-tooltip md-direction="top">下载项目</md-tooltip>
          </span>
          <template v-if="isOwner">
            <span>
              <md-button class="md-primary"
                         @click="onCreateDirectory">
                <md-icon class="fa fa-plus"></md-icon>
                <md-icon class="fa fa-folder"></md-icon>
              </md-button>
              <md-tooltip md-direction="top">创建目录</md-tooltip>
            </span>
            <span>
              <md-button class="md-primary"
                         @click="onCreateFile">
                <md-icon class="fa fa-plus"></md-icon>
                <md-icon class="fa fa-file-o"></md-icon>
              </md-button>
              <md-tooltip md-direction="top">创建文件</md-tooltip>
            </span>
            <span>
              <md-button class="md-primary"
                         @click="onUploadFile">
                <md-icon class="fa fa-upload"></md-icon>
              </md-button>
              <md-tooltip md-direction="top">上传文件</md-tooltip>
              <md-field class="hidden">
                <md-file placeholder="选择初始化项目的文件（限选一个）"
                         @md-change="onFileChange"
                         id="file" />
              </md-field>
            </span>
          </template>
        </div>
      </md-toolbar>

      <!-- 文件暂时不排序
                md-sort="name"
                md-sort-order="asc"
       -->
      <md-table v-model="searchList"
                @md-selected="onSelect"
                v-show="!showFileContent">
        <md-table-toolbar>
          <md-field md-clearable
                    class="md-toolbar-section-end">
            <md-input placeholder="搜索文件名称..."
                      v-model="search"
                      @input="handleSearchOnTable" />
          </md-field>
        </md-table-toolbar>

        <div v-show="resList.length && !searchList.length">
          <md-empty-state md-label="找不到文件"
                          :md-description="`当前目录找不到关键字为'${search}'的文件或目录`">
            <md-button class="md-primary md-raised"
                       @click="search = ''">撤销查找</md-button>
          </md-empty-state>
        </div>

        <div v-show="!resList.length">
          <md-empty-state md-label="目录空空如也"
                          :md-description="`看看其他目录吧~`">
            <md-button class="md-primary md-raised"
                       @click="onPrevDirectory">返回上一级</md-button>
          </md-empty-state>
        </div>

        <md-table-row slot="md-table-row"
                      slot-scope="{ item }"
                      md-selectable="single">
          <md-table-cell md-label="文件"
                         md-sort-by="name">
            <md-icon :class="['fa', item.type === 'file' ? 'fa-file-o' : 'fa-folder', 'file-icon']"></md-icon>
            {{ item.name }}
          </md-table-cell>
          <md-table-cell md-label="操作">
            <md-button @click.stop="handleClickWatchChainInfo(item)">查看链上信息</md-button>
            <span v-if="isOwner">
              <md-button @click.stop="onDelete(item)"
                         class=" md-accent">
                <md-icon class="fa fa-trash"></md-icon>
              </md-button>
              <md-tooltip md-direction="top">删除</md-tooltip>
            </span>

          </md-table-cell>
        </md-table-row>
      </md-table>
      <div v-show="showFileContent">
        <div class="language">
          <md-field>
            <label for="lang">语法识别：</label>
            <md-select v-model="language"
                       id="lang"
                       @md-selected="onSelectLanguage">
              <md-option value="-1">未匹配</md-option>
              <md-option :value="key"
                         v-for="(value, key) in languageMap"
                         :key="key">{{ value.language }}</md-option>
            </md-select>
          </md-field>
        </div>
        <md-card class="no-shadow">
          <md-ripple>
            <md-card-header>
              <div class="md-title"
                   v-show="!isCreatingNewFile">{{ this.selected.name }} </div>
                   <md-button @click="onPreviewHTML()"
                         class="md-raised md-primary" v-if="!isCreatingNewFile && isHTMLFile">
                预览网页文件
              </md-button>
              <md-button @click="onCloseFile"  v-show="!isCreatingNewFile" class="md-raised">关闭文件</md-button>
              <div class="md-title"
                   v-show="isCreatingNewFile">
                <md-field>
                  <label>文件名称</label>
                  <md-input v-model="newFile.name"
                            id="newFileNameInput"
                            ref="newFileNameInput"></md-input>
                </md-field>
              </div>
              <div class="md-subhead">

              </div>
              <div class="align-right"
                   v-if="!isCreatingNewFile">
                <!-- <md-button @click="onCloseFile">关闭</md-button> -->
                <!-- <span>
                  <md-button @click="onDownload"
                             class="md-primary">
                    <md-icon class="fa fa-download"></md-icon>
                  </md-button>
                  <md-tooltip md-direction="top">下载</md-tooltip>
                </span> -->
                <template v-if="isOwner">

                  <span>
                    <md-button @click="onEdit"
                               class="md-primary">
                      <md-icon class="fa fa-edit"></md-icon>
                    </md-button>
                    <md-tooltip md-direction="top">编辑</md-tooltip>
                  </span>
                  <span>
                    <md-button @click="onDelete()"
                               class="md-accent">
                      <md-icon class="fa fa-trash"></md-icon>
                    </md-button>
                    <md-tooltip md-direction="top">删除</md-tooltip>
                  </span>
                </template>
              </div>
            </md-card-header>

            <md-card-content>



              <!-- markdown渲染 -->

              <codemirror v-model="editorFileContent"
                          :options="cmOptions"
                          class="code-editor"
                          ref="editor"></codemirror>

              <div v-if="isEdit">
                <md-button @click="onSave"
                           class="md-raised md-primary">保存</md-button>
                <md-button @click="onReset"
                           class="md-raised">取消</md-button>
              </div>
              <!-- <pre><code>{{ fileContent }}</code> -->
              <!-- </pre> -->
            </md-card-content>

            <md-card-actions v-show="!isCreatingNewFile">
              <md-button @click="onCloseFile" class="md-raised">关闭文件</md-button>
            </md-card-actions>
          </md-ripple>
        </md-card>
      </div>
    </div>

    <md-dialog :md-active.sync="showDialog"
               class="bg-white">
      <md-dialog-title>区块信息</md-dialog-title>
      <md-dialog-content>
        <md-list>
          <md-list-item>项目：{{ chainInfo.name }}</md-list-item>
          <md-list-item>哈希：{{ chainInfo.hash}}</md-list-item>
          <md-list-item>大小：{{ chainInfo.cumulativeSize | filterSize}}</md-list-item>
          <md-list-item>占用区块：{{ chainInfo.blocks }}</md-list-item>
        </md-list>
      </md-dialog-content>
      <md-dialog-actions>
        <md-button class="md-primary"
                   @click="showDialog = false">Close</md-button>
      </md-dialog-actions>
    </md-dialog>

    <!-- 创建新文件夹名称 -->
    <md-dialog-prompt :md-active.sync="isCreatingNewDir"
                      v-model="newDirName"
                      md-title="目录名称?"
                      md-input-placeholder="Type your directory..."
                      md-cancel-text="取消"
                      md-confirm-text="创建"
                      @md-confirm="onConfirmCreateNewDir"
                      @md-cancel="onCancelCreateNewDir" />

  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Mixins } from 'vue-property-decorator'

import { ProjectMixin } from '@utils/mixin'
import { FileInterface } from '@utils/interface'
import {
  getFileExtension,
  isResourceType,
  trim,
  isUnSupportedType,
  isPackageType,
  isOfficeType,
  isWebpageType
} from '@utils/index'
import {
  getProjectDetail,
  getFileContent,
  createNewDir,
  saveFile,
  deleteFile,
  uploadFile,
  downloadProject
} from '@api/project'
import { BASIC_CONFIG, LANGUAGE_MAP } from '@config/codemirror'
import { Getter } from 'vuex-class'
import {
  openLoading,
  openBar,
  closeLoading,
  closeBar
} from '@utils/share-variable'

import { codemirror } from 'vue-codemirror'
import baseURL from '@utils/api-url'
import {IPFS_URL} from '@config/url'
import {Watch} from 'vue-property-decorator'

// 导入codermirror初始化文件
require('@config/codemirror-require')

@Component({
  components: {
    codemirror
  }
})
export default class DetailCode extends Mixins(ProjectMixin) {
  @Prop() readonly userName!: string
  @Prop() readonly projectName!: string
  @Prop() readonly isOwner!: boolean
  baseURL: string = baseURL
  // 文件列表
  resList: Array<FileInterface> = []
  // 搜索结果列表
  searchList: Array<FileInterface> = []
  // 选择的是文件或者目录
  selected: any = {}
  // 面包屑导航
  breadList: Array<string> = []
  // 是否是根目录
  isRoot : boolean = true
  // 文件内容
  fileContent: string = ''
  // 编辑器显示的内容
  editorFileContent: string = ''
  // 修改的文件或者新创建的文件
  newFile: any = {
    name: '',
    content: ''
  }
  // 新的文件夹名称
  newDirName: string = ''
  // 识别语言
  language: string = 'js'
  cmOptions: any = Object.assign({}, BASIC_CONFIG, {
    // more codemirror options, 更多 codemirror 的高级配置...
    readOnly: true
  })
  languageMap: object = LANGUAGE_MAP

  // 控制面板的一些布尔值
  showFileContent: boolean = false
  isEdit: boolean = false
  isCreatingNewFile: boolean = false
  isCreatingNewDir: boolean = false
  isHTMLFile: boolean = false

  get $codemirror(): HTMLElement {
    return (this.$refs.editor as any).codemirror
  }

  created(): void {
    // 获取文件内容
    this.fetchProjectDetail()
  }

  // 发起页面请求，获取项目详情
  onSelect(item: FileInterface): void {
    console.log('选择了：', item)
    if (!item) {
      return
    }
    // 判断文件类型
    this.selected = item
    const isDir = item.Type === 'directory'
    console.log(isDir);

    if (isDir) {
      this.jumpDir(item)
    } else {
      this.openFile(item)
    }
  }

  onSelectLanguage(lang: string): void {
    // let theme = this.cmOptions.theme
    let langItem = LANGUAGE_MAP[lang]
    if (!langItem) {
      // 语言暂不支持，默认用javascript
      console.log('不支持的语言：', lang)
      langItem = LANGUAGE_MAP['js']
      return
    }

    const language = langItem.language

    console.log('选择了语言：', lang)
    console.log('语言的配置：', langItem)
    if (langItem.options.theme) {
      console.log('主题存在')
      require(`codemirror/theme/${langItem.options.theme}.css`)
    }

    require(`codemirror/mode/${language}/${language}.js`)
    this.cmOptions = Object.assign({}, this.cmOptions, langItem.options)
  }

  onEdit(): void {
    this.isEdit = true
    this.cmOptions = Object.assign({}, this.cmOptions, {
      readOnly: false
    })
    this.$codemirror.focus()
  }
  onDownload(): void {
    console.log('下载中：', this.selected)
  }
  onDelete(item: FileInterface) {
    if (!item) {
      item = this.selected
    }

    const instance = this.$confirm({
      content: `确认删除${item.Type === 'directory' ? '目录' : '文件'}'${
        item.name
      }'吗`
    })

    instance.$on('confirm', () => {
      console.log('on confirm')
      console.log('删除中：', item.name)
      let path = ''
      if (!this.breadList.length) {
        path = item.name
      } else {
        path = this.breadList.join('/') + '/' + item.name
      }

      openLoading('正在删除中...')
      deleteFile(this.projectName, path)
        .then(() => {
          closeLoading()
          this.onCloseFile()
          this.fetchProjectDetail()
        })
        .catch(err => {
          closeLoading()
          this.$alert(err)
        })
    })

    instance.$on('cancel', () => {
      console.log('on cancel')
    })
  }

  onSave(): void {
    let path = ''
    let fileName = ''
    let fileContent = this.editorFileContent

    if (fileContent === '') {
      fileContent = ' '
    }

    // 保存新文件，校验
    if (this.isCreatingNewFile) {
      this.newFile.content = this.editorFileContent
      this.newFile.name = trim(this.newFile.name)

      if (this.newFile.name === '') {
        this.$alert('文件名称不能为空噢')
        return
      }

      fileName = this.newFile.name
    } else {
      fileName = this.selected.name
    }

    openLoading('正在保存文件...')

    if (this.breadList.length) {
      path = this.breadList.join('/') + '/' + fileName
    } else {
      path = fileName
    }

    // scrable data
    saveFile(this.projectName, path, fileContent)
      .then(data => {
        this.newFile = {
          name: '',
          content: ''
        }
        closeLoading()
        this.onCloseFile()
        this.fetchProjectDetail()
        this.editorFileContent = ''
        // this.fileContent = this.editorFileContent
      })
      .catch(err => {
        console.log('新建文件失败：', err)
        closeLoading()
        this.$alert(err)
      })
  }
  onReset() {
    if (this.isCreatingNewFile) {
      this.onCloseFile()
      return
    }
    console.log('撤销')
    this.isEdit = false
    this.disabledEditor()
    this.editorFileContent = this.fileContent
  }

  onCloseFile(): void {
    this.isCreatingNewFile = false
    this.showFileContent = false
    this.isEdit = false
    this.disabledEditor()
  }

  onDownloadProject(): void {
    console.log('下载项目中...')
    openLoading('正在下载中...')
    // 传递用户名和项目名称
    downloadProject(this.userName, this.projectName)
      .then(data => {
        console.log('下载成功：', data)
        closeLoading()
      })
      .catch(err => {
        this.$alert(err)
        closeLoading()
      })
  }

  onCreateDirectory(): void {
    console.log('创建目录中...')
    this.isCreatingNewDir = true
  }

  onCreateFile(): void {
    this.isCreatingNewFile = true
    this.showFileContent = true
    this.isEdit = true
    this.enabledEditor()
    this.editorFileContent = ''
    this.$nextTick(() => {
      const $input: any = this.$refs.newFileNameInput
      if ($input) {
        $input.$el.focus()
      }
    })
    console.log('创建文件中...')
  }

  onConfirmCreateNewDir(): void {
    console.log('创建新目录：', this.newDirName)
    this.newDirName = trim(this.newDirName)
    // 合法校验
    if (this.newDirName === '') {
      this.$nextTick(() => {
        this.isCreatingNewDir = true
      })
      openBar('目录名称不能为空')
      return
    }

    let path = ''
    if (this.breadList.length) {
      path = this.breadList.join('/') + '/' + this.newDirName
    } else {
      path = this.newDirName
    }

    closeBar()

    createNewDir(this.projectName, path)
      .then(data => {
        // 创建成功，重新拉取目录
        console.log('目录创建成功', data)
        this.newDirName = ''
        this.fetchProjectDetail()
      })
      .catch(err => {
        console.log('创建目录失败：', err)
        this.$alert(err)
      })
  }

  onCancelCreateNewDir(): void {
    console.log('取消新目录：', this.newDirName)
    closeBar()
  }

  onUploadFile(): void {
    const $file: any = document.querySelector('#file')
    $file.click()
  }

  onFileChange(fileList: any): void {
    console.log('文件变化：', fileList)
    if (!fileList.length) {
      console.log('没有选择文件')
      return
    }

    const $file = fileList[0]
    const ext = getFileExtension($file.name)
    

    // @todo 文件后缀合法校验
    if (isUnSupportedType(ext)) {
      this.$alert('暂不支持上传该类型的文件，后续会考虑支持')
      return
    }

    if($file.size>(30*1024*1024))
    {
      this.$alert('暂不支持上传大于30M的数据')
      return
    }

    if (isPackageType(ext)) {
      this.$alert('项目内不支持压缩包上传，如有需要，请新建项目')
      return
    }

    const formData = new FormData()
    let path = ''

    if (this.breadList.length) {
      path = this.breadList.join('/')
    } else {
      path = ' '
    }

    formData.append('sProjectName', this.projectName)
    formData.append('sPath', path)
    formData.append('upload-file', $file)

    openLoading('正在上传中...')

    uploadFile(formData)
      .then(() => {
        closeLoading()
        this.$alert(`'${$file.name}'上传成功`)
        this.fetchProjectDetail()
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
  }
  @Watch('breadList')
  checkIsRoot()
  {
    if (!this.breadList.length) {
      this.isRoot = true
    }
    else
    {
      this.isRoot = false
    }
  }
  onPrevDirectory(): void {
    if (!this.breadList.length) {
      this.$alert('已经是根目录啦')
      return
    }
    this.breadList.pop()
    this.fetchProjectDetail()
    this.onCloseFile()
  }

  disabledEditor() {
    this.cmOptions = Object.assign({}, this.cmOptions, {
      readOnly: true
    })
  }
  enabledEditor() {
    this.cmOptions = Object.assign({}, this.cmOptions, {
      readOnly: false
    })
  }
  fetchProjectDetail(): void {
    openLoading('正在拉取项目详情...')

    let path = ''
    if (this.breadList.length) {
      path = this.breadList.join('/')
    } else {
      path = ' '
    }

    getProjectDetail(this.userName, this.projectName, path)
      .then((data: Array<FileInterface> = []) => {
        console.log('拉取到的项目详情：', data)
        // 对项目进行排序
        // 数字 -> 中文 -> 字母
        data.sort((a: FileInterface, b: FileInterface) => {
          return a.name.localeCompare(b.name, 'zh')
        })
        const dirList: Array<FileInterface> = []
        const fileList: Array<FileInterface> = []
        data.forEach((item: FileInterface) => {
          if (item.Type === 'directory') {
            dirList.push(item)
          } else {
            fileList.push(item)
          }
        })

        data = dirList.concat(fileList)

        this.resList = data
        this.search = ''
        this.searchList = data
        closeLoading()
      })
      .catch(err => {
        console.log('出错啦：', err)
        this.$alert(err)
        closeLoading()
      })
  }

  handleClickJumpDir(idx: number) {
    console.log('跳转索引：', idx)
    this.breadList.splice(idx + 1)
    console.log('after: ', this.breadList)
    this.fetchProjectDetail()
    this.onCloseFile()
  }

  jumpDir(dir: FileInterface): void {
    console.log('准备拉取文件夹')
    this.breadList.push(dir.name)
    this.fetchProjectDetail()
  }

  onPreviewHTML() {
    let file = this.selected
    const url = `${IPFS_URL}/${file.hash}`
    window.open(url)
  }

  openFile(file: FileInterface): void {
    this.isHTMLFile = false
    // 判断文件后缀
    const ext = getFileExtension(file.name)
    if (isResourceType(ext)) {
      // 预览office文件
      if (isOfficeType(ext)) {
        const url = `/#/preview?hash=${file.Hash}&filename=${file.name}`
        window.open(url)
        return
      }

      // 暂时不使用统一预览模板
      // const url = `/#/preview?hash=${file.hash}&filename=${file.name}&isOffice=false`;
      const url = `${IPFS_URL}/${file.Hash}`
      window.open(url)
      return
    }

    if (isWebpageType(ext)) {
      // 网页，可以打开
      this.isHTMLFile = true;
    }

    openLoading('正在获取文件内容...')

    // 读取文件内容
    this.isCreatingNewFile = false
    this.showFileContent = true
    this.editorFileContent = ''
    this.disabledEditor()

    if (LANGUAGE_MAP[ext]) {
      this.language = ext
    } else {
      this.language = '-1'
    }
    getFileContent(file.Hash)
      .then(data => {
        console.log(data)

        let content = data.sMsg
        // console.log('获取到的文件内容：', content)
        if (!content) {
          content = '文件内容为空'
        }
        console.log(content)

        content = decodeURIComponent(content)
        this.fileContent = content
        this.editorFileContent = content
        closeLoading()
      })
      .catch(err => {
        this.$alert(err)
        closeLoading()
      })
  }
}
</script>

<style lang="stylus" scoped>
.file-icon
  font-size 20px !important
.bread-item
  font-weight normal
  cursor pointer
  color #448aff !important
.new
  justify-content space-between
.align-right span
  margin-left 2px
.back-link
  display inline-block
  padding 10px 5px
  i
    margin-bottom 5px
.language
  display flex
  justify-content flex-end
</style>

<style>
.code-editor .CodeMirror {
  height: auto !important;
}

.language .md-field {
  width: 150px;
  margin-bottom: 0;
}
</style>
