<template>
  <div>
    <label
      :for="name"
      v-html="$sanitize(title)"
    />
    <input
      :class="`control_${ uiControl }`"
      :type="uiControl"
      :name="name"
      @keydown="onKeydown($event)"
      :value="concattedValues"
      v-bind="uiControlAttributes"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { debounce } from 'CoreHome';

export default defineComponent({
  props: {
    name: String,
    title: String,
    uiControl: String,
    modelValue: Array,
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  computed: {
    concattedValues() {
      return (this.modelValue || []).join(', ');
    },
  },
  emits: ['update:modelValue'],
  created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = debounce(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown(event: Event) {
      const values = (event.target as HTMLInputElement).value.split(',').map((v) => v.trim());
      this.$emit('update:modelValue', values);
    },
  },
});
</script>
