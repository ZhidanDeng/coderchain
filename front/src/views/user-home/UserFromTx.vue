<template>
  <div class="container">
    <md-table class="vote-table" v-show="txList.length" @md-selected="showTxDetail">
      <div class="vote-detail">总交易次数 {{totalConut}} 次</div>
      <md-table-row>
        <md-table-head>ID</md-table-head>
        <md-table-head>Hash</md-table-head>
        <md-table-head>CreateTime</md-table-head>
      </md-table-row>
      <md-table-row class="vote-table-row" @click="showTxDetail(vote.hash)" v-for="(vote, index) in txList" :key="vote.hash">
        <md-table-cell>{{index + 1}}</md-table-cell>
        <md-table-cell>{{vote.hash}}</md-table-cell>
        <md-table-cell>{{f(String(vote.createTime))}}</md-table-cell>
      </md-table-row>
    </md-table>
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
          <md-table-row>
            <md-table-cell>交易类型</md-table-cell>
            <md-table-cell>{{txType[txInfo.txType]}}</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>交易大小</md-table-cell>
            <md-table-cell>{{txInfo.size}}</md-table-cell>
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
          <md-table-row>
            <md-table-cell>交易金额</md-table-cell>
            <md-table-cell>{{HandleToken(txInfo.txCount)}}&nbsp;CDB</md-table-cell>
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
import {openLoading, closeLoading} from '@utils/share-variable'
import { getUserTx,getUserTxDetail } from '@/api/user'
import { filterPartTime } from '@utils/index'

@Component
export default class UserFromTx extends Vue {
  f :any = filterPartTime
  @Prop() readonly userName!: string
  @Prop() readonly sAvatar!: string
  

  txList: Array<any> = []
  totalConut: number = 0
  created(): void {
    this.txChange()
  }

  txType: object = {
    '202':'用户注册',
    '203':'信息更新',
    '204':'头像更新',
    '205':'项目创建',
    '206':'项目更新',
    '207':'项目投票',
    '208':'项目删除',
    '209':'转账交易'
  }

  txChange():void{
    getUserTx(this.userName, 0)
    .then((data)=>{
      this.txList = data.hashList.sort(
        (a: any, b: any) =>
          b.createTime - a.createTime
      )
      this.totalConut = data.txCount
      closeLoading();
    })
    .catch((err)=>{
      closeLoading();
      this.$alert(err);
    })
  }

  HandleToken(token: number|string): number{
    // @ts-ignore
    return parseFloat(parseInt(token) / 100000000).toFixed(2)
  }

  // 交易详情相关
  showTxDetailDialog: boolean = false;
  txInfo: object = {}
  currentHash: string = "";
  showTxDetail(tx: string):void{
    if(tx === this.currentHash)
    {
      this.showTxDetailDialog = true;
      return
    }
    openLoading("正在获取交易详情，请耐心等待...")
    getUserTxDetail(tx)
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
</script>