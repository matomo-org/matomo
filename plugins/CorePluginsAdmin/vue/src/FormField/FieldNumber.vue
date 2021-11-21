<template>
  <input
    :class="`control_${uiControl}`"
    :type="uiControl"
    :id="name"
    :name="name"
    :value="(modelValue || '').toString()"
    @keydown="onChange($event)"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  />
  <label :for="name" v-html="$sanitize(title)"></label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
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
    },
  },
  mounted() {
    window.Materialize.updateTextFields();
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
});
</script>
