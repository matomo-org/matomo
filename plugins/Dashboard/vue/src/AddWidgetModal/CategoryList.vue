<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul class="widgetpreview-base widgetpreview-categorylist">
    <li
      v-for="category in categories"
      :key="category"
      :class="{ 'widgetpreview-choosen': category === chosenCategory }"
      role="button"
      tabindex="0"
      @mouseover="$emit('update:chosenCategory', category)"
      @click="$emit('update:chosenCategory', category)"
      @focus="$emit('update:chosenCategory', category)"
      @keydown.enter.prevent="$emit('update:chosenCategory', category)"
      @keydown.space.prevent="$emit('update:chosenCategory', category)"
    >{{ category }}</li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'CategoryList',
  props: {
    categories: {
      type: Array as PropType<string[]>,
      required: true,
    },
    chosenCategory: {
      type: String as PropType<string | null>,
      default: null,
    },
  },
  emits: ['update:chosenCategory'],
});
</script>
