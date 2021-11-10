<template>
  <div>
    <input
      :type="uiControl"
      :name="name"
      :value="value"
      @change="onChange($event)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    value: null,
    uiControl: String,
    name: String,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
    },
  },
});
</script>
