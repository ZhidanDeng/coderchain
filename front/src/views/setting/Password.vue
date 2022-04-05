<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <div>
          <h3>修改密码</h3>
          <md-divider></md-divider>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-lock"></md-icon>
              </div>
              <md-field>
                <label>
                  原密码
                </label>
                <md-input v-model="oldPassword"
                          type="password"></md-input>
              </md-field>
            </div>
            <p># 请输入原密码</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-lock"></md-icon>
              </div>
              <md-field>
                <label>
                  新密码
                </label>
                <md-input v-model="newPassword1"
                          type="password"></md-input>
              </md-field>
            </div>
            <p># 请输入新密码</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-lock"></md-icon>
              </div>
              <md-field>
                <label>
                  确认密码
                </label>
                <md-input v-model="newPassword2"
                          type="password"></md-input>
              </md-field>
            </div>
            <p># 请再次输入新密码</p>
          </div>
        </div>
      </md-card-content>

      <md-card-actions>
        <md-button class="md-raised md-primary"
                   @click="onSave">保存</md-button>
        <md-button class="md-raised"
                   @click="onCancel">重置</md-button>
      </md-card-actions>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator'
import { Getter, Mutation } from 'vuex-class'
import { SET_USER } from '@/store/mutation-types'
import { trim, isPhone, isEmail, isID } from '@utils/index'
import { getUserInfo, updateUserPassword, uploadFile } from '@api/user'
import { openLoading, closeLoading } from '@utils/share-variable'

@Component
export default class Profile extends Vue {
  oldPassword: string = ''
  newPassword1: string = ''
  newPassword2: string = ''

  @Getter('isLogin') isLogin!: boolean
  @Getter('user') user: any
  @Mutation(SET_USER) setUser: any

  created(): void {
    if (!this.isLogin) {
      this.$alert('您还没有登录')
      this.$router.push('/')
    }
  }

  onSave(): void {
    this.oldPassword = trim(this.oldPassword)
    this.newPassword1 = trim(this.newPassword1)
    this.newPassword2 = trim(this.newPassword2)

    if (this.oldPassword === '') {
      this.$alert('原密码不能为空')
      return
    }

    if (this.newPassword1 === '') {
      this.$alert('新密码不能为空')
      return
    }

    if (this.newPassword1 !== this.newPassword2) {
      this.$alert('两次密码输入不一致')
      return
    }

    openLoading('正在修改密码...')

    updateUserPassword({
      sUserName: this.user.sUserName,
      sOriginPwd: this.oldPassword,
      sPwd: this.newPassword1
    })
      .then(data => {
        closeLoading()
        this.$alert('修改密码成功')
        // 修改成功，调回主页
        this.oldPassword = ''
        this.newPassword1 = ''
        this.newPassword2 = ''
        this.$router.push('/')
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
  }

  onCancel(): void {
    this.oldPassword = ''
    this.newPassword1 = ''
    this.newPassword2 = ''
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

