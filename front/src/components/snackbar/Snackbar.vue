<template>
 <md-snackbar :md-position="position" :md-duration="isInfinity ? Infinity : duration" :md-active.sync="active" md-persistent>
      <span>{{ content }}</span>
      <md-button class="md-primary" @click="close">Retry</md-button>
    </md-snackbar>
</template>

<script lang="ts">
import {Vue, Component, Prop} from 'vue-property-decorator'

@Component
export default class Snackbar extends Vue {
  @Prop(String) readonly title!: string
  @Prop({default: '出错'}) readonly content!: string
  @Prop({default: 'center'}) readonly position!: string
  @Prop({default: true}) readonly isInfinity!: boolean
  @Prop({default: 4000}) readonly duration!: number

  active: boolean = true

  close(): void {
    this.active = false
  }
}
</script>
