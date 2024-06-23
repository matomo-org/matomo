<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="showField">
    <FormField
      :model-value="modelValue"
      @update:model-value="changeValue($event)"
      :form-field="setting"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FormField from '../FormField/FormField.vue';
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
