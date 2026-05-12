<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="18"
    height="18"
    viewBox="0 0 18 18"
    aria-hidden="true"
  >
    <path
      d="M0.78125 2H7.78125V16H0.78125L3.00852 9L0.78125 2Z"
      :fill="iconFill"
      stroke="currentColor"
      stroke-width="1.5"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M10.2188 2H17.2188L14.9915 9L17.2188 16H10.2188V2Z"
      :fill="iconFill"
      stroke="currentColor"
      stroke-width="1.5"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'CompareIcon',
  props: {
    state: {
      type: String,
      required: true,
    },
  },
  computed: {
    iconFill(): string {
      return this.state === 'active' ? 'currentColor' : 'transparent';
    },
  },
});
</script>
