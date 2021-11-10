<template>
  <div>
    <label
      :for="name"
      v-html="$sanitize(title)"
    ></label>
    <textarea
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
    value: Array,
  },
  emits: ['update:modelValue'],
  computed: {
    concattedValue() {
      return this.value.join(SEPARATOR);
    },
  },
  methods: {
    onChange($event) {
      const value = ($event as HTMLTextAreaElement).value.split(SEPARATOR).map(v => v.trim());
      this.$emit('update:modelValue', value);
    },
  },
});
</script>
