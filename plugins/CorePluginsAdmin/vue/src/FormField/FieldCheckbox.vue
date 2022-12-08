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
        :checked="isChecked"
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
import AbortableModifiers from './AbortableModifiers';

export default defineComponent({
  props: {
    modelValue: [Boolean, Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object,
    name: String,
    title: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      const newValue = (event.target as HTMLInputElement).checked;
      if (this.modelValue !== newValue) {
        if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        const emitEventData = {
          value: newValue,
          abort() {
            (event.target as HTMLInputElement).checked = !newValue;
          },
        };

        this.$emit('update:modelValue', emitEventData);
      }
    },
  },
  computed: {
    isChecked() {
      return !!this.modelValue && this.modelValue !== '0';
    },
  },
});
</script>
