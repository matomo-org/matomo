<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <Notification
    v-if="formErrors?.length"
    :noclear="true"
    context="error"
  >
    <span v-for="(data, index) in formErrors" :key="index">
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
    formErrors: Array,
  },
  components: {
    Notification,
  },
});
</script>
