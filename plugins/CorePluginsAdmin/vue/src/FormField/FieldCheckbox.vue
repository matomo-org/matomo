<template>
  <div class="checkbox">
    <label>
      <input
        @change="onChange()"
        v-bind="uiControlAttributes"
        :value="1"
        :checked="!!modelValue"
        type="checkbox"
        :id="name"
        :name="name"
      />

      <span v-html="$sanitize(title)"/>
    </label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    modelValue: null,
    uiControlAttributes: Object,
    value: null,
    name: String,
    title: String,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(event: Event) {
      if (this.modelValue !== event.target.checked) {
        this.$emit('update:modelValue', event.target.checked);
      }
    },
  },
});
</script>
