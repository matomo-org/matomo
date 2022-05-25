<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- note: @change is used in case the change event is programmatically triggered -->
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
      @change="onKeydown($event)"
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
    modelValue: [Array, String],
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      }

      return (this.modelValue || []).join(SEPARATOR);
    },
  },
  created() {
    this.onKeydown = debounce(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown(event: KeyboardEvent) {
      const value = (event.target as HTMLTextAreaElement).value.split(SEPARATOR);
      if (value.join(SEPARATOR) !== this.concattedValue) {
        this.$emit('update:modelValue', value);
      }
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        setTimeout(() => {
          if (this.$refs.textarea) {
            window.Materialize.textareaAutoResize(this.$refs.textarea);
          }
          window.Materialize.updateTextFields();
        });
      }
    },
  },
  mounted() {
    setTimeout(() => {
      if (this.$refs.textarea) {
        window.Materialize.textareaAutoResize(this.$refs.textarea);
      }
      window.Materialize.updateTextFields();
    });
  },
});
</script>
