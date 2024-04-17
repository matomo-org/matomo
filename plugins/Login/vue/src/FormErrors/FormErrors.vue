<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <Notification
    v-if="Object.keys(formErrors || {}).length"
    :noclear="true"
    context="error"
  >
    <span v-for="(data, key) in formErrors" :key="key">
      <strong>{{ translate('General_Error') }}</strong>: <span v-html="$sanitize(data)"/>
      <br/>
    </span>
  </Notification>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Notification } from 'CoreHome';

export default defineComponent({
  props: {
    formErrors: [Array, Object],
  },
  components: {
    Notification,
  },
});
</script>
