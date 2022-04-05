<template>
  <div class="container">
    <md-card class="con-card">
      <md-card-content>
        <div class="item">
          <div id="chartUser"
               class="chart-user"></div>
        </div>
      </md-card-content>
    </md-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator'
import echarts from 'echarts'

@Component
export default class DetailIndex extends Vue {
  mounted(): void {
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('chartUser'))

    // 指定图表的配置项和数据
    var option = {
      title: {
        text: '社区统计图'
      },
      tooltip: {},
      legend: {
        data: ['用户量', '项目量']
      },
      xAxis: {
        data: ['2018.9', '2018.10', '2018.11', '2018.12', '2019.1', '2019.2', '2019.3', '2019.4']
      },
      yAxis: {},
      series: [
        {
          name: '用户量',
          type: 'line',
          data: [0, 100, 110, 130, 150, 200, 230, 250],
          // itemStyle : { normal: {label : {show: true}}}
        },
      {
          name: '项目量',
          type: 'line',
          data: [0, 30, 40, 55, 60, 65, 85, 100],
          // itemStyle : { normal: {label : {show: true}}}
        }
      ]
    }

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option)
  }
}
</script>


<style lang="stylus" scoped>
.con-card
  padding 20px 0
.chart-user
  width 100%
  height 400px
</style>

