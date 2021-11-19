<template>
  <div>
    <input
      :type="uiControl"
      :name="name"
      :value="modelValue"
      @change="onChange($event)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    modelValue: null,
    uiControl: String,
    name: String,
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
