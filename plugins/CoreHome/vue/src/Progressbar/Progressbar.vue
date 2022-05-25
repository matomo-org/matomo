<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="progressbar">
    <div class="progress">
      <div
        class="determinate"
        style="width: 0"
        :style="{ width: `${actualProgress}%` }"
      />
    </div>
    <span v-show="!!label">
      <img src="plugins/Morpheus/images/loading-blue.gif" style="margin-right: 3.5px" />
      <span class="label" v-html="$sanitize(label)" />
    </span>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    progress: {
      type: Number,
      required: true,
    },
    label: String,
  },
  computed: {
    actualProgress() {
      if (this.progress > 100) {
        return 100;
      }

      if (this.progress < 0) {
        return 0;
      }

      return this.progress;
    },
  },
});
</script>
