<template>
  <div class="container preview">
    <!-- <md-card>
      <md-card-content> -->
    <div class="preview-content">
      <div class="header">
        <h2>{{filename}}</h2>
        <p class="tip"># CoderChain文件预览</p>
        <!-- <md-divider></md-divider> -->
      </div>
      <div class="content">
        <!-- <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=https%3A%2F%2Fcoderchain%2Ecn%3A443%2Fdev%2Fcontroller%2FexViewerController%2Ephp%3FsHash%3DQmadcst3dNyjA5YWGxnhZWLkmXPxqQwhHi1rpS46WtHhTp%26sFileName%3DT2G7%5FSRS%2Dbest%2Edoc&amp;wdStartOn=1" width="476px" height="288px" frameborder="0">这是嵌入 <a target="_blank" href="https://office.com">Microsoft Office</a> 文档，由 <a target="_blank" href="https://office.com/webapps">Office Online</a> 支持。</iframe> -->

        <iframe :src="src"
                border="0"
                frameBorder="no"
                id="content-frame">这是嵌入 <a target="_blank"
             href="https://office.com">Microsoft Office</a> 文档，由 <a target="_blank"
             href="https://office.com/webapps">Office Online</a> 支持。</iframe>
      </div>
    </div>
    <!-- </md-card-content>
    </md-card> -->
  </div>
</template>

<script lang="ts">
import { Vue, Component, Inject } from 'vue-property-decorator'
import {IPFS_URL, BASE_URL} from '@config/url'

@Component
export default class Preview extends Vue {
  hash: any = ''
  filename: any = ''
  isOffice: any = true
  src: string = ''

  created() {
    // 获取hash和文件名
    this.hash = this.$route.query.hash
    this.filename = this.$route.query.filename
    this.isOffice = this.$route.query.isOffice

    if (this.hash == null || this.filename == null) {
      this.$alert('文件预览出错')
      this.$router.push('/')
      return
    }

    // 普通文件
    if (this.isOffice === 'false') {
      console.log('是资源类型')
      this.src = `${IPFS_URL}/${this.hash}`
      return
    }

    // office文件在线预览，不能直接通过ipfs获取，需要后台转换一下
    const resourceUrl = `${BASE_URL}/viewer/Viewer/getPreviewer?sHash=${this.hash}&sFileName=${encodeURIComponent(this.filename)}`
    this.src = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(
      resourceUrl
    )}&amp;wdStartOn=1`
  }
}
</script>

<style lang="stylus" scoped>
.container
  max-width 100vw
.header
  h2
    margin 4px 0
  .tip
    font-size 12px
    margin-bottom 2px
.preview
  position fixed
  top 0
  bottom 0
  width 100vw
  background-color #fff
  z-index 999
.preview-content
  display flex
  flex-direction column
  min-height 100vh
  padding 10px
  .content
    position relative
    flex 1
    iframe
      position absolute
      width 100%
      height 100%
      border none
      outline none
</style>

<style>
.usehover .Embed.WACFrameWord:hover {
  border-color: red !important;
}
</style>