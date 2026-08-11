<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="mtm-searchInput">
    <span class="mtm-searchInput__icon">
      <span class="icon-search" />
    </span>
    <input
      ref="input"
      class="mtm-searchInput__input browser-default"
      type="text"
      v-model="searchValue"
      :placeholder="resolvedPlaceholder"
      v-bind="$attrs"
      v-focus-if="{ focused }"
    >
    <button
      v-if="showClear && modelValue"
      type="button"
      class="mtm-searchInput__clear"
      @click="onClear()"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FocusIf from '../FocusIf/FocusIf';
import { translate } from '../translate';

export default defineComponent({
  name: 'SearchInput',
  // keep false so a parent's attrs and listeners reach the input rather than the wrapper
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
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue'],
  directives: {
    FocusIf,
  },
  computed: {
    // v-model, not :value + @input, so Vue's IME composition handling applies
    searchValue: {
      get(): string {
        return this.modelValue;
      },
      set(value: string) {
        this.$emit('update:modelValue', value);
      },
    },
    resolvedPlaceholder(): string {
      return this.placeholder || translate('General_Search');
    },
  },
  methods: {
    translate,
    onClear() {
      this.$emit('update:modelValue', '');
    },
    blur() {
      (this.$refs.input as HTMLInputElement | undefined)?.blur();
    },
  },
});
</script>
