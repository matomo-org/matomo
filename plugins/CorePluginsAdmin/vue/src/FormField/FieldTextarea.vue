<template>
  <textarea
    :name="name"
    v-bind="uiControlAttributes"
    :id="name"
    :value="value"
    @change="onChange($event)"
    class="materialize-textarea"
  ></textarea>
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    name: String,
    uiControlAttributes: Object,
    value: String,
    title: String,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      this.$emit('update:modelValue', (event as HTMLTextAreaElement).value);
    },
  },
});
</script>
