<template>
  <aside class="aside">
    <div class="return-to-top"
         title="返回顶部">
      <span class="fa fa-arrow-up"
            aria-hidden="true"></span>
    </div>
  </aside>

</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator'
import jquery  from 'jquery'
const $ = jquery

@Component
export default class ReturnToTop extends Vue {
  mounted() {
    $(window).on("scroll", () => {
      if ($(window).scrollTop() >= 1000) {
        $(".return-to-top").fadeIn()
      } else {
        $(".return-to-top").fadeOut()
      }
    })

    $(".return-to-top").on("click", () => {
      $("html, body").animate({ scrollTop: 0 }, 300)
    })
  }
}
</script>

<style lang="stylus" scoped>
.aside {
  position: fixed;
  bottom: 10px;
  right: 10px;
  z-index 999
}

.return-to-top {
  display: none;
  width: 50px;
  height: 50px;
  font-size: 20px;
  line-height: 50px;
  text-align: center;
  border-radius: 5px;
  background-color: #8d8989;
  color: #eee;
  cursor: pointer;
}

.return-to-top:hover {
  background-color: #666;
}
</style>