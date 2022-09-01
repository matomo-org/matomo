<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- note: @change is used in case the change event is programmatically triggered -->
  <textarea
    :name="name"
    v-bind="uiControlAttributes"
    :id="name"
    :value="modelValueText"
    @keydown="onKeydown($event)"
    @change="onKeydown($event)"
    class="materialize-textarea"
    ref="textarea"
  ></textarea>
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { debounce } from 'CoreHome';
import AbortableModifiers from './AbortableModifiers';

export default defineComponent({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    modelModifiers: Object,
    title: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created() {
    this.onKeydown = debounce(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown(event: Event) {
      const newValue = (event.target as HTMLTextAreaElement).value;
      if (newValue !== this.modelValue) {
        if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        const emitEventData = {
          value: newValue,
          abort: () => {
            if ((event.target as HTMLInputElement).value !== this.modelValue) {
              (event.target as HTMLInputElement).value = this.modelValueText;
            }
          },
        };

        this.$emit('update:modelValue', emitEventData);
      }
    },
  },
  computed: {
    modelValueText() {
      return this.modelValue || '';
    },
  },
  watch: {
    modelValue() {
      setTimeout(() => {
        window.Materialize.textareaAutoResize(this.$refs.textarea);
        window.Materialize.updateTextFields();
      });
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
