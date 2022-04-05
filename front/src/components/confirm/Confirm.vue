<template>
<md-dialog-confirm
      :md-active="active"
      :md-title="title"
      :md-content="content"
      :md-confirm-text="confirmText"
      :md-cancel-text="cancelText"
      @md-cancel="onCancel"
      @md-confirm="onConfirm" />
</template>

<script lang="ts">
import {Vue, Component, Prop} from 'vue-property-decorator'

@Component
export default class Alert extends Vue {
  @Prop({default: '确认操作'}) readonly title!: string
  @Prop({default: '确认进行下一步操作吗'}) readonly content!: string
  @Prop({default: '我已知晓'}) readonly confirmText!: string
  @Prop({default: '取消'}) readonly cancelText!: string

  @Prop() readonly onA!: any
  @Prop() readonly onB!: any

  active: boolean = true

  close(): void {
    this.active = false
  }

  onConfirm(): void {
    this.$emit('confirm')
    this.close()
  }

  onCancel(): void {
    this.$emit('cancel')
    this.close()
  }
}
</script>

<style>

</style>
