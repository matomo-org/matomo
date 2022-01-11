<template>
  <a :href="link" rel='noreferrer noopener' target='_blank'>
  {{ text }}
  </a>
</template>
<script>
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    keyword: String,
    base: String,
    text: String,
  },
  computed: {
    link() {
      let keyword = '';
      if (this.keyword) {
        keyword = `&mtm_keyword=${this.keyword}&s=${this.keyword}`;
      }
      let base = '';
      if (this.base) {
        base = this.base;
      } else {
        base = 'https://matomo.org/';
      }
      return `${base}?mtm_campaign=App_Help&mtm_source=Matomo_App${keyword}`;
    },
  },

});
</script>
