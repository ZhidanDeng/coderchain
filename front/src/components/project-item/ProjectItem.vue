<template>
  <md-card class="no-shadow">
    <!-- md-with-hover -->
    <md-ripple>
      <md-card-header>
        <div class="md-title">
          <router-link :to="`/detail/code?projectName=${encodeURIComponent(project.sProjectName)}&userName=${project.sUserName}`">
            <md-icon class="fa fa-inbox"></md-icon>{{ project.sProjectName | decodeURIComponent}}
          </router-link>
        </div>
        <div class="md-subhead">CREATE AT {{String(project.createAt) | filterFullTime}}</div>
      </md-card-header>

      <md-card-content>{{ project.sDescription }}</md-card-content>

      <div class="display-flex project-bottom">
        <div class="project-create-time">
          <router-link :to="'/user/' + project.sUserName">
            <md-avatar md-menu-trigger
                       class="project-avatar">
              <img :src="baseURL + '/user/User/getImage?sImagePath=' + project.sAvatar"
                   alt="Avatar">
            </md-avatar>
            {{ project.sUserName }}
          </router-link>
        </div>
        <md-card-actions>
          <md-button @click="handleClickWatchChainInfo(project)">查看链上信息</md-button>
          <md-button class="md-primary"
                     :to="`/detail/code?projectName=${encodeURIComponent(project.sProjectName)}&userName=${project.sUserName}`">
            查看项目
          </md-button>
        </md-card-actions>
      </div>
    </md-ripple>
  </md-card>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator'
import baseURL from '@utils/api-url'

@Component
export default class ProjectItem extends Vue {
  @Prop() readonly project!: object
  @Prop() readonly handleClickWatchChainInfo!: any

  baseURL: string = baseURL
}
</script>


<style>
.project-avatar.md-avatar {
  min-width: auto;
}
</style>

<style lang="stylus" scoped>
.project
  max-width 900px
  margin 0 auto
.project-content
  margin-bottom 20px
.project-list
  display flex
  flex-flow row wrap
.project-avatar
  float none
  border 1px solid #2c3e50
  margin-right 10px
  width 20px
  height 20px
.project-bottom
  justify-content space-between
  align-items center
.project-create-time
  padding-left 16px
.filter-toolbar
  justify-content space-between
</style>
