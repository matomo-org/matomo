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
      @change="onChange($event)"
      :value="concattedValues"
      v-bind="uiControlAttributes"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    name: String,
    title: String,
    uiControl: String,
    value: Array,
    uiControlAttributes: Object,
  },
  computed: {
    concattedValues() {
      return this.value.join(', ');
    },
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      const values = (event.target as HTMLInputElement).value.split(',').map((v) => v.trim());
      this.$emit('update:modelValue', values);
    },
  },
});
</script>
