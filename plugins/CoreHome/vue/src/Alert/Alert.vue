<template>
  <div
    class="alert"
    :class="{ [`alert-${severity}`]: true }"
  >
    <slot></slot>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    severity: {
      type: String,
      required: true,
    },
  },
});
</script>
