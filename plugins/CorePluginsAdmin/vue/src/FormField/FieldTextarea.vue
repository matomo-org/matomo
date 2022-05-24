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
    :value="modelValue"
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

export default defineComponent({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
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

      // change to previous value so the parent component can determine if this change should
      // go through
      (event.target as HTMLInputElement).value = this.modelValue || '';

      this.$emit('update:modelValue', newValue);
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
