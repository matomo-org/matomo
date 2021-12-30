<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="form-group row matomo-form-field"
    v-show="showField"
  >
    <h3
      v-if="formField.introduction"
      class="col s12"
    >
      {{ formField.introduction }}
    </h3>
    <div
      class="col s12"
      :class="{
        'input-field': formField.uiControl !== 'checkbox' && formField.uiControl !== 'radio',
        'file-field': formField.uiControl === 'file',
        'm6': !formField.fullWidth,
      }"
    >
      <component
        :is="childComponent"
        v-bind="{
          formField,
          ...formField,
          modelValue: processedModelValue,
          availableOptions,
          ...extraChildComponentParams,
        }"
        @update:modelValue="onChange($event)"
      >
      </component>
    </div>
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
          v-if="formField.inlineHelp"
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
import {
  defineComponent,
  onMounted,
  ref,
  watch,
} from 'vue';
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
import FieldTextArray from './FieldTextArray.vue';
import FieldTextarea from './FieldTextarea.vue';
import FieldTextareaArray from './FieldTextareaArray.vue';
import { processCheckboxAndRadioAvailableValues } from './utilities';

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

export default defineComponent({
  props: {
    modelValue: null,
    formField: {
      type: Object,
      required: true,
    },
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
    FieldTextArray,
    FieldTextarea,
    FieldTextareaArray,
  },
  setup(props) {
    const inlineHelpNode = ref(null);

    const setInlineHelp = (newVal) => {
      let toAppend: HTMLElement|string;

      if (!newVal) {
        return;
      }

      if (typeof newVal === 'string' && newVal && newVal.indexOf('#') === 0) {
        toAppend = window.$(newVal);
      } else {
        toAppend = window.vueSanitize(newVal);
      }

      window.$(inlineHelpNode.value).html('').append(toAppend);
    };

    watch(() => props.formField.inlineHelp, setInlineHelp);

    onMounted(() => {
      setInlineHelp(props.formField.inlineHelp);
    });

    return {
      inlineHelp: inlineHelpNode,
    };
  },
  computed: {
    childComponent() {
      if (this.formField.component) {
        return this.formField.component;
      }

      const { uiControl } = this.formField;

      let control = CONTROL_TO_COMPONENT_MAP[uiControl];
      if (TEXT_CONTROLS.indexOf(uiControl) !== -1) {
        control = 'FieldText'; // we use same template for text and password both
      }

      if (this.formField.type === 'array' && CONTROLS_SUPPORTING_ARRAY.indexOf(uiControl) !== -1) {
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
        || this.showDefaultValue;
    },
    showDefaultValue() {
      return this.defaultValuePretty
        && this.formField.uiControl !== 'checkbox'
        && this.formField.uiControl !== 'radio';
    },
    showField() {
      if (!this.formField
        || !this.formField.condition
      ) {
        return true;
      }

      return this.formField.condition();
    },
    processedModelValue() {
      const field = this.formField;

      // convert boolean values since angular 1.6 uses strict equals when determining if a model
      // value matches the ng-value of an input.
      if (field.type === 'boolean') {
        const valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0';

        // for checkboxes, the value MUST be either true or false
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
      let { defaultValue } = this.formField;
      const { availableOptions } = this;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        let defaultParsed = null;
        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {
          // invalid JSON
        }

        if (defaultParsed !== null && typeof defaultParsed === 'object') {
          return '';
        }
      }

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

      (availableOptions || []).forEach((value) => {
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
