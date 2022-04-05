<template>
    <div class="container">
        <md-tabs md-alignment="left" md-sync-route>
            
            <md-tab id="tab-all" md-label="所有" :to="`/issues/list/all?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
                
            </md-tab>
            <md-tab id="tab-open" md-label="开启的" :to="`/issues/list/open?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
                
            </md-tab>
            <md-tab id="tab-todo" md-label="待办的" :to="`/issues/list/todo?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
                
            </md-tab>
            <md-tab id="tab-ongoing" md-label="进行中" :to="`/issues/list/ongoing?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
               
            </md-tab>
            <md-tab id="tab-completed" md-label="已完成" :to="`/issues/list/completed?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">`
                
            </md-tab>
            <md-tab id="tab-refuse" md-label="已拒绝"  :to="`/issues/list/refuse?projectName=${encodeURIComponent(projectName)}&userName=${userName}`">
                
            </md-tab>
        
        </md-tabs>
        
        
        <keep-alive>
          <router-view :userName="userName"
                       :projectName="projectName"
                       :isOwner="isOwner"
                       
                        />
        </keep-alive>
        
  </div>

</template>


<script lang="ts">

import { Vue, Component, Prop, Watch,Inject } from "vue-property-decorator";
import { openLoading, closeLoading } from "@utils/share-variable";

import { Getter} from "vuex-class";

@Component
export default class IssuesList extends Vue {
  @Prop() readonly userName!: string;
  @Prop() readonly projectName!: string;
  @Prop() readonly isOwner!: boolean;
  @Getter isLogin!: boolean;
  @Inject('onShowLoginBox') onShowLoginBox: any;
  
} 
</script>

<style>

</style>