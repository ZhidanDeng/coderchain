<template>
  <div>
    <div v-show="resList.length">
      <div class="project-content"
           v-for="project in resList"
           :key="project.sId">
        <project-item :project="project"
                      :handleClickWatchChainInfo="handleClickWatchChainInfo"></project-item>
                      <md-divider></md-divider>
      </div>
    </div>
    <!-- 不用template -->
    <div v-show="!resList.length">
      <div>
        <md-empty-state md-label="还没有发布过项目噢"
                        :md-description="``">
          <md-button class="md-primary md-raised" to="/project">去看看其他项目</md-button>
        </md-empty-state>
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
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Mixins } from 'vue-property-decorator'
import { getUserProjects } from '@/api/project'
import { ProjectMixin } from '@utils/mixin'
import ProjectItem from '@/components/project-item/ProjectItem.vue'
import {openLoading, closeLoading} from '@utils/share-variable'

@Component({
  components: {
    ProjectItem
  }
})
export default class UserProject extends Mixins(ProjectMixin) {
  @Prop() readonly userName!: string
  @Prop() readonly sAvatar!: string

  resList: Array<object> = []

  created() {
    this.fetchUserProject()
  }

  fetchUserProject(): void {
    openLoading('正在拉取个人项目...')
    getUserProjects(this.userName)
    .then(data => {
      console.log('获取到的项目列表：', data)
      // 先排序
      data.sort((a: any, b: any) => {
        return b.createAt - a.createAt
      })
      data = data.map((item: any) => {
        item['sUserName'] = this.userName
        item['sAvatar'] = this.sAvatar
        return item
      })
      this.resList = data
      closeLoading()
    })
    .catch(err => {
      this.$alert(err)
      closeLoading()
    })
    console.log(this.resList);
  }
}
</script>

<style lang="stylus" scoped>
.project-content
  margin-bottom 20px
</style>

