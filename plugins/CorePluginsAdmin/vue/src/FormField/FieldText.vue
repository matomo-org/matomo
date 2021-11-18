<template>
  <input
    :class="`control_${uiControl}`"
    :type="uiControl"
    :id="name"
    :name="name"
    :value="value"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  />
  <label
    :for="name"
    v-html="$sanitize(title)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    title: String,
    name: String,
    uiControlAttributes: Object,
    value: String,
    uiControl: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  mounted() {
    window.Materialize.updateTextFields();
  },
  watch: {
    modelValue(newVal, oldVal) { // TODO: double check if newVal !== oldVal is needed
      if (newVal !== oldVal) {
        // TODO: removed $timeout
        setTimeout(() => {
          window.Materialize.updateTextFields();
        });
      }
    },
  },
  methods: {
    onChange(event: Event) {
      this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
    },
  },
});

</script>
