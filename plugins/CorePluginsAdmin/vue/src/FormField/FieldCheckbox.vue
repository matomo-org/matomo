<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="checkbox">
    <label>
      <input
        @change="onChange($event)"
        v-bind="uiControlAttributes"
        :value="1"
        :checked="!!modelValue"
        type="checkbox"
        :id="name"
        :name="name"
      />

      <span v-html="$sanitize(title)"/>
    </label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    modelValue: Boolean,
    uiControlAttributes: Object,
    name: String,
    title: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      if (this.modelValue !== (event.target as HTMLInputElement).checked) {
        this.$emit('update:modelValue', (event.target as HTMLInputElement).checked);
      }
    },
  },
});
</script>
