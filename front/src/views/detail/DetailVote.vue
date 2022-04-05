<template>
  <div>
    <md-card class="no-shadow">
      <!-- <md-card-header>
        <div class="md-title">Card without hover effect</div>
      </md-card-header>-->

      <md-card-content>
        <div class="md-layout md-gutter md-alignment-center">
          <div class="md-layout-item vote-detail">项目Token支持数：{{ HandleToken(tokenNumber) }} CDB / 投票交易次数：{{ txCount }} 次</div>
          <div
            v-if="user.sUserName != userName && voteList.length"
            class="md-layout-item md-size-15"
          >
            <md-button class="md-primary md-raised" @click="onShowVoteDialog">我也要支持</md-button>
          </div>
        </div>
        <div v-show="!voteList.length && user.sUserName == userName">
          <div>
            <md-empty-state md-label="该项目还未有过赞赏哦" :md-description="``">
              <md-button class="md-primary md-raised" to="/rank">去看看其他优秀的项目吧</md-button>
            </md-empty-state>
          </div>
          <md-divider></md-divider>
        </div>
        <div v-show="!voteList.length && user.sUserName != userName">
          <div>
            <md-empty-state md-label="该项目还未有过赞赏哦" :md-description="``">
              <md-button class="md-primary md-raised" @click="onShowVoteDialog">支持该项目</md-button>
            </md-empty-state>
          </div>
          <md-divider></md-divider>
        </div>
        
        <md-table class="vote-table" v-show="voteList.length" @md-selected="showTxDetail">
          <md-table-row>
            <md-table-head>ID</md-table-head>
            <md-table-head>Hash</md-table-head>
            <md-table-head>CreateTime</md-table-head>
            <md-table-head>VoteCount</md-table-head>
          </md-table-row>
          <md-table-row class="vote-table-row" @click="showTxDetail(vote.tx)" v-for="(vote, index) in voteList" :key="vote.tx">
            <md-table-cell>{{index + 1}}</md-table-cell>
            <md-table-cell>{{vote.tx}}</md-table-cell>
            <md-table-cell>{{f(String(vote.createTime))}}</md-table-cell>
            <md-table-cell><span class="token">{{HandleToken(vote.supportCount)}}</span>&nbsp;CDB</md-table-cell>
          </md-table-row>
        </md-table>
      </md-card-content>
    </md-card>
    <md-dialog :md-active.sync="showVoteDialog"  class="tx_dialog">
      <md-dialog-title>发起项目投票交易</md-dialog-title>
      <md-field>
        <label>投票用户</label>
        <md-input v-model="user.sUserName" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field>
        <label>目标账户</label>
        <md-input v-model="userName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>目标项目</label>
        <md-input v-model="projectName" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field>
        <label>投票数/CDB</label>
        <md-input v-model="supportCount"></md-input>
      </md-field>
      
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <md-dialog-actions>
        <md-button class="md-primary" @click="onCancelSupport">取消</md-button>
        <md-button class="md-primary" @click="onConfirmSupport">确认转账</md-button>
      </md-dialog-actions>
    </md-dialog>
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
            <md-table-cell>发款账户</md-table-cell>
            <md-table-cell>{{txInfo.fromUser}}&nbsp;/&nbsp;{{txInfo.fromAddress}}</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>收款账户</md-table-cell>
            <md-table-cell>{{txInfo.toUser}}&nbsp;/&nbsp;{{txInfo.toAddress}}</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>支持项目</md-table-cell>
            <md-table-cell>{{txInfo.toUser}}&nbsp;/&nbsp;{{this.projectName}}</md-table-cell>
          </md-table-row>
          <md-table-row>
            <md-table-cell>支持数量</md-table-cell>
            <md-table-cell>{{HandleToken(txInfo.supportCount)}}&nbsp;CDB</md-table-cell>
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
import { Vue, Component, Prop, Inject } from "vue-property-decorator";
import { getProjectSupportCount, voteProject,getSupportDetail } from "@api/project";
import { Getter, Action } from "vuex-class";
import { UserInterface } from "@/utils/interface";
import { trim, isValidPwd,filterPartTime } from '@utils/index'
import { openLoading, closeLoading } from "@utils/share-variable";

@Component
export default class DetailVote extends Vue {
  @Prop() readonly userName!: string;
  @Prop() readonly projectName!: string;
  @Prop() readonly isOwner!: boolean;
  @Inject("onShowLoginBox") onShowLoginBox: any;


  tokenNumber: any = "loading...";
  txCount: number = 0;
  voteList: Array<Object> = [];

  @Getter user!: UserInterface;
  @Getter isLogin!: boolean;
  @Action("setUserToken") setUserToken!: any;
  f :any = filterPartTime

  // 交易相关
  showVoteDialog: boolean = false;
  password: string = "";
  supportCount: number = 0;

  created() {
    this.fetchSupportCount();
    console.log(this.isLogin, this.user.sUserName, this.userName);
  }

  fetchSupportCount(): void {
    getProjectSupportCount(this.userName, this.projectName)
      .then((data) => {
        this.tokenNumber = data.supportCount;
        this.txCount = data.txCount;
        this.voteList = data.supportList;
        this.voteList = this.voteList.sort(
          (a: any, b: any) =>
            b.supportCount - a.supportCount
        )
        console.log("获取到的token数：", data);
        closeLoading();
      })
      .catch((err) => {
        this.$alert(err);
        closeLoading();
      });
  }

  onShowVoteDialog(): void {
    if (this.isLogin) {
      this.showVoteDialog = true;
    } else {
      this.onShowLoginBox();
    }
  }

  onConfirmSupport(): void {
    if (isNaN(this.supportCount)) {
      return this.$alert("投币数必须是数字且为整数");
    }

    if (this.supportCount <= 0) {
      return this.$alert("投票币数不能小于0");
    }

    if (this.supportCount > 100) {
      return this.$alert("投票币数不能超过100");
    }
    this.password = trim(this.password);
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
  
    openLoading("正在转账中，请耐心等待...");

    voteProject(
      this.userName,
      this.projectName,
      this.supportCount * 100000000,
      this.password
    )
      .then(() => {
        this.showVoteDialog = false;
        closeLoading();
        console.log("投票成功");
        // 更新项目的投票数
        this.fetchSupportCount();
        // 更新登录用户的Token数
        this.setUserToken();
      })
      .catch((err) => {
        this.showVoteDialog = false;
        closeLoading();
        this.$alert(err);
      });
  }
  onCancelSupport(): void {
    this.showVoteDialog = false;
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
</script>

<style>
.add-button {
  position: absolute;
  bottom: 12px;
  right: 16px;
}


</style>
