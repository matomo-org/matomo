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
import FormField from '../FormField/FormField.vue';
import FieldAngularJsTemplate from '../FormField/FieldAngularJsTemplate.vue';
import expressions from '../expressions';

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
      let condition = this.setting.condition as string;
      if (!condition) {
        return true;
      }

      // math.js does not currently support &&/||/! (https://github.com/josdejong/mathjs/issues/844)
      condition = condition.replace(/&&/g, ' and ');
      condition = condition.replace(/\|\|/g, ' or ');
      condition = condition.replace(/!/g, ' not ');

      try {
        return expressions.evaluate(condition, this.conditionValues);
      } catch (e) {
        console.log(`failed to parse setting condition '${condition}': ${e.message}`);
        console.log(this.conditionValues);
        return false;
      }
    },
  },
  methods: {
    changeValue(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
