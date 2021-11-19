<template>
  <textarea
    :name="name"
    v-bind="uiControlAttributes"
    :id="name"
    :value="modelValue"
    @keydown="onKeydown($event)"
    class="materialize-textarea"
    ref="textarea"
  ></textarea>
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    title: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onKeydown(event: Event) {
      setTimeout(() => {
        this.$emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
      });
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        // TODO: removed a $timeout
        // TODO: does this happen multiple times initially
        setTimeout(() => {
          window.Materialize.textareaAutoResize(this.$refs.textarea);
          window.Materialize.updateTextFields();
        });
      }
    },
  },
  mounted() {
    window.Materialize.textareaAutoResize(this.$refs.textarea);
    window.Materialize.updateTextFields();
  },
});
</script>
