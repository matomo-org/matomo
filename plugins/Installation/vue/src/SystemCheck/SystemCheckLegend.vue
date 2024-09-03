<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="system-check-legend">
    <h2>{{ translate('Installation_Legend') }}</h2>

    <p>
      <span class="icon-ok"></span>
      {{ translate('General_Ok') }}
    </p>
    <p>
      <span class="icon-warning"></span>
      {{ translate('General_Warning') }}: {{ translate('Installation_SystemCheckWarning') }}
    </p>
    <p>
      <span class="icon-error"></span>
      {{ translate('General_Error') }}: {{ translate('Installation_SystemCheckError') }}
    </p>
  </div>

  <p class="next-step">
    <a :href="url">{{ translate('General_RefreshPage') }} &raquo;</a>
  </p>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    url: {
      type: String,
      required: true,
    },
  },
});
</script>
