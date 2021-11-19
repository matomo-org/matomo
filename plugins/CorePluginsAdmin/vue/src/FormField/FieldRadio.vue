<template>
  <div>
    <label class="fieldRadioTitle" v-show="title">{{ title }}</label>

    <p
      v-for="radioModel in availableOptions"
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
          :checked="modelValue === radioModel.key"
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

export default defineComponent({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: null,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
    },
  },
});
</script>
