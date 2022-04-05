<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <div>
          <h3>个人信息</h3>
          <md-divider></md-divider>
          <div class="create-panel">
            <div class="align-center">
              <img :src="avatar"
                   alt="头像"
                   class="avatar">
              <p v-show="!isEdit">
                <md-button class="md-primary md-raised"
                           @click="onUploadFile">更改头像</md-button>
              </p>
            </div>
            <div class="field">
              <div>
                <div>
                  <md-icon class="fa fa-user"></md-icon>
                </div>
                <md-field>
                  <label>
                    用户名
                  </label>
                  <md-input v-model="editUser.sUserName"
                            disabled="disabled"></md-input>
                </md-field>
              </div>
              <p># 一个简短且方便记忆的名称更容易让人记住</p>
            </div>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-address-book"></md-icon>
              </div>

              <div>
                <md-radio v-model="editUser.iSex"
                          value="1"
                          :disabled="!isEdit">男</md-radio>
                <md-radio v-model="editUser.iSex"
                          value="0"
                          :disabled="!isEdit">女</md-radio>
                <md-radio v-model="editUser.iSex"
                          value="-1"
                          :disabled="!isEdit">保密</md-radio>
              </div>
            </div>
            <p></p>
          </div>
          <!-- <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-envelope-open"></md-icon>
              </div>
              <md-field>
                <label>
                  邮箱
                </label>
                <md-input v-model="editUser.sEmail"
                          :disabled="!isEdit"></md-input>
              </md-field>
            </div>
            <p></p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-phone"></md-icon>
              </div>
              <md-field>
                <label>
                  手机
                </label>
                <md-input v-model="editUser.sPhone"
                          :disabled="!isEdit"></md-input>
              </md-field>
            </div>
            <p></p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-id-card"></md-icon>
              </div>
              <md-field>
                <label>
                  真实姓名
                </label>
                <md-input v-model="editUser.sRealName"
                          :disabled="!isEdit"></md-input>
              </md-field>
            </div>
            <p># 实名认证</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-id-card"></md-icon>
              </div>
              <md-field>
                <label>
                  学号
                </label>
                <md-input v-model="editUser.sStudentNumber"
                          :disabled="!isEdit"></md-input>
              </md-field>
            </div>
            <p># 如21119xxxxx</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-id-card"></md-icon>
              </div>
              <md-field>
                <label>
                  班级
                </label>
                <md-input v-model="editUser.sClass"
                          :disabled="!isEdit"></md-input>
              </md-field>
            </div>
            <p># 如19级计算机学硕</p>
          </div> -->
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  个人简介
                </label>
                <md-textarea v-model="editUser.sDescription"
                             :disabled="!isEdit"></md-textarea>
              </md-field>
            </div>
            <p></p>
          </div>
          <div class="field hidden">
            <md-field>
              <label>修改头像</label>
              <md-file placeholder="选择头像（仅限图片类型）"
                       @md-change="onFileChange"
                       id="file"
                       accept="image/*" />
            </md-field>
            <p># 仅支持图片类型</p>
          </div>
        </div>
      </md-card-content>

      <md-card-actions>
        <template v-if="isEdit">
          <md-button class="md-raised md-primary"
                     @click="onSave">保存</md-button>
          <md-button class="md-raised md-primary"
                     @click="onCancel">取消</md-button>
        </template>

        <md-button class="md-raised md-primary"
                   @click="isEdit = true"
                   v-else>编辑信息</md-button>

      </md-card-actions>
    </md-card>
    <md-dialog :md-active.sync="showImgTxDiaLog" :md-click-outside-to-close="false" class="tx_dialog">
      <md-dialog-title>发起头像更新交易</md-dialog-title>
      <md-field>
        <label>账户</label>
        <md-input v-model="editUser.sUserName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>账户地址</label>
        <md-input v-model="editUser.sWalletAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>新的图片名</label>
        <md-input v-model="fileName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <p class="pay-tip"># 手续费0.5个币</p>
      <md-dialog-actions>
        <md-button class="md-primary" @click="onCancelImg">取消</md-button>
        <md-button class="md-primary" @click="onConfirmImg">更新</md-button>
      </md-dialog-actions>
    </md-dialog>
    <md-dialog :md-active.sync="showInfoTxDiaLog"  class="tx_dialog">
      <md-dialog-title>发起信息更新交易</md-dialog-title>
      <md-field>
        <label>账户</label>
        <md-input v-model="editUser.sUserName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>账户地址</label>
        <md-input v-model="editUser.sWalletAddress" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <p class="pay-tip"># 手续费1.0个币</p>
      <md-dialog-actions>
        <md-button class="md-primary" @click="onCancelInfo">取消</md-button>
        <md-button class="md-primary" @click="onConfirmInfo">保存</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
  
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator'
import { Getter, Mutation } from 'vuex-class'
import { SET_USER } from '@/store/mutation-types'
import { trim, isPhone, isEmail, isID,isValidPwd } from '@utils/index'
import { getUserInfo, updateUserInfo, uploadFile } from '@api/user'
import {openLoading, closeLoading} from '@utils/share-variable'
import { UserInterface } from '@/utils/interface';
import baseURL from '@utils/api-url';


const user = {
  sId: '',
  sUserName: '',
  sDisplayName: '',
  sAvatar: '',
  sWalletAddress: '',
  iSex: '-1',
  sDescription:""
}

@Component
export default class Profile extends Vue {
  file: any = null
  isEdit: boolean = false
  defaultUser: UserInterface = user
  editUser: UserInterface = user

  // 交易
  password: string = "";
  fileName: string = "";
  showImgTxDiaLog: boolean = false;
  showInfoTxDiaLog: boolean = false;

  @Getter('isLogin') isLogin!: boolean
  @Getter('user') user!: UserInterface
  @Mutation(SET_USER) setUser: any

  created(): void {
    if (!this.isLogin) {
      this.$alert('您还没有登录')
      this.$router.push('/')
    }
    this.fetchUserInfo()
  }

  // 获取头像
  get avatar(): string {
    if (!this.editUser || !this.editUser.sAvatar) {
      return `${baseURL}/user/User/getImage?sImagePath=""
    }`
    }
    return `${baseURL}/user/User/getImage?sImagePath=${
      this.editUser.sAvatar
    }`
  }

  
  // 用户信息的获取
  fetchUserInfo(): void {
    getUserInfo()
      .then(data => {
        console.log('获取个人信息成功：', data)
        this.defaultUser = Object.assign({}, data)
        this.editUser = Object.assign({}, data)
        this.setUser(
          Object.assign({}, this.user, {
            sAvatar: data.sAvatar,
            sUserName: data.sUserName
          })
        )
      })
      .catch(err => {
        this.$alert(err)
      })
  }

  onUploadFile(): void {
    const $file: any = document.querySelector('#file')
    $file.click()
  }

  // 观察图片变化
  onFileChange(fileList: any): void {
    console.log('文件变化：', fileList)
    if (!fileList.length) {
      console.log('没有选择文件')
      this.file = null
      return
    }
    this.file = fileList[0];
    this.fileName = this.file.name;
    this.showImgTxDiaLog = true;
  }
  onConfirmImg(): void 
  {
    // 数据合法校验
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
    this.changeImage();
    
  }
  onCancelImg(): void 
  {
    this.file = null;
    this.fileName = "";
    this.showImgTxDiaLog = false;
  }

  changeImage(): void 
  {
    // 这里直接上传
    const formData = new FormData()
    formData.append('upload-file', this.file)
    formData.append('password', this.password)
    formData.append('sUserName', this.editUser.sUserName)
    openLoading('正在上传图片...')
    uploadFile(formData)
      .then(data => {
        console.log('上传成功')
        const sImagePath = data.sFileName
        this.editUser.sAvatar = sImagePath
        this.defaultUser = sImagePath
        closeLoading()
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
      this.showImgTxDiaLog = false;
  }

  // 用户信息更新
  onSave(): void {
    // trim
    Object.keys(this.editUser).forEach(key => {
      if (['iSex', 'sAvatar', 'sDescription'].includes(key)) {
        console.log(`${key}匹配`)
        return
      }
      // @ts-ignore
      this.editUser[key] = trim(this.editUser[key])
    })
    if(this.editUser["iSex"] == this.defaultUser["iSex"] && 
    this.editUser["sAvatar"] == this.defaultUser["sAvatar"] && 
    this.editUser["sDescription"] == this.defaultUser["sDescription"])
    {
        this.isEdit = false
        return
    }
    console.log('匹配后：', this.editUser)
    if (this.editUser.sUserName === '') {
      return this.$alert('用户名不能为空')
    }
    this.showInfoTxDiaLog = true;
  }
  updateInfo(): void{
    const updateInfo = Object.assign({"password":this.password}, this.editUser)
    
    openLoading('正在修改个人信息...')
    updateUserInfo(updateInfo)
      .then(data => {
        closeLoading()
        this.isEdit = false
        this.fetchUserInfo()
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
      this.showInfoTxDiaLog = false;
  }
  onConfirmInfo(): void {
    // 数据合法校验
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
    this.updateInfo();
  }
  onCancelInfo(): void {
    this.editUser = Object.assign({}, this.defaultUser)
    this.isEdit = false
    this.showInfoTxDiaLog = false;
  }

  onCancel(): void {
    this.editUser = Object.assign({}, this.defaultUser)
    this.isEdit = false
  }
}
</script>

<style lang="stylus" scoped>
.container
  max-width 500px
.owner
  display flex
  align-items center
.avatar
  width 100px
  height 100px
  // border 1px solid #333
  margin-top 10px
.owner-wrapper
  display flex
  align-items center
  img
    margin-right 3px
.field
  margin-top 20px
  & > div
    display flex
    align-items center
  p
    opacity 0.44
    margin-top -18px
.md-icon
  font-size 18px !important
  margin-top 2px
</style>

