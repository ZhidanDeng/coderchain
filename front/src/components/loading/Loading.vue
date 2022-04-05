<template>
  <div v-if="isLoading" class="loading">
    <md-progress-spinner class="md-accent"
                         :md-stroke="3"
                         md-mode="indeterminate"></md-progress-spinner>
    <p>{{ text }}</p>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator'

@Component
export default class Loading extends Vue {
  @Prop({ default: 'Loading...' }) readonly text!: string
  @Prop(Boolean) readonly isLoading!: boolean
}
</script>

<style lang="stylus" scoped>
  .loading
    padding 10px 0
    text-align center
</style>

