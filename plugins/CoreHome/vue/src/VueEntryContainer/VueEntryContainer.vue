<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <component v-if="componentWrapper" :is="componentWrapper"/>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import Matomo from '../Matomo/Matomo';

export default defineComponent({
  props: {
    html: String,
  },
  mounted() {
    Matomo.helper.compileVueEntryComponents(this.$refs.root as HTMLElement);
  },
  beforeUnmount() {
    Matomo.helper.destroyVueComponent(this.$refs.root as HTMLElement);
  },
  computed: {
    componentWrapper() {
      if (!this.html) {
        return null;
      }

      return markRaw({
        template: this.html,
      });
    },
  },
});
</script>
