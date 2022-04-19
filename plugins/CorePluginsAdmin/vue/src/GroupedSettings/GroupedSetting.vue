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
import { IScope } from 'angular';
import { Matomo } from 'CoreHome';
import FormField from '../FormField/FormField.vue';
import FieldAngularJsTemplate from '../FormField/FieldAngularJsTemplate.vue';

// TODO: have to use angularjs here until there's an expression evaluating alternative
let conditionScope: IScope;

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

      if (!conditionScope) {
        const $rootScope = Matomo.helper.getAngularDependency('$rootScope');
        conditionScope = $rootScope.$new(true);
      }

      return conditionScope.$eval(condition, this.conditionValues);
    },
  },
  methods: {
    changeValue(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
