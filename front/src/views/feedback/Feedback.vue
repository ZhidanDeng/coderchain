<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-tabs md-centered class="md-transparent">
          <md-tab md-label="意见反馈">
          <h3>意见反馈</h3>
          <md-divider></md-divider>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  反馈内容
                </label>
                <md-textarea v-model="content"></md-textarea>
              </md-field>
            </div>
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  用户地址
                </label>
                <md-input v-model="address"></md-input>
              </md-field>
            </div>
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  用户密码
                </label>
                <md-input v-model="password" type="password"></md-input>
              </md-field>
            </div>
            <md-card-actions>

              <md-button class="md-raised md-primary"
                        @click="onSave">提交反馈</md-button>
            </md-card-actions>
          </div>
          </md-tab>
          <md-tab md-label="意见详情" @click="fetchAllComment">
            <h3>用户意见详情</h3>
              <md-divider></md-divider>
              <md-list>
                <md-list-item v-for="(comment, index) in commentList"
                              :key="comment.timestamp"
                              class="rank-item">
                  <div>
                    <!-- <md-icon class="fa fa-inbox"></md-icon>&nbsp; -->
                    <span>[{{ index + 1 }}]&nbsp;</span><span>{{comment.user}}&nbsp;</span>
                    <p>{{comment.content}}</p>

                  </div>
                  <md-divider></md-divider>
                </md-list-item>
              </md-list>
          </md-tab>
        </md-tabs>
        <!-- <div>
          <h3>意见反馈</h3>
          <md-divider></md-divider>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-id-card"></md-icon>
              </div>
              <md-field>
                <label>
                  昵称
                </label>
                <md-input v-model="userName"></md-input>
              </md-field>
            </div>
            <p></p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-envelope-open"></md-icon>
              </div>
              <md-field>
                <label>
                  联系方式（填入邮箱或者手机号）
                </label>
                <md-input v-model="contact"></md-input>
              </md-field>
            </div>
            <p></p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  反馈内容
                </label>
                <md-textarea v-model="content"></md-textarea>
              </md-field>
            </div>
            <p># 好的反馈有机会获得Token奖励</p>
          </div>
        </div> -->
      </md-card-content>

      <!-- <md-card-actions>

        <md-button class="md-raised md-primary"
                   @click="onSave">提交反馈</md-button>
      </md-card-actions> -->
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator'
import { openLoading, closeLoading } from '@utils/share-variable'
import { trim } from '@/utils'
import { addFeedback, getFeedbackList } from '@api/user'
import { Getter, Mutation,Action } from 'vuex-class'
import { SET_USER } from '@/store/mutation-types'
import { UserInterface } from '@/utils/interface';
@Component
export default class Profile extends Vue {
  content: string = ''
  address: string = ''
  password: string = ''

  commentList: Array<any> = []

  @Getter('isLogin') isLogin!: boolean
  @Getter('user') user!: UserInterface
  @Mutation(SET_USER) setUser: any

  created(): void {
    if(this.isLogin)
    {
      this.address = this.user.sWalletAddress
    }
  }
  fetchAllComment(): void{
    if(this.commentList.length>0)
    {
      return
    }
    else
    {
      openLoading('正在努力搜集用户反馈列表...')
      getFeedbackList()
        .then(data => {
          this.commentList = data
          // 把这个
          closeLoading()
        })
        .catch(err => {
          console.log('出错啦：', err)
          this.$alert(err)
          closeLoading()
        })
    }
  }


  onSave(): void {
    this.content = trim(this.content)
    if (this.content === '') {
      this.$alert('请填入有效的反馈内容')
      return
    }
    if (this.address === '') {
      this.$alert('请填入有效的反馈内容')
      return
    }
    if (this.password === '') {
      this.$alert('请填入有效的反馈内容')
      return
    }
    openLoading('正在提交反馈内容...')
    addFeedback(this.content, this.password, this.address)
      .then(data => {
        closeLoading()
        this.$alert('反馈成功，感谢您的意见')
        this.content = ''
        this.password = ''
        this.address = ''
        // this.$router.push('/')
      })
      .catch(err => {
        closeLoading()
        this.$alert(err)
      })
  }
}
</script>

<style lang="stylus" scoped>
.container
  max-width 800px
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

