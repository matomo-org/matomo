<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fieldArray form-group">
    <div
      class="fieldArrayTable multiple valign-wrapper"
      v-for="(item, index) in modelValue"
      :class="{[`fieldArrayTable${index}`]: true}"
      :key="index"
    >
      <Field
        v-if="field.templateFile"
        class="fieldUiControl"
        :full-width="true"
        :model-value="item"
        :options="field.availableValues"
        @update:modelValue="onEntryChange($event, index)"
        :placeholder="' '"
        :uicontrol="field.uiControl"
        :data-title="field.title"
        :name="`${name}-${index}`"
      >
      </Field>
      <span
        @click="removeEntry(index)"
        class="icon-minus valign"
        v-show="index + 1 !== modelValue.length"
        :title="translate('General_Remove')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, defineAsyncComponent } from 'vue';

// async since this is a a recursive component
const Field = defineAsyncComponent(() => {
  return new Promise((resolve) => {
    window.$(document).ready(() => resolve(window.CorePluginsAdmin.Field));
  });
});

export default defineComponent({
  props: {
    name: String,
    field: Object,
    modelValue: Array,
  },
  components: {
    Field,
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue(newValue) {
      // TODO: does this get called initially?
      // make sure there is always an empty new value
      if (!newValue.length || newValue.pop() !== '') {
        this.$emit('update:modelValue', [...newValue, '']);
      }
    },
  },
  methods: {
    onEntryChange(newValue: unknown, index: number) {
      const newArrayValue = [ ...this.modelValue ];
      newArrayValue[index] = newArrayValue;

      this.$emit('update:modelValue', newArrayValue);
    },
    removeEntry(index) {
      if (index > -1) {
        const newValue = this.modelValue.filter((x, i) => i !== index);
        this.$emit('update:modelValue', newValue);
      }
    },
  },
});
</script>
