<template>
      <md-dialog-alert
      :md-active.sync="active"
      :md-title="title"
      :md-content="content"
      class="dialog-alert"
       />
</template>

<script lang="ts">
import {Vue, Component, Prop} from 'vue-property-decorator'

@Component
export default class Alert extends Vue {
  @Prop(String) readonly title!: string
  @Prop({default: '操作成功'}) readonly content!: string

  active: boolean = true

  close(): void {
    this.active = false
  }
}
</script>

<style lang="stylus" scoped>
.dialog-alert
  z-index 99
</style>
