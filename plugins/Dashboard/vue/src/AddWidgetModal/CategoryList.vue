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
      class="category-list-item"
    >
      <button
        type="button"
        class="category-button-item"
        @mouseover="selectCategory(category)"
        @click="selectCategory(category)"
        @focus="selectCategory(category)"
        @keydown.enter.prevent="confirmCategory(category)"
        @keydown.space.prevent="confirmCategory(category)"
      >{{ category }}</button>
    </li>
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
  emits: ['update:chosenCategory', 'confirm'],
  methods: {
    selectCategory(category: string) {
      this.$emit('update:chosenCategory', category);
    },
    // Keyboard-only: selecting via Enter/Space also signals "advance focus to
    // the widgets list", so a keyboard user isn't stranded on the category
    // they just confirmed. Mouse/touch paths intentionally don't emit this.
    confirmCategory(category: string) {
      this.selectCategory(category);
      this.$emit('confirm');
    },
  },
});
</script>
