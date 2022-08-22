<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <label class="fieldRadioTitle" v-show="title">{{ title }}</label>

    <p
      v-for="radioModel in (availableOptions || [])"
      :key="radioModel.key"
      class="radio"
    >
      <label>
        <input
          :value="radioModel.key"
          @change="onChange($event)"
          type="radio"
          :id="`${name}${radioModel.key}`"
          :name="name"
          :disabled="radioModel.disabled || disabled"
          v-bind="uiControlAttributes"
          :checked="modelValue === radioModel.key || `${modelValue}` === radioModel.key"
        />

        <span>
          {{ radioModel.value }}

          <span class="form-description" v-show="radioModel.description">
            {{ radioModel.description }}
          </span>
        </span>
      </label>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import AbortableModifiers from './AbortableModifiers';

export default defineComponent({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
        this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
        return;
      }

      const reset = () => {
        // change to previous value so the parent component can determine if this change should
        // go through
        (this.$refs.root as HTMLElement).querySelectorAll('input').forEach((inp, i) => {
          if (!this.availableOptions?.[i]) {
            return;
          }

          const { key } = (this.availableOptions as { key: string }[])[i];
          (inp as HTMLInputElement).checked = this.modelValue === key || `${this.modelValue}` === key;
        });
      };

      const emitEventData = {
        value: (event.target as HTMLInputElement).value,
        abort: () => {
          reset();
        },
      };

      this.$emit('update:modelValue', emitEventData);
    },
  },
});
</script>
