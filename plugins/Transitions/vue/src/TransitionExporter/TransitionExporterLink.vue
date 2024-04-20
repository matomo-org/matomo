<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <a class="dataTableAction" v-transition-exporter>
    <span class="icon-export"></span>
  </a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import TransitionExporter from './TransitionExporter';

export default defineComponent({
  directives: {
    TransitionExporter,
  },
});
</script>
