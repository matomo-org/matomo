<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <a tabindex="7"
     class="plugin-details"
     href="#"
     v-plugin-name="{ pluginName }"
     :title="translate('General_MoreDetails')"
  >{{ translate('General_Help') }}</a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { PluginName } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    pluginName: {
      type: String,
      required: true,
    },
  },
  directives: {
    PluginName,
  },
});
</script>
