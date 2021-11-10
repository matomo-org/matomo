<template>
  <input
    :class="`control_${uiControl}`"
    :type="uiControl"
    :id="name"
    :name="name"
    :value="value.toString()"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  />
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    uiControl: String,
    name: String,
    title: String,
    value: [Number, String],
    uiControlAttributes: Object,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      const value = parseFloat((event.target as HTMLInputElement).value);
      this.$emit('update:modelValue', value);
    },
  },
});
</script>
