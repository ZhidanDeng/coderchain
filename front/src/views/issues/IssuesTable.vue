<template>
  <div>
    <md-table v-model="searched" v-if="isOwner"  @md-selected="onSelect" md-sort="createTime" md-sort-order="asc" md-card md-fixed-header>
      <md-table-toolbar>
        <h1 class="md-title" style="flex:2">{{title}}</h1>
        <md-field md-clearable class="md-toolbar-section-end">
          <md-input placeholder="Search by issues title..." v-model="search" @input="searchOnTable" />
        </md-field>
      </md-table-toolbar>
      <md-table-empty-state
        md-label="No issues found"
        :md-description="`No result found for this query in current tab. Try a different search term or create a new issues.`">
        <md-button class="md-primary md-raised" @click="newIssues">Create New Issues</md-button>
      </md-table-empty-state>

      <md-table-toolbar slot="md-table-alternate-header" slot-scope="{ count }">
        <div class="md-toolbar-section-start">{{ getAlternateLabel(count) }}</div>

        <div class="md-toolbar-section-end">
          <md-button  class="md-icon-button" @click="onBatchDelete">
            <md-icon class="fa fa-trash"></md-icon>
            <md-tooltip md-direction="top">删除</md-tooltip>
          </md-button>
          <md-button class="md-icon-button" @click="showBatchUpdateStatus">
            <md-icon class="fa fa-pencil-square-o"></md-icon>
            <md-tooltip md-direction="top">状态更新</md-tooltip>
          </md-button>
        </div>
      </md-table-toolbar>
      
      <md-table-row
        slot="md-table-row"
        slot-scope="{ item }"
        md-selectable="multiple"
      >
        <md-table-cell md-label="标题" md-sort-by="title"><router-link :to="`/issues/detail?projectName=${encodeURIComponent(projectName)}&userName=${userName}&issueID=${item.issueID}`">{{ item.title }}</router-link></md-table-cell>
        <md-table-cell md-label="类型">{{ typeObject[item.type] }}</md-table-cell>
        <md-table-cell md-label="优先级">{{ levelObject[item.level] }}</md-table-cell>
        <md-table-cell md-label="创建者">{{ item.createUser }}</md-table-cell>
        <md-table-cell md-label="创建时间" md-sort-by="createTime">{{ item.createTime }}</md-table-cell>
      </md-table-row>
    </md-table>
    <md-table v-model="searched" v-else  md-sort="createTime" md-sort-order="asc" md-card md-fixed-header>
      <md-table-toolbar>
        <h1 class="md-title" style="flex:2">{{title}}</h1>
        <md-field md-clearable class="md-toolbar-section-end">
          <md-input placeholder="Search by issues title..." v-model="search" @input="searchOnTable" />
        </md-field>
      </md-table-toolbar>
      <md-table-empty-state
        md-label="No issues found"
        :md-description="`No result found for this query in current tab. Try a different search term or create a new issues.`">
        <md-button class="md-primary md-raised" @click="newIssues">Create New Issues</md-button>
      </md-table-empty-state>
      <md-table-row
        slot="md-table-row"
        slot-scope="{ item }"
      >
        <md-table-cell md-label="标题" md-sort-by="title"><router-link :to="`/issues/detail?projectName=${encodeURIComponent(projectName)}&userName=${userName}&issueID=${item.issueID}`">{{ item.title }}</router-link></md-table-cell>
        <md-table-cell md-label="类型">{{ typeObject[item.type] }}</md-table-cell>
        <md-table-cell md-label="优先级">{{ levelObject[item.level] }}</md-table-cell>
        <md-table-cell md-label="创建者">{{ item.createUser }}</md-table-cell>
        <md-table-cell md-label="创建时间" md-sort-by="createTime">{{ item.createTime }}</md-table-cell>
      </md-table-row>
    </md-table>
    <div>
      <md-dialog-confirm
        :md-active.sync="showBatchDeleteDialog"
        md-title="是否确认批量删除所选issue？"
        md-confirm-text="确认"
        md-cancel-text="取消"
        @md-cancel="showBatchDeleteDialog = false"
        @md-confirm="onBatchDeleteConfirm" />
      
   </div>

   <div>
    <md-dialog :md-active.sync="showBatchUpdateDialog">
      <md-dialog-title>批量更新</md-dialog-title>
      <md-dialog-content>
        <md-field>
          <label for="status">请选择批量更新状态</label>
          <md-select v-model="selectStatus"  id="status">
              <md-option
                v-for="dir in issueStatus"
                :value="statusObject[dir]"
                :key="dir"
              >{{ dir }}</md-option>
              
          </md-select>
      
      </md-field>
      </md-dialog-content>

      <md-dialog-actions>
        <md-button class="md-primary" @click="onBatchUpdateConfirm">Save</md-button>
        <md-button class="md-primary" @click="showBatchUpdateDialog = false">取消</md-button>
        
      </md-dialog-actions>
    </md-dialog>

    
  </div>
  </div>
  
</template>

<script lang="ts">
import { Vue, Component, Prop,Inject ,Watch} from "vue-property-decorator";
import { Getter } from 'vuex-class';
import { IssueInterface } from "@utils/interface";
import {getIssuesList, batchUpdateIssuesStatus, batchDeleteIssues} from "@api/issues"
import { openLoading, closeLoading } from "@utils/share-variable";

@Component
export default class IssuesTable extends Vue {
  @Prop() readonly userName!: string;
  @Prop() readonly projectName!: string;
  @Prop() readonly isOwner!: boolean;
  @Inject('onShowLoginBox') onShowLoginBox: any;
  @Inject('reloadPage') reloadPage: any
  @Getter isLogin!: boolean;
  showBatchUpdateDialog :boolean = false 
  showBatchDeleteDialog :boolean = false 
  
  

  selected: IssueInterface[] = [];
  search: string="";
  searched : IssueInterface[] = [];
  result : IssueInterface[] = [];
  title:string = "";

  selectStatus: number = 0
  issueStatus: Array<string> = [
      "开启的","待办的","进行中","已完成","已拒绝"
  ]
  statusObject: object = {
      "开启的":0,"待办的":1,"进行中":2,"已完成":3,"已拒绝":4
  }

  

  levelObject: object = {
    不指定: -1,
    严重: 0,
    主要: 1,
    次要: 2,
    不重要: 3,
    "-1": "未指定",
    "0": "严重",
    "1": "主要",
    "2": "次要",
    "3": "不重要"
  };

  typeObject: object = {
    未设置: -1,
    bug: 0,
    enhancement: 1,
    feature: 2,
    duplicate: 3,
    invalid: 4,
    question: 5,
    wontfix: 6,
    "-1": "未设置",
    "0": "bug",
    "1": "enhancement",
    "2": "feature",
    "3": "duplicate",
    "4": "invalid",
    "5": "question",
    "6": "wontfix"
  };


  @Watch('search')
  changeSearch(){
    if(this.search == "")
    {
      this.searched = this.result
    }
  }

  tableChange():void{
    let path = this.$route.path
    let status = -1
    if(path == "/issues/list/all")
    {
      this.title = "所有的ISSUES"
      status = -1
    }
    else if(path == "/issues/list/open")
    {
      this.title = "开启的的ISSUES"
      status = 0

    }
    else if(path == "/issues/list/todo")
    {
      this.title = "待办的ISSUES"
      status = 1
    }
    else if(path == "/issues/list/ongoing")
    {
      this.title = "进行中的ISSUES"
      status = 2
    }
    
    else if(path == "/issues/list/completed")
    {
      this.title = "已完成的ISSUES"
      status = 3
    }
    else if(path == "/issues/list/refuse")
    {
      this.title = "已拒绝的ISSUES"
      status = 4
    }
    else
    {
      this.$router.push("/");
    }
    openLoading("正在拉取中")

    
    getIssuesList(this.userName,this.projectName,status)
      .then(data => {
        closeLoading();
        this.searched = data.data
        this.result = data.data
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
  }

  @Watch('$route')
  changeData(){
    this.search = ""
    this.tableChange()
  }
  
  created(): void {
    this.tableChange();  
  }

  toLower (text:any):string {
    return text.toString().toLowerCase()
  }
  searchByName (items:IssueInterface[], term:string) :IssueInterface[] {
    if (term) {
      return items.filter(item => this.toLower(item.title).includes(this.toLower(term)))
    }

    return items
  }


  searchOnTable () {
    this.searched = this.searchByName(this.searched, this.search)
  }

  onBatchDelete():void{
    if(!this.isOwner)
    {
      return this.$alert("操作错误")
    }
    this.showBatchDeleteDialog = true
  }
  showBatchUpdateStatus():void{
    if(!this.isOwner)
    {
      return this.$alert("操作错误")
    }
    this.showBatchUpdateDialog = true
    
  }
  onBatchDeleteConfirm():void{
    if(!this.isOwner)
    {
      return this.$alert("操作错误")
    }
    this.showBatchDeleteDialog = false;
    openLoading("正在删除中")
    let arr :number[] = [];
    this.selected.forEach(element => {
      arr.push(element.issueID)
    });
    batchDeleteIssues(this.projectName,this.userName,JSON.stringify(arr))
      .then(data => {
        closeLoading();
        this.reloadPage()
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });

  }
  onBatchUpdateConfirm():void{
    if(!this.isOwner || !this.selectStatus)
    {
      return this.$alert("操作错误")
    }
    this.showBatchUpdateDialog = false;
    openLoading("正在更新中")
    let arr :number[] = [];
    this.selected.forEach(element => {
      arr.push(element.issueID)
    });
    
    batchUpdateIssuesStatus(this.projectName,this.userName,this.selectStatus,JSON.stringify(arr))
      .then(data => {
        closeLoading();
        this.reloadPage()
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
  }

  

  onSelect(items: IssueInterface[]): void {
    this.selected = items;    
  }
  getAlternateLabel(count: number): string {
    let plural = "";

    if (count > 1) {
      plural = "s";
    }

    return `${count} issue${plural} selected`;
  }

  newIssues():void{
    if(this.isLogin)
    {
      const url = `/issues/add?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}`;
      
        this.$router.push(url)
    }
    else
    {
      this.onShowLoginBox();
    }
    
  }
}
</script>


<style>

</style>