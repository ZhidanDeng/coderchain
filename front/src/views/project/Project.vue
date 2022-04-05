<template>
  <div class="container project">
    <md-card>
      <md-card-content>
        <md-toolbar md-elevation="0"
                    class="filter-toolbar md-transparent">
          <h3 class="md-title">发现有趣的项目</h3>
          <!-- <div>
        <md-field>
          <label for="movie">筛选项目类型</label>
          <md-select v-model="selectCategory"
                     name="movie"
                     id="category">
            <md-option value>全部</md-option>
            <md-option v-for="category in categoryList"
                       :value="category"
                       :key="category">{{ category }}</md-option>
          </md-select>
        </md-field>
      </div> -->
        </md-toolbar>

        <md-toolbar md-elevation="0"
                    class="md-transparent">
          <md-field md-clearable
                    class="md-toolbar-section-end">
            <md-input placeholder="搜索项目名称..."
                      v-model="search"
                      @input="handleSearchOnTable" />
          </md-field>
        </md-toolbar>

        <div class="clearfix"
             v-show="searchList.length">
          <md-table v-model="pageList"
                    md-sort="createAt"
                    md-sort-order="asc"
                    class="project-list-table">
            <md-table-row slot="md-table-row"
                          slot-scope="{ item }">
              <md-table-cell md-label="[项目排序]"></md-table-cell>
              <md-table-cell md-label="项目名称"
                             md-sort-by="sProjectName"></md-table-cell>
              <md-table-cell md-label="作者名称"
                             md-sort-by="sUserName"></md-table-cell>
              <md-table-cell md-label="创建时间"
                             md-sort-by="createAt">{{ item.createAt }}</md-table-cell>
            </md-table-row>
          </md-table>
        </div>
        <!-- 这里不要用template标签，原因待发现(好像不能跟v-if联用) -->
        <!-- template渲染有点蛋疼，无规律 -->
        <!-- 解决办法 -->
        <!--
      1、用div-v-if/v-show
    <div v-if="searchList.length">
    </div>
     -->

        <!--
      2、用template时，true or false条件都要用到(但是同时用template的条件用的是v-show，初始化两个都会渲染出来，得找个时间好好测试一下template,div,v-if,v-show的异同了)

      template-v-if（后面还有个template要配合上v-if="!condition"，）

      <template v-if="searchList.length">
        v-for
      </template>

    <template v-if="!searchList.length">
      tip
    </template>
     -->
        <div>

          <div v-show="searchList.length">
            <div class="project-content"
                 v-for="project in pageList"
                 :key="project.sId">
              <project-item :project="project"
                            :handleClickWatchChainInfo="handleClickWatchChainInfoAtTime"></project-item>
              <md-divider></md-divider>
            </div>
            <div>
              <ul class="pagination"
                  id="mPageItem"></ul>
            </div>
          </div>
          <!-- 不用template -->
          <div v-show="!searchList.length">
            <div v-show="projectList.length">
              <md-empty-state md-label="找不到项目"
                              :md-description="`找不到关键字包含'${search}'的项目，请尝试换关键词或者创建一个新项目。`">
                <md-button @click="onCreate" class="md-primary md-raised">创建新项目</md-button>
              </md-empty-state>
            </div>
            <div v-show="!projectList.length">
              <md-empty-state md-label="还未有新的项目"
                              :md-description="`创建一个新项目吧`">
                <md-button @click="onCreate" class="md-primary md-raised">创建新项目</md-button>
              </md-empty-state>
            </div>
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
      </md-card-content>
    </md-card>

  </div>
</template>

<script lang="ts">
import { Component, Vue, Watch, Inject, Mixins } from 'vue-property-decorator'
import { getAllProject } from '@api/project'
import { Getter, Mutation } from 'vuex-class'
import { SET_CATEGORY_LIST } from '@/store/mutation-types'
import ProjectItem from '@components/project-item/ProjectItem.vue'
import { ProjectMixin } from '@utils/mixin'
import { openLoading, closeLoading } from '@utils/share-variable'
import { ProjectInterface } from '@/utils/interface'
import { PaginationWithDOM } from '@/utils/pagination'

const toLower = (text: string): string => {
  return text.toString().toLowerCase()
}

const searchByName = (
  items: Array<ProjectInterface>,
  term: string
): Array<ProjectInterface> => {
  if (term) {
    return items.filter(
      (item: any): boolean =>
        toLower(decodeURIComponent(item.sProjectName)).includes(toLower(term))
    )
  }

  return items.map(item => item)
}

@Component({
  props: {},
  components: {
    ProjectItem
  }
})
export default class Project extends Mixins(ProjectMixin) {
  projectList: Array<ProjectInterface> = []
  searchList: Array<ProjectInterface> = []
  pageList: Array<ProjectInterface> = []
  selectCategory: string = ''
  search: string = ''
  $pageInstance: any = null

  @Inject('onShowLoginBox') onShowLoginBox: any
  @Getter('categoryList') categoryList!: Array<string>
  @Mutation(SET_CATEGORY_LIST) setCategoryList: any
  @Getter isLogin!: boolean;

  // watch data
  @Watch('selectCategory')
  onSelectCategoryChange(val: string, oldVal: string) {
    console.log('目录发生改变：', val, oldVal)
  }

  created(): void {
    this.fetchAllProject()
    if (!this.categoryList.length) {
      console.log('列表不存在，获取')
      this.setCategoryList([
      "前端",
      "后台",
      "全栈",
      "运维",
      "区块链",
      "人工智能",
      "其他分类"
    ])

    } else {
      console.log('列表存在啦')
    }
  }
  onCreate(): void {
    if (this.isLogin) {
      this.$router.push('/project/create');
    } else {
      this.onShowLoginBox();
    }
  }

  generatePagination() {
    this.$pageInstance = new PaginationWithDOM(
      {
        contents: this.searchList,
        perPageRecord: 20,
        pageBtnNum: 5
      },
      (list: Array<ProjectInterface>) => {
        console.log('分页触发啦：', list)
        this.pageList = list
        // 回到顶部
        window.scrollTo(0, 0)
      }
    )
  }
  fetchAllProject(): void {
    openLoading('正在努力搜集有趣的项目...')
    getAllProject()
      .then(data => {
        console.log('获取到的项目列表：', data)
        // 按照时间降序，最新创建的项目排在前面
        data = data.sort(
          (a: ProjectInterface, b: ProjectInterface) => b.createAt - a.createAt
        )
        this.projectList = data
        this.searchList = data
        this.generatePagination()
        // 把这个
        closeLoading()
      })
      .catch(err => {
        console.log('出错啦：', err)
        this.$alert(err)
        closeLoading()
      })
  }

  handleSearchOnTable() {
    console.log('this.earch值：', this.search)
    this.searchList = []
    this.searchList = searchByName(this.projectList, this.search)
    this.generatePagination()
  }

  updated() {
    // console.log('页面更新了：', this.searchList.length)
  }
}
</script>

<style>
.project-avatar.md-avatar {
  min-width: auto;
}

.md-menu-content-container {
  background: #fff;
}
.project-list-table {
  float: right;
  max-width: 450px;
}
.project-list-table tbody {
  display: none;
}

.pagination li {
  display: inline-block;
}

.pagination li span {
  display: block;
  padding: 5px 10px;
}

.pagination li span:hover {
  background-color:  #005dd8;
  color: #fff
}
</style>

<style lang="stylus" scoped>
.project
  max-width 900px
  margin 0 auto
.project-content
  margin-bottom 20px
.filter-toolbar
  justify-content space-between
.pagination
  list-style none
  text-align center
</style>