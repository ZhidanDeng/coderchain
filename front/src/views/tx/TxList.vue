<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <md-tabs md-centered class="md-transparent">
          <md-tab md-label="投票交易" @click="isSupport = true">
            <div>
              <h3>投票实时交易</h3>
              <md-divider></md-divider>
              <md-table class="vote-table" v-show="supportList.length" @md-selected="showTxDetail">
                <md-table-row>
                  <md-table-head>ID</md-table-head>
                  <md-table-head>Hash</md-table-head>
                  <md-table-head>CreateTime</md-table-head>
                  <md-table-head>VoteCount</md-table-head>
                </md-table-row>
                <md-table-row class="vote-table-row" @click="showTxDetail(vote.hash,0)" v-for="(vote, index) in supportList" :key="vote.hash">
                  <md-table-cell>{{index + 1}}</md-table-cell>
                  <md-table-cell>{{vote.hash}}</md-table-cell>
                  <md-table-cell>{{f(String(vote.createTime))}}</md-table-cell>
                  <md-table-cell><span class="token">{{HandleToken(vote.txCount)}}</span>&nbsp;CDB</md-table-cell>
                </md-table-row>
              </md-table>
            </div>
          </md-tab>
          <md-tab md-label="转账交易" @click="isSupport = false">
            <h3>转账实时交易</h3>
              
            <md-divider></md-divider>
            <md-table class="vote-table" v-show="transferList.length" @md-selected="showTxDetail">
              <md-table-row>
                <md-table-head>ID</md-table-head>
                <md-table-head>Hash</md-table-head>
                <md-table-head>CreateTime</md-table-head>
                <md-table-head>VoteCount</md-table-head>
              </md-table-row>
              <md-table-row class="vote-table-row" @click="showTxDetail(vote.hash,1)" v-for="(vote, index) in transferList" :key="vote.hash">
                <md-table-cell>{{index + 1}}</md-table-cell>
                <md-table-cell>{{vote.hash}}</md-table-cell>
                <md-table-cell>{{f(String(vote.createTime))}}</md-table-cell>
                <md-table-cell><span class="token">{{HandleToken(vote.txCount)}}</span>&nbsp;CDB</md-table-cell>
              </md-table-row>
            </md-table>
          </md-tab>
        </md-tabs>
      </md-card-content>
    </md-card>
    <md-dialog :md-active.sync="showTxDetailDialog"  class="tx_detail_dialog">
      <md-dialog-title>交易详情</md-dialog-title>
        <md-table>
          <md-table-row>
            <md-table-head>属性</md-table-head>
            <md-table-head>具体值</md-table-head>
          </md-table-row>
          <md-table-row>
            <md-table-cell>交易哈希</md-table-cell>
            <md-table-cell>{{txInfo.hash}}</md-table-cell>
          </md-table-row>
          <md-table-row v-if="txInfo.fromAddress ===''">
            <md-table-cell>发款账户</md-table-cell>
            <md-table-cell>{{txInfo.fromUser}}</md-table-cell>
          </md-table-row>
          <md-table-row v-else>
            <md-table-cell>发款账户</md-table-cell>
            <md-table-cell>{{txInfo.fromUser}}&nbsp;/&nbsp;{{txInfo.fromAddress}}</md-table-cell>
          </md-table-row>
          <md-table-row v-if="txInfo.toAddress ===''">
            <md-table-cell>收款账户</md-table-cell>
            <md-table-cell>{{txInfo.toUser}}</md-table-cell>
          </md-table-row>
          <md-table-row v-else>
            <md-table-cell>收款账户</md-table-cell>
            <md-table-cell>{{txInfo.toUser}}&nbsp;/&nbsp;{{txInfo.toAddress}}</md-table-cell>
          </md-table-row>
          <md-table-row v-if="txInfo.projectName">
            <md-table-cell>支持项目</md-table-cell>
            <md-table-cell>{{txInfo.toUser}}&nbsp;/&nbsp;{{txInfo.projectName}}</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>交易金额</md-table-cell>
            <md-table-cell>{{HandleToken(txInfo.supportCount?txInfo.supportCount:txInfo.txCount)}}&nbsp;CDB</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>创建时间</md-table-cell>
            <md-table-cell>{{f(String(txInfo.createTime))}}</md-table-cell>
          </md-table-row>
        </md-table>
      <md-dialog-actions>
        <md-button class="md-primary" @click="showTxDetailDialog = false">关闭</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script lang="ts">

import { Vue, Component, Prop, Inject, Watch } from "vue-property-decorator";
import { getSupportDetailList ,getSupportDetail } from "@api/project";
import { getTransferList, getTransferDetail} from "@api/user";
import { Getter, Action } from "vuex-class";
import { filterPartTime } from '@utils/index'
import { openLoading, closeLoading } from "@utils/share-variable";

@Component
export default class TxList extends Vue {
  supportList: Array<any> = []
  transferList: Array<any> = []
  tokenUnit: number = 100000000;
  f :any = filterPartTime
  isSupport :boolean = true
  @Watch("isSupport")
  watchIsSupport():void
  {
    if(this.isSupport)
    {
      this.fetchAllSupport()
    }
    else
    {
      this.fetchAllTransfer()
    }
  }
  created() {
    this.fetchAllSupport()
  }


  fetchAllTransfer(): void
  {
    openLoading('正在努力获取转账实时交易情况...')
      getTransferList()
        .then(data => {
          // 按照Token排序
          data = data.sort(
            (a: any, b: any) =>
              b.createTime - a.createTime
          )
          this.transferList = data.slice(0, 50)
          // 把这个
          closeLoading()
        })
        .catch(err => {
          console.log('出错啦：', err)
          this.$alert(err)
          closeLoading()
        })
    
  }

  fetchAllSupport(): void {
    openLoading('正在努力获取项目实时投票情况...')
    getSupportDetailList()
      .then(data => {
        data = data.sort(
          (a: any, b: any) =>
            b.createTime - a.createTime
        )
        this.supportList = data.slice(0, 50)
        // 把这个
        closeLoading()
      })
      .catch(err => {
        this.$alert(err)
        closeLoading()
      })
  }

  HandleToken(token: number|string): number{
    // @ts-ignore
    return parseFloat(parseInt(token) / this.tokenUnit).toFixed(2)
  }

  showTxDetailDialog: boolean = false;
  txInfo: object = {}
  currentHash: string = "";
  showTxDetail(tx: string, type: number):void{
    if(tx === this.currentHash)
    {
      this.showTxDetailDialog = true;
      return
    }
    openLoading("正在获取交易详情，请耐心等待...")
    if(type == 1)
    {
      getTransferDetail(tx)
      .then((data) => {
          this.currentHash = tx
          this.txInfo = data
          closeLoading();
          this.showTxDetailDialog = true;
        })
        .catch((err) => {
          closeLoading();
          this.showTxDetailDialog= false;
          this.$alert(err);
        });
    }
    else
    {
      getSupportDetail(tx)
      .then((data) => {
          this.currentHash = tx
          this.txInfo = data
          closeLoading();
          this.showTxDetailDialog = true;
        })
        .catch((err) => {
          closeLoading();
          this.showTxDetailDialog= false;
          this.$alert(err);
        });
      }
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
