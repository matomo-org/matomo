<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- fake link/button for tab navigation and visualization -->
  <a tabindex="7"
     :class="{'btn btn-block': showAsButton}"
     href=""
     :title="translate('General_MoreDetails')"
     @click.prevent="$emit('action')"
     @keyup.enter="$emit('action')"
  >{{ label ? label : translate('General_Help') }}</a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    showAsButton: {
      type: Boolean,
      required: false,
      default: false,
    },
    label: {
      type: String,
      required: false,
    },
  },
  emits: ['action'],
});
</script>
