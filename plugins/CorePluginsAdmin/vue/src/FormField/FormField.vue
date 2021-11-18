<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="form-group row"
    v-show="showField"
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
      v-bind="{ ...formField, availableOptions, ...extraChildComponentParams }"
      :value="processedModelValue"
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
          ref="inlineHelp"
        />
        <span v-show="showDefaultValue">
          <br />
          {{ translate('General_Default') }}:
          <span>{{ defaultValuePrettyTruncated }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FieldCheckbox from './FieldCheckbox.vue';
import FieldCheckboxArray from './FieldCheckboxArray.vue';
import FieldExpandableSelect, {
  getAvailableOptions as getExpandableSelectAvailableOptions,
} from './FieldExpandableSelect.vue';
import FieldFieldArray from './FieldFieldArray.vue';
import FieldFile from './FieldFile.vue';
import FieldHidden from './FieldHidden.vue';
import FieldMultituple from './FieldMultituple.vue';
import FieldNumber from './FieldNumber.vue';
import FieldRadio from './FieldRadio.vue';
import FieldSelect, {
  getAvailableOptions as getSelectAvailableOptions,
} from './FieldSelect.vue';
import FieldSite from './FieldSite.vue';
import FieldText from './FieldText.vue';
import FieldTextarea from './FieldTextarea.vue';
import FieldTextareaArray from './FieldTextareaArray.vue';
import { processCheckboxAndRadioAvailableValues } from './utilities';

/*
4. go through directive JS/controller JS and distribute code
5. template here
6. other code here
7. all in source TODO that is for code
8. get to build
9. test the shit out of it.
*/

const TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
const CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
const CONTROL_TO_COMPONENT_MAP = {
  checkbox: 'FieldCheckbox',
  'expandable-select': 'FieldExpandableSelect',
  'field-array': 'FieldFieldArray',
  file: 'FieldFile',
  hidden: 'FieldHidden',
  multiselect: 'FieldSelect',
  multituple: 'FieldMultituple',
  number: 'FieldNumber',
  radio: 'FieldRadio',
  select: 'FieldSelect',
  site: 'FieldSite',
  text: 'FieldText',
  textarea: 'FieldTextarea',
};

const CONTROL_TO_AVAILABLE_OPTION_PROCESSOR = {
  FieldSelect: getSelectAvailableOptions,
  FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
  FieldRadio: processCheckboxAndRadioAvailableValues,
  FieldExpandableSelect: getExpandableSelectAvailableOptions,
};

interface Setting {
  name: string;
  value: unknown;
}

export default defineComponent({
  props: {
    modelValue: null,
    formField: {
      type: Object,
      required: true,
    },
    allSettings: [Object, Array],
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
  watch: {
    'formField.inlineHelp': {
      handler(newValue) {
        const field = this.formField;

        let toAppend: HTMLElement|string;

        if (typeof newValue === 'string' && field.inlineHelp && field.inlineHelp.indexOf('#') === 0) {
          toAppend = window.$(field.inlineHelp);
        } else {
          toAppend = window.vueSanitize(field.inlineHelp);
        }

        window.$(this.$refs.inlineHelp).html('').append(toAppend);
        // TODO: used to have $timeout here
      },
    },
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
          && this.formField.uiControl !== 'checkbox'
          && this.formField.uiControl !== 'radio');
    },
    showDefaultValue() {
      return this.formField.defaultValuePretty
        && this.formField.uiControl !== 'checkbox'
        && this.formField.uiControl !== 'radio';
    },
    showField() {
      if (!this.formField.condition
        || !this.allSettings
        || !Object.values(this.allSettings).length
      ) {
        return true;
      }

      const values = {};
      Object.values(this.allSettings as Record<string, Setting>).forEach((setting) => {
        if (setting.value === '0') {
          values[setting.name] = 0;
        } else {
          values[setting.name] = setting.value;
        }
      });

      return this.formField.condition(values); // TODO: condition shouldn't be on formField, but...
    },
    processedModelValue() {
      const field = this.formField;

      // convert boolean values since angular 1.6 uses strict equals when determining if a model
      // value matches the ng-value of an input.
      if (field.type === 'boolean') {
        const valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0';

        // for checkboxes, the value MUST be either true or faluse
        if (field.uiControl === 'checkbox') {
          return valueIsTruthy;
        }

        if (field.uiControl === 'radio') {
          return valueIsTruthy ? '1' : '0';
        }
      }

      return this.modelValue;
    },
    defaultValue() {
      let { defaultValue } = this.formField;
      if (Array.isArray(defaultValue)) {
        defaultValue = defaultValue.join(',');
      }
      return defaultValue;
    },
    // TODO: availableOptions is assumed to be an array here? make the change everywhere.
    availableOptions() {
      const { childComponent, formField } = this;

      if (!formField.availableValues
        || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]
      ) {
        return null;
      }

      return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](
        formField.availableValues,
        formField.type,
        formField.uiControlAttributes,
      );
    },
    defaultValuePretty() {
      let { defaultValue } = this;
      const { availableOptions } = this;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        let defaultParsed = null;
        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {
          // invalid JSON
        }

        // TODO: additional check for null + typeof !== object'
        if (defaultParsed !== null && typeof defaultParsed === 'object') {
          return '';
        }
      }

      // TODO: change all instanceof Array to Array.isArray
      if (!Array.isArray(availableOptions)) {
        if (Array.isArray(defaultValue)) {
          return '';
        }

        return defaultValue ? defaultValue.toString() : '';
      }

      const prettyValues = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      Object.values(availableOptions).forEach((value) => {
        if (defaultValue.indexOf(value.key) !== -1 && typeof value.value !== 'undefined') {
          prettyValues.push(value.value);
        }
      });

      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    },
  },
  methods: {
    onChange(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
