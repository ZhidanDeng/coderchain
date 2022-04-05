<template>
  <div v-if="isLoading"
       class="loading">
    <div class="md-overlay md-fixed md-dialog-overlay"></div>
    <div class="progress">
      <md-progress-spinner class="md-primary"
                           :md-stroke="3"
                           md-mode="indeterminate"></md-progress-spinner>
      <p>{{ content }}</p>
    </div>

  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator'

@Component
export default class FullLoading extends Vue {
  @Prop({ default: 'Loading...' }) readonly content!: string

  isLoading: boolean = true
  
  
  close(): void {
    this.isLoading = false
    this.$emit('remove')
  }
}
</script>

<style lang="stylus" scoped>
.md-overlay
  background rgba(255, 255, 255, 0.9)!important

.loading
  position fixed
  top 0
  left 0
  width 100vw
  height 100vh
  display flex
  align-items center
  justify-content center
  z-index 99
  color #448aff
  text-align center
  .progress
    z-index 100
</style>

