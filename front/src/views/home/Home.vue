<template>
  <div class="container">
    <div class="btn-create-wrapper">
             <!-- <md-button class="md-primary md-raised" @click="onCreate"
        >Create first project</md-button
      > -->

      <div @click="onCreate" class="btn-create-project ">
        <md-icon class="md-size-5x">+</md-icon>
      </div>
    </div>
          <!-- <md-button class="md-primary md-raised" @click="onCreate"
        >Create first project</md-button
      > -->
    <md-empty-state
      md-label="创建你的第一个项目"
      md-description="通过创建项目，你可以上传你的代码和有机会获得别人的认同。"
    >

    </md-empty-state>

    <!-- 最近动态 -->
    <div>
      <md-card>
        <md-card-content>
          <loading :isLoading="loading"></loading>
          <template v-if="!loading">
            <!-- Tab1 用户动态 -->
            <!--
            <h3>用户动态</h3>
            <md-divider></md-divider>
            <md-list>
              <md-list-item v-for="item in userList"
                            :key="item.sId"
                            class="rank-item">
                <div>
                  <md-avatar class="project-avatar">
                    <img :src="baseURL + '/user/User/getImage?sImagePath=' + item.sAvatar"
                         alt="Avatar">
                  </md-avatar>
                  &nbsp;
                  <router-link :to="`/user/${item.sUserName}`">{{ item.sUserName }}</router-link>
                  &nbsp;加入了社区
                </div>
                <p>{{ item.createAt | filterPartTime }}</p>
              </md-list-item>
            </md-list>
           -->

            <h3>项目动态</h3>
            <md-divider></md-divider>
            <md-list>
              <md-list-item
                v-for="item in projectList"
                :key="item.sId"
                class="rank-item"
              >
                <div>
                  <md-avatar class="project-avatar">
                    <img
                      :src="
                        `${baseURL}/user/User/getImage?sImagePath=` +
                          item.sAvatar
                      "
                      alt="Avatar"
                    />
                  </md-avatar>
                  &nbsp;
                  <router-link :to="`/user/${item.sUserName}`">{{
                    item.sUserName
                  }}</router-link>
                  &nbsp;创建了项目
                  <router-link
                    :to="
                      `/detail/code?projectName=${encodeURIComponent(
                        item.sProjectName
                      )}&userName=${item.sUserName}`
                    "
                    >{{ item.sProjectName }}</router-link
                  >
                </div>
                <p>{{ f(String(item.createAt)) }}</p>
              </md-list-item>
            </md-list>
          </template>
        </md-card-content>
      </md-card>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject } from 'vue-property-decorator';
import { openLoading, closeLoading } from '@utils/share-variable';
import { getLatest } from '@api/dynamic';
import Loading from '@components/loading/Loading.vue';
import { Getter } from 'vuex-class';
import baseURL from '@utils/api-url';
import {filterPartTime} from '@utils/index';
@Component({
  components: {
    Loading
  }
})
export default class Home extends Vue {
  userList: Array<object> = [];
  projectList: Array<object> = [];
  baseURL: string = baseURL;
  loading: boolean = false;
  f :any = filterPartTime
  @Getter isLogin!: boolean;

  @Inject('onShowLoginBox') onShowLoginBox: any;

  created(): void {
    this.fetchLatest();
  }

  onCreate(): void {
    if (this.isLogin) {
      this.$router.push('/project/create');
    } else {
      this.onShowLoginBox();
    }
  }

  fetchLatest(): void {
    this.loading = true;
    getLatest()
      .then(data => {
        console.log('getLatest: ', data);
        // const userList = data.arrUser
        // this.userList = userList
        // const projectList = data.arrProject
        // this.projectList = projectList
        // console.log('data: ', data)
        // console.log('user:', userList)
        // console.log('proj:', projectList)
        
        data.sort((a: any, b: any) => {
          return b.createAt - a.createAt
        })
        this.projectList = data.slice(0, 20)
        this.projectList = data;
        this.loading = false;
      })
      .catch(err => {
        console.log('进入error');
        this.$alert(err);
        this.loading = false;
      });
  }
}
</script>

<style>
.rank-item .md-list-item-content {
  flex-wrap: wrap;
  white-space: normal;
}

.btn-create-wrapper {
  text-align: center;
}
.btn-create-project {
  display: inline-block;
  text-align: center;
  border: 1px solid transparent;
  cursor: pointer;
  transition: background-color .3s;
}

.btn-create-project:hover {
  border: 1px solid #eee;

  background-color:#eee;
}
.create-btn:hover {
  background: red;
}
</style>
