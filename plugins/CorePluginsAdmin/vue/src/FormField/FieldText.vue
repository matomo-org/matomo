<template>
  <input
    :class="`control_${uiControl}`"
    :type="uiControl"
    :id="name"
    :name="name"
    :value="modelValue.toString()"
    @keydown="onKeydown($event)"
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
    modelValue: [String, Number],
    uiControl: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  mounted() {
    setTimeout(() => {
      window.Materialize.updateTextFields();
    });
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
    onKeydown(event: Event) {
      setTimeout(() => {
        this.$emit('update:modelValue', (event.target as HTMLInputElement).value);
      });
    },
  },
});

</script>
