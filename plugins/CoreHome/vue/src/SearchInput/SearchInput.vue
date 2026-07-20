<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="searchInputContainer">
    <span class="icon-search" />
    <input
      class="searchInputField browser-default"
      type="text"
      :value="modelValue"
      :placeholder="resolvedPlaceholder"
      v-bind="$attrs"
      @input="onInput($event)"
    >
    <button
      v-if="showClear && modelValue"
      type="button"
      class="searchInputClear"
      @click="onClear()"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';

export default defineComponent({
  name: 'SearchInput',
  inheritAttrs: false,
  props: {
    modelValue: {
      type: String,
      required: true,
    },
    placeholder: {
      type: String,
      default: '',
    },
    showClear: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue'],
  computed: {
    resolvedPlaceholder() {
      return this.placeholder || translate('General_Search');
    },
  },
  methods: {
    translate,
    onInput(event: Event) {
      this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
    },
    onClear() {
      this.$emit('update:modelValue', '');
    },
  },
});
</script>
