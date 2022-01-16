<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <FormField
      :model-value="modelValue"
      @update:model-value="changeValue($event)"
      :form-field="{
        ...setting,
        condition: conditionFunction,
      }"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { IScope } from 'angular';
import { Matomo } from 'CoreHome';
import FormField from '../FormField/FormField.vue';

// TODO: have to use angularjs here until there's an expression evaluating alternative
let conditionScope: IScope;

export default defineComponent({
  props: {
    pluginName: {
      type: String,
      required: true,
    },
    setting: {
      type: Object,
      required: true,
    },
    modelValue: null,
    settingValues: Object,
  },
  components: {
    FormField,
  },
  emits: ['update:modelValue'],
  computed: {
    conditionFunction() {
      const condition = this.setting.condition as string;
      if (!condition) {
        return undefined;
      }

      return () => {
        if (!conditionScope) {
          const $rootScope = Matomo.helper.getAngularDependency('$rootScope');
          conditionScope = $rootScope.$new(true);
        }

        return conditionScope.$eval(condition, this.conditionValues);
      };
    },
    conditionValues() {
      const values: Record<string, unknown> = {};
      Object.entries(this.settingValues as Record<string, unknown>).forEach(([key, value]) => {
        const [pluginName, settingName] = key.split('.');
        if (pluginName !== this.pluginName) {
          return;
        }

        values[settingName] = value;
      });
      return values;
    },
  },
  methods: {
    changeValue(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
