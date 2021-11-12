<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="form-group row"
    v-show="formField.showField"
  >
    <h3
      v-if="formField.introduction"
      class="col s12"
    >
      {{ formField.introduction }}
    </h3>
    <component
      :is="childComponent"
      class="col s12"
      :class="{
        'input-field': formField.uiControl !== 'checkbox' && formField.uiControl !== 'radio',
        'file-field': formField.uiControl === 'file',
        'm6': !formField.fullWidth
      }"
      onload="templateLoaded()"
      v-bind="{ ...formField, ...extraChildComponentParams }"
      :value="modelValue"
      @update:modelValue="onChange($event)"
    >
    </component>
    <div
      class="col s12"
      :class="{ 'm6': !formField.fullWidth }"
    >
      <div
        v-if="showFormHelp"
        class="form-help"
      >
        <div
          v-show="formField.description"
          class="form-description"
        >
          {{ formField.description }}
        </div>
        <span
          class="inline-help"
          v-html="$sanitize(formField.inlineHelp)"
        />
        <span v-show="showDefaultValue">
          <br />
          {{ translate('General_Default') }}:
          <span>{{ formField.defaultValuePretty.substring(0, 50) }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FieldCheckbox from './FieldCheckbox.vue';
import FieldCheckboxArray from './FieldCheckboxArray.vue';
import FieldExpandableSelect from './FieldExpandableSelect.vue';
import FieldFieldArray from './FieldFieldArray.vue';
import FieldFile from './FieldFile.vue';
import FieldHidden from './FieldHidden.vue';
import FieldMultituple from './FieldMultituple.vue';
import FieldNumber from './FieldNumber.vue';
import FieldRadio from './FieldRadio.vue';
import FieldSelect from './FieldSelect.vue';
import FieldSite from './FieldSite.vue';
import FieldText from './FieldText.vue';
import FieldTextarea from './FieldTextarea.vue';
import FieldTextareaArray from './FieldTextareaArray.vue';

/*
4. go through directive JS/controller JS and distribute code
*/

const TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
const CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
const CONTROL_TO_COMPONENT_MAP = {
  checkbox: FieldCheckbox,
  'expandable-select': FieldExpandableSelect,
  'field-array': FieldFieldArray,
  file: FieldFile,
  hidden: FieldHidden,
  multiselect: FieldSelect,
  multituple: FieldMultituple,
  number: FieldNumber,
  radio: FieldRadio,
  select: FieldSelect,
  site: FieldSite,
  text: FieldText,
  textarea: FieldTextarea,
};

export default defineComponent({
  props: {
    modelValue: null,
    formField: Object,
    allSettings: String,
  },
  emits: ['update:modelValue'],
  components: {
    FieldCheckbox,
    FieldCheckboxArray,
    FieldExpandableSelect,
    FieldFieldArray,
    FieldFile,
    FieldHidden,
    FieldMultituple,
    FieldNumber,
    FieldRadio,
    FieldSelect,
    FieldSite,
    FieldText,
    FieldTextarea,
    FieldTextareaArray,
  },
  computed: {
    childComponent() {
      let control = CONTROL_TO_COMPONENT_MAP[this.formField.uiControl];
      if (TEXT_CONTROLS.indexOf(control) !== -1) {
        control = 'FieldText'; // we use same template for text and password both
      }

      if (this.formField.type === 'array' && CONTROLS_SUPPORTING_ARRAY.indexOf(control) !== -1) {
        control = `${control}Array`;
      }

      return control;
    },
    extraChildComponentParams() {
      if (this.formField.uiControl === 'multiselect') {
        return { multiple: true };
      }
      return {};
    },
    showFormHelp() {
      return this.formField.description
        || this.formField.inlineHelp
        || (this.formField.defaultValue
          && this.formField.uiControl != 'checkbox'
          && this.formField.uiControl != 'radio');
    },
    showDefaultValue() {
      return this.formField.defaultValuePretty
        && this.formField.uiControl != 'checkbox'
        && this.formField.uiControl != 'radio';
    },
    onChange(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
