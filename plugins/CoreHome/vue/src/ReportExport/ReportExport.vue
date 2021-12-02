<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    reportTitle: String,
    requestParams: String,
    reportFormats: String,
    apiMethod: String,
    maxFilterLimit: String,
  },
});
</script>
