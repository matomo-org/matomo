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
    :value="modelValueFormatted"
    @keydown="onChange($event)"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  />
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import { debounce } from 'CoreHome';

export default defineComponent({
  props: {
    uiControl: String,
    name: String,
    title: String,
    modelValue: [Number, String],
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created() {
    this.onChange = debounce(this.onChange.bind(this), 50);
  },
  methods: {
    onChange(event: Event) {
      const value = parseFloat((event.target as HTMLInputElement).value);

      this.$emit('update:modelValue', value);

      nextTick(() => {
        if ((event.target as HTMLInputElement).value !== this.modelValueFormatted) {
          // change to previous value if the parent component did not update the model value
          // (done manually because Vue will not notice if a value does NOT change)
          (event.target as HTMLInputElement).value = this.modelValueFormatted;
        }
      });
    },
  },
  mounted() {
    window.Materialize.updateTextFields();
  },
  watch: {
    modelValue() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
  },
  computed: {
    modelValueFormatted() {
      return (this.modelValue || '').toString();
    },
  },
});
</script>
