<template>
  <div>
    <md-card v-show="reportList.length" class="no-shadow">
      <md-table v-model="reportList"
                md-sort="name"
                md-sort-order="asc">
        <md-table-toolbar>
          <h1 class="md-title">评测列表</h1>
          <p># 评测结果仅供参考</p>
        </md-table-toolbar>
        <md-table-row slot="md-table-row"
                      slot-scope="{ item }">
          <md-table-cell md-label="任务ID"
                         md-sort-by="sDetectTaskId"
                         md-numeric>{{ item.sDetectTaskId }}</md-table-cell>
          <md-table-cell md-label="项目名称">{{ projectName }}</md-table-cell>
          <md-table-cell md-label="检测分数"
                         md-sort-by="iScore">{{ item.iScore }}</md-table-cell>
          <md-table-cell md-label="检测时间"
                         md-sort-by="createAt">{{ item.createAt | filterFullTime}}</md-table-cell>
          <md-table-cell md-label="操作">
            <md-button class="md-primary md-raised"
                       :href="`${baseURL}/detect/Detect/detectReport?sDetectTaskId=${item.sDetectTaskId}`"
                       target="_blank">查看报告</md-button>
          </md-table-cell>
          <!-- <md-table-cell md-label="Job Title" md-sort-by="title">{{ item.title }}</md-table-cell> -->
        </md-table-row>
      </md-table>
      <div class="align-center">
        <md-button class="md-primary md-raised"
                   @click="onDetect"
                   v-if="isOwner">进行安全评测</md-button>
      </div>
    </md-card>

    <div v-show="!reportList.length">
      <md-empty-state md-label="没有检测报告"
                      :md-description="`项目还没有进行过代码安全评测`">
        <md-button class="md-primary md-raised"
                   @click="onDetect"
                   v-if="isOwner">立刻进行代码评测</md-button>
      </md-empty-state>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator'
import { getReportList, addDetectTask, getDetectStatus } from '@api/project'
import { ReportInterface } from '@utils/interface'
import {
  openLoading,
  openBar,
  closeLoading,
  closeBar
} from '@utils/share-variable'
import baseURL from '@utils/api-url'

@Component
export default class DetailReport extends Vue {
  @Prop() readonly userName!: string
  @Prop() readonly projectName!: string
  @Prop() readonly isOwner!: boolean
  baseURL: string = baseURL
  reportList: Array<ReportInterface> = []

  created(): void {
    this.fetchReportList()
  }

  fetchReportList(): void {
    openLoading('正在获取检测报告...')
    getReportList(this.userName, this.projectName)
      .then(data => {
        this.reportList = data
        console.log('获取到的数据：', data)
        closeLoading()
      })
      .catch(err => {
        this.$alert(err)
        closeLoading()
      })
  }

  onDetect(): void {
    openLoading('正在添加检测任务，请耐心等待...')

    addDetectTask(this.userName, this.projectName)
      .then(data => {
        console.log('添加任务完成')
        const sDetectTaskId = data.sDetectTaskId
        openLoading('正在获取检测状态...')

        getDetectStatus(sDetectTaskId)
          .then(res => {
            closeLoading()
            const score = res.iScore
            this.$alert(`检测项目成功，您的项目得分为${score}`)
            this.fetchReportList()
          })
          .catch(err => {
            this.$alert(err)
            closeLoading()
          })
        console.log('获取到的数据：', data)
      })
      .catch(err => {
        this.$alert(err)
        closeLoading()
      })
  }
}
</script>
