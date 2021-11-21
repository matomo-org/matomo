<template>
  <div>
    <label
      :for="name"
      v-html="$sanitize(title)"
    ></label>
    <textarea
      ref="textarea"
      :name="name"
      v-bind="uiControlAttributes"
      :value="concattedValue"
      @keydown="onKeydown($event)"
      class="materialize-textarea"
    ></textarea>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

const SEPARATOR = '\n';

export default defineComponent({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: Array,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue() {
      return (this.modelValue || []).join(SEPARATOR);
    },
  },
  methods: {
    onKeydown(event) {
      setTimeout(() => {
        const value = (event.target as HTMLTextAreaElement).value
          .split(SEPARATOR).map((v) => v.trim());
        this.$emit('update:modelValue', value);
      });
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        setTimeout(() => {
          window.Materialize.textareaAutoResize(this.$refs.textarea);
          window.Materialize.updateTextFields();
        });
      }
    },
  },
  mounted() {
    setTimeout(() => {
      window.Materialize.textareaAutoResize(this.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  },
});
</script>
