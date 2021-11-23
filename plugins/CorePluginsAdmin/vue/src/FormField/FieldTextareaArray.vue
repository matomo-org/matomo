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
import { debounce } from 'CoreHome';

const SEPARATOR = '\n';

export default defineComponent({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue() {
      return (this.modelValue || []).join(SEPARATOR);
    },
  },
  created() {
    this.onKeydown = debounce(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown(event) {
      const value = (event.target as HTMLTextAreaElement).value.split(SEPARATOR);
      this.$emit('update:modelValue', value);
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
