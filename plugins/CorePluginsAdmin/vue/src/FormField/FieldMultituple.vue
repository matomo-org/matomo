<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fieldMultiTuple">
    <label :for="name" v-html="$sanitize(title)"></label>
    <MultiPairField
      :name="name"
      :model-value="modelValue"
      @update:modelValue="onUpdateValue"
      :model-modifiers="modelModifiers"
      :field1="uiControlAttributes.field1"
      :field2="uiControlAttributes.field2"
      :field3="uiControlAttributes.field3"
      :field4="uiControlAttributes.field4"
    >
    </MultiPairField>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MultiPairField } from 'CoreHome';

export default defineComponent({
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  components: {
    MultiPairField,
  },
  emits: ['update:modelValue'],
  methods: {
    onUpdateValue(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
