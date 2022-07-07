<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="showField">
    <FormField
      :model-value="modelValue"
      @update:model-value="changeValue($event)"
      :form-field="settingWithComponent"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
import { create as createMathJs, all as mathJsAll } from 'mathjs/lib/esm/number';
import FormField from '../FormField/FormField.vue';
import FieldAngularJsTemplate from '../FormField/FieldAngularJsTemplate.vue';

const math = createMathJs(mathJsAll);

// support natural equal for strings (or any variable)
math.import(
  {
    // eslint-disable-next-line eqeqeq
    equal: (a: unknown, b: unknown) => a == b,
  },
  {
    override: true,
  },
);

export default defineComponent({
  props: {
    setting: {
      type: Object,
      required: true,
    },
    modelValue: null,
    conditionValues: {
      type: Object,
      required: true,
    },
  },
  components: {
    FormField,
  },
  emits: ['update:modelValue'],
  computed: {
    // bc for angularjs field that uses templateFile
    settingWithComponent() {
      if (this.setting.templateFile) {
        return {
          ...this.setting,
          component: FieldAngularJsTemplate,
        };
      }

      return this.setting;
    },
    showField() {
      const condition = this.setting.condition as string;
      if (!condition) {
        return true;
      }

      return math.evaluate(condition, this.conditionValues);
    },
  },
  methods: {
    changeValue(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
