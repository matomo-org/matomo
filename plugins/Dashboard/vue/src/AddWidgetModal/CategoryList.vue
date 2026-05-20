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
      @mouseover="selectCategory(category)"
      @click="selectCategory(category)"
      @focus="selectCategory(category)"
      @keydown.enter.prevent="selectCategory(category)"
      @keydown.space.prevent="selectCategory(category)"
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
  methods: {
    selectCategory(category: string) {
      this.$emit('update:chosenCategory', category);
    },
  },
});
</script>
