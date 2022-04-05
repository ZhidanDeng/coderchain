<template>
  <div class="container">
    <md-card>
      <md-card-content>
        <div>
          <h3>在线转账</h3>
          <md-divider></md-divider>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-user"></md-icon>
              </div>
              <md-field>
                <label>
                  发款用户名
                </label>
                <md-input v-model="fromUser" @blur="onFromUserChange"></md-input>
              </md-field>
            </div>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-user"></md-icon>
              </div>
              <md-field>
                <label>
                  收款用户名
                </label>
                <md-input v-model="toUser" @blur="onToUserChange"></md-input>
              </md-field>
            </div>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-lock"></md-icon>
              </div>
              <md-field>
                <label>
                  转账金额/CDB
                </label>
                <md-input v-model="transferCount"></md-input>
              </md-field>
            </div>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-text"></md-icon>
              </div>
              <md-field>
                <label>
                  转账备注
                </label>
                <md-input v-model="remark"></md-input>
              </md-field>
            </div>
          </div>
           <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-lock"></md-icon>
              </div>
              <md-field :md-toggle-password="false">
                <label>请输入您的账户密码</label>
                <md-input v-model="password" type="password"></md-input>
              </md-field>
            </div>
          </div>
          
          <p class="pay-tip"># 手续费0.1个币</p>
        </div>
      </md-card-content>

      <md-card-actions>
        <md-button class="md-raised md-primary"
                   @click="onSave">转账</md-button>
        <md-button class="md-raised"
                   @click="onCancel">重置</md-button>
      </md-card-actions>
    </md-card>
    <md-dialog :md-active.sync="showTransferTxDiaLog"  class="tx_dialog">
      <md-dialog-title>转账交易确认</md-dialog-title>
      <md-field>
        <label>发款账户</label>
        <md-input v-model="fromUser" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>发款地址</label>
        <md-input v-model="fromAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>收款账户</label>
        <md-input v-model="toUser" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>收款地址</label>
        <md-input v-model="toAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>转账备注</label>
        <md-input v-model="remark" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>交易金额/CDB</label>
        <md-input v-model="transferCount" readonly class="readonly_input"></md-input>
      </md-field>
      
      <md-field>
        <label>手续费用/CDB</label>
        <md-input v-model="txFee" readonly class="readonly_input"></md-input>
      </md-field>

      
      
      <md-dialog-actions>
        <md-button class="md-primary" @click="showTransferTxDiaLog = false">取消</md-button>
        <md-button class="md-primary" @click="onConfirmTransfer">确认转账</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script lang="ts">
import { Vue, Component,Inject,Watch } from 'vue-property-decorator'
import { Getter, Mutation,Action } from 'vuex-class'
import { SET_USER } from '@/store/mutation-types'
import { trim, isValidPwd } from '@utils/index'
import { getUserInfoByName,transferByName } from '@api/user'
import {openLoading, closeLoading} from '@utils/share-variable'
import { UserInterface } from '@/utils/interface';
import baseURL from '@utils/api-url';


@Component
export default class Transfer extends Vue {
  
  // 交易相关
  showTransferTxDiaLog: boolean = false;
  password: string = "";
  fromUser: string = "";
  fromAddress: string ="";
  toUser: string = "";
  toAddress:string = "";
  remark: string = "";
  transferCount: number = 1.0;
  txFee: number = 0.1;
  

  @Getter('isLogin') isLogin!: boolean
  @Getter('user') user!: UserInterface
  @Mutation(SET_USER) setUser: any
  @Inject("onShowLoginBox") onShowLoginBox: any;
  @Action('setUserToken') setUserToken: any

  created(): void {
    if(this.isLogin)
    {
      this.fromUser = this.user.sUserName
      this.fromAddress = this.user.sWalletAddress
    }
  }
  checkOutLogin()
  {
    if (!this.isLogin) {
      this.onShowLoginBox()
    }
    else
    {
      return true
    }
  }
  
  onFromUserChange(): void{
    if(this.fromUser === '')
    {
      this.fromAddress = "";
      return
    }
    if(this.isLogin && this.fromAddress === this.user.sWalletAddress && this.fromUser === this.user.sUserName)
    {
      return;
    }
    getUserInfoByName(this.fromUser)
    .then(data => {
      this.fromAddress = data["sWalletAddress"]
    })
  }
  
  onToUserChange(): void{
    if(this.toUser === '')
    {
      this.toAddress = "";
      return
    }
    getUserInfoByName(this.toUser)
    .then(data => {
      this.toAddress = data["sWalletAddress"];
      return;
    })
    
  }
  // 重置按钮
  onCancel(): void{
    if(this.isLogin)
    {
      this.fromUser = this.user.sUserName
      this.fromAddress = this.user.sWalletAddress
      this.password = "";
      this.toUser  = "";
      this.toAddress = "";
      this.remark = "";
      this.transferCount = 1.0;
    }
    else
    {
      this.password = "";
      this.fromUser = "";
      this.fromAddress ="";
      this.toUser  = "";
      this.toAddress = "";
      this.remark = "";
      this.transferCount = 1.0;
    }
  }
  onConfirmTransfer(): void{
    this.onTransfer();
  }
  onSave(): void{
    this.fromUser = trim(this.fromUser);
    this.toUser = trim(this.toUser);
    this.toAddress = trim(this.toAddress);
    this.fromAddress = trim(this.fromAddress);
    this.remark = trim(this.remark);
    this.password = trim(this.password);
    if (this.fromUser === '') {
      return this.$alert('发款用户名不能为空')
    }
    if (this.toUser === '') {
      return this.$alert('发款用户名不能为空')
    }
    if (this.remark === '') {
      return this.$alert('转账备注不能为空')
    }
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }
    if (isNaN(this.transferCount)) {
      return this.$alert("投币数必须是数字且为整数");
    }

    if (this.transferCount <= 0) {
      return this.$alert("投票币数不能小于0");
    }
    
    if(this.toAddress === '' || this.fromAddress === '')
    {
      return this.$alert("用户信息有误，请仔细确认用户是否存在")
    }
    this.showTransferTxDiaLog = true;
  }

  onTransfer(): void{
    const transferInfo = {
      fromUser: this.fromUser,
      toUser: this.toUser,
      password: this.password,
      transferCount: this.transferCount * 100000000,
      remark: this.remark
    }
    openLoading('正在转账')
    transferByName(transferInfo)
      .then(data => {
        this.showTransferTxDiaLog = false;
        this.onCancel();
        closeLoading();
        this.$alert('转账成功');
        if(this.isLogin)
        {
          this.setUserToken()
        }
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
  }
}
</script>

<style lang="stylus" scoped>
.container
  max-width 650px
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

