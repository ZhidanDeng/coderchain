<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-tabs md-centered class="md-transparent">
          <md-tab md-label="项目排行">
            <div>
              <h3>项目热度排行</h3>
              <p>项目热度排行榜每天更新一次(00:00)，根据项目投票支持数排序（展示前20名）</p>
              <md-divider></md-divider>
              <md-list>
                <md-list-item v-for="(project, index) in projectList"
                              :key="project.sProjectName"
                              class="rank-item">
                  <div>
                    <md-icon class="fa fa-inbox"></md-icon>&nbsp;
                    <span>[{{ index + 1 }}]&nbsp;</span>
                    <router-link :to="`/user/${project.sUserName}`">{{ project.sUserName }}</router-link>
                    <span>&nbsp;/&nbsp;</span>
                    <router-link :to="`/detail/code?projectName=${encodeURIComponent(project.sProjectName)}&userName=${project.sUserName}`"
                                active-class="active-class">{{ project.sProjectName | decodeURIComponent }}</router-link>
                  </div>
                  <p>Token: <span class="token">{{ HandleToken(project.iSupportToken) }}&nbsp;</span>CDB</p>
                </md-list-item>
              </md-list>
            </div>
          </md-tab>
          <md-tab md-label="用户排行" @click="fetchAllUser">
            <h3>用户token排行</h3>
              <p>用户token排行榜每天更新一次(00:00)，根据用户token数排序（展示前20名）</p>
              <md-divider></md-divider>
              <md-list>
                <md-list-item v-for="(user, index) in userList"
                              :key="user.userName"
                              class="rank-item">
                  <div>
                    <md-icon class="fa fa-inbox"></md-icon>&nbsp;
                    <span>[{{ index + 1 }}]&nbsp;</span>
                    <router-link :to="`/user/${user.userName}`">{{ user.userName }}&nbsp;/&nbsp;{{ user.address }}</router-link>
                  </div>
                  <p>Token: <span class="token">{{ HandleToken(user.balance) }}&nbsp;</span>CDB</p>
                </md-list-item>
              </md-list>
          </md-tab>
        </md-tabs>
        
      </md-card-content>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject } from 'vue-property-decorator'
import { Getter } from 'vuex-class'
import { UserInterface, ProjectInterface } from '@utils/interface'
import { openLoading, closeLoading } from '@utils/share-variable'
import { getProjectRank } from '@api/project'
import { getAllUser } from '@api/user'

@Component
export default class DetailIndex extends Vue {
  projectList: Array<ProjectInterface> = []
  userList: Array<any> = []
  tokenUnit: number = 100000000;

  created() {
    this.fetchAllProject()
  }

  fetchAllUser(): void
  {
    if(this.userList.length>0)
    {
      return
    }
    else
    {
      openLoading('正在努力搜集用户排行榜...')
      getAllUser()
        .then(data => {
          // 按照Token排序
          data = data.sort(
            (a: any, b: any) =>
              b.balance - a.balance
          )
          this.userList = data.slice(0, 20)
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

  fetchAllProject(): void {
    openLoading('正在努力搜集项目排行榜...')
    getProjectRank()
      .then(data => {
        console.log('获取到的项目列表：', data)
        // 按照Token排序
        data = data.sort(
          (a: ProjectInterface, b: ProjectInterface) =>
            b.iSupportToken - a.iSupportToken
        )
        this.projectList = data.slice(0, 20)
        // 把这个
        closeLoading()
      })
      .catch(err => {
        console.log('出错啦：', err)
        this.$alert(err)
        closeLoading()
      })
  }

  HandleToken(token: number|string): number{
    // @ts-ignore
    return parseFloat(parseInt(token) / this.tokenUnit).toFixed(2)
  }
}
</script>

<style lang="stylus" scoped>
.token
  color red
</style>

<style>
.rank-item .md-list-item-content {
  flex-wrap: wrap;
  white-space: normal;
}
</style>
