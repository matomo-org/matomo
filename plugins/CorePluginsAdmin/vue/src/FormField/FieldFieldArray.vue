<template>
  <div>
    <label :for="name" v-html="$sanitize(title)"></label>

    <FieldArray
      :name="name"
      :model-value="modelValue"
      @update:modelValue="onValueUpdate($event)"
      :field="uiControlAttributes.field"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FieldArray } from 'CoreHome';

export default defineComponent({
  components: {
    FieldArray,
  },
  props: {
    name: String,
    title: String,
    modelValue: null,
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onValueUpdate(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
