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
      @change="onChange($event)"
      class="materialize-textarea"
    ></textarea>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

const SEPARATOR = '&#10;';

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
      return this.modelValue.join(SEPARATOR);
    },
  },
  methods: {
    onChange($event) {
      const value = ($event as HTMLTextAreaElement).value.split(SEPARATOR).map((v) => v.trim());
      this.$emit('update:modelValue', value);
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
