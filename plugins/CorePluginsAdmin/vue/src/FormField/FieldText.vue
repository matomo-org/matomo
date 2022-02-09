<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- note: @change is used in case the change event is programmatically triggered -->
  <input
    :class="`control_${uiControl}`"
    :type="uiControl"
    :id="name"
    :name="name"
    :value="modelValueText"
    @keydown="onKeydown($event)"
    @change="onKeydown($event)"
    v-bind="uiControlAttributes"
  />
  <label
    :for="name"
    v-html="$sanitize(title)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { debounce } from 'CoreHome';

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
  computed: {
    modelValueText() {
      if (typeof this.modelValue === 'undefined' || this.modelValue === null) {
        return '';
      }

      return this.modelValue.toString();
    },
  },
  created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = debounce(this.onKeydown.bind(this), 50);
  },
  mounted() {
    setTimeout(() => {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
  },
  methods: {
    onKeydown(event: Event) {
      const newValue = (event.target as HTMLInputElement).value;
      if (this.modelValue !== newValue) {
        this.$emit('update:modelValue', newValue);
      }
    },
  },
});

</script>
