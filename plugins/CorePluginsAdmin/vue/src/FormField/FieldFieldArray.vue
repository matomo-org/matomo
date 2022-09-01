<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <label :for="name" v-html="$sanitize(title)"></label>

    <FieldArray
      :name="name"
      :model-value="modelValue"
      @update:modelValue="onValueUpdate($event)"
      :model-modifiers="modelModifiers"
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
    modelModifiers: Object,
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
