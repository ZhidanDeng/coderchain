<template>
  <div class="container">
    <md-card class="no-shadow">
      <md-card-content>
        <div class="field">
          <div>
            <div>
              <md-icon class="fa fa-inbox"></md-icon>
            </div>
            <md-field>
              <label>项目名称*</label>
              <md-input v-model="showProjectName"
                        disabled></md-input>
            </md-field>
          </div>
          <p># 一个好的项目名称是简短并且容易记忆的</p>
        </div>
        <div class="field">
          <div>
            <div>
              <md-icon class="fa fa-file-code-o"></md-icon>
            </div>
            <md-field>
              <label for="movie">
                项目类型*
              </label>
              <md-select v-model="editProject.sCategoryName"
                         name="movie"
                         id="category"
                         :disabled="!isEdit">
                <md-option v-for="category in categoryList"
                           :value="category"
                           :key="category">{{ category }}</md-option>
              </md-select>
            </md-field>
          </div>
          <p># 项目类型在索引时提供帮助</p>
        </div>
        <div class="field">
          <div>
            <div>
              <md-icon class="fa fa-file-text-o"></md-icon>
            </div>
            <md-field>
              <label>
                项目描述
              </label>
              <md-textarea v-model="editProject.sDescription"
                           :disabled="!isEdit"></md-textarea>
            </md-field>
          </div>
          <p># 项目描述可以让人了解您的项目</p>
        </div>
        <div class="field">
          <div>
            <md-field>
              <md-icon class="fa fa-calendar-o"></md-icon>
              <label>创建时间</label>
              <md-input :value="date"
                        disabled></md-input>
            </md-field>
          </div>
          <p class="info"
             v-if="isEdit"># 目前仅支持修改项目类型和项目描述</p>
        </div>
      </md-card-content>

      <md-card-actions v-if="isOwner">
        <md-button class="md-primary md-raised"
                   v-if="!isEdit"
                   @click="isEdit=true">修改信息</md-button>
        <md-button class="md-accent md-raised"
                   v-if="!isEdit"
                   @click="onDelete">删除项目</md-button>
        <template v-else>
          <md-button class="md-primary md-raised"
                     @click="onSave">确认</md-button>
          <md-button class="md-accent md-raised"
                     @click="onCancel">取消更改</md-button>
        </template>

      </md-card-actions>
    </md-card>
    <md-dialog :md-active.sync="showUpdateTxDiaLog"  class="tx_dialog">
      <md-dialog-title>发起项目更新交易</md-dialog-title>
      <md-field>
        <label>账户</label>
        <md-input v-model="userName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>账户地址</label>
        <md-input v-model="user.sWalletAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>更新项目</label>
        <md-input v-model="projectName" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <p class="pay-tip"># 手续费0.5个币</p>
      <md-dialog-actions>
        <md-button class="md-primary" @click="onCancelUpdate">取消</md-button>
        <md-button class="md-primary" @click="onConfirmUpdate">更新</md-button>
      </md-dialog-actions>
    </md-dialog>
    <md-dialog :md-active.sync="showDeleteTxDiaLog"  class="tx_dialog">
      <md-dialog-title>发起项目删除交易</md-dialog-title>
      <md-field>
        <label>账户</label>
        <md-input v-model="userName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>账户地址</label>
        <md-input v-model="user.sWalletAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>删除项目</label>
        <md-input v-model="projectName" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <md-dialog-actions>
        <md-button class="md-primary" @click="showDeleteTxDiaLog = false">取消</md-button>
        <md-button class="md-primary" @click="onConfirmDelete">保存</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator'
import { Getter } from 'vuex-class'
import { getProjectInfo, updateProjectInfo, deleteProject } from '@api/project'
import { trim, isValidPwd,filterFullTime } from '@utils/index'
import { openLoading, closeLoading } from '@utils/share-variable'
import { UserInterface } from '@utils/interface'

@Component
export default class DetailSetting extends Vue {
  @Prop() readonly userName!: string
  @Prop() readonly projectName!: string
  @Prop() readonly isOwner!: boolean
  @Prop() readonly user!: UserInterface

  @Getter categoryList!: Array<string>
  // 项目详情
  project: any = {}
  // 编辑的项目
  editProject: any = {}
  // 是否编辑
  isEdit: boolean = false
  // 交易相关
  password: string = "";
  showUpdateTxDiaLog: boolean = false;
  showDeleteTxDiaLog: boolean = false;

  created(): void {
    this.fetchProjectInfo()
  }

  // 时间获取
  get date() {
    return filterFullTime(String(this.project.createAt))
  }

  // 项目名
  get showProjectName() {
    return decodeURIComponent(this.projectName)
  }
  
  // 获取项目信息
  fetchProjectInfo(): void {
    openLoading('正在获取项目信息...')
    getProjectInfo(this.userName, this.projectName)
      .then(data => {
        this.project = data
        this.editProject = Object.assign({}, this.project)
        closeLoading()
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
  }


  // 项目更新相关
  onSave(): void {
    console.log('确认修改：', this.editProject)
    if (this.editProject.sCategoryName === '') {
      return this.$alert('请选择项目类型')
    }
    if(this.editProject.sCategoryName == this.project.sCategoryName && this.editProject.sDescription == this.project.sDescription)
    {
      this.isEdit = false;
      return;
    }
    if(this.editProject.sProjectName != this.project.sProjectName)
    {
      return this.$alert('项目名不可修改')
    }
    this.showUpdateTxDiaLog = true;
  }
  onConfirmUpdate() :void{
    // 数据合法校验
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
    this.updateProject()
  }
  updateProject(): void
  {
    openLoading('正在保存中...')
    updateProjectInfo(
      this.projectName,
      this.editProject.sCategoryName,
      this.editProject.sDescription,
      this.password
    )
      .then(data => {
        // 更新成功
        this.project = Object.assign({}, this.editProject)
        this.isEdit = false
        closeLoading()
        this.$alert('更新信息成功')
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
      this.showUpdateTxDiaLog = false;
  }
  // 取消项目更新
  onCancel(): void {
    this.editProject = Object.assign({}, this.project)
    this.isEdit = false
  }
  onCancelUpdate():void{
    this.editProject = Object.assign({}, this.project)
    this.isEdit = false
    this.showUpdateTxDiaLog = false;
  }

  // 删除监听
  onDelete(): void {
    this.showDeleteTxDiaLog = true;
    // console.log('删除项目：')
    // const instance = this.$confirm({
    //   content: `确认删除项目'${this.project.sProjectName}'吗`
    // })

    // instance.$on('confirm', () => {
    //   console.log('on confirm')
    //   console.log('删除中：', this.projectName)

    //   openLoading('正在删除中...')
    //   deleteProject(this.projectName)
    //     .then(() => {
    //       closeLoading()
    //       this.$router.replace('/')
    //     })
    //     .catch(err => {
    //       console.log('删除出错啦：', err)
    //       closeLoading()
    //       this.$alert(err)
    //     })
    // })

    // instance.$on('cancel', () => {
    //   console.log('on cancel')
    // })
  }
  onConfirmDelete(): void{
    // 数据合法校验
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
    this.DeleteProject()
  }
  DeleteProject():void{
    openLoading('正在删除中...')
      deleteProject(this.projectName,this.password)
        .then(() => {
          closeLoading()
          this.$router.replace('/')
        })
        .catch(err => {
          console.log('删除出错啦：', err)
          closeLoading()
          this.$alert(err)
        })
        this.showDeleteTxDiaLog = false;
  }
}
</script>

<style lang="stylus" scoped>
.container
  width 500px
.view
  max-width 500px
.field
  margin-top 20px
  & > div
    display flex
    align-items center
  p
    opacity 0.44
    margin-top -18px
  .info
    color #f00
    opacity 1
.md-icon
  font-size 18px !important
  margin-top 2px
</style>