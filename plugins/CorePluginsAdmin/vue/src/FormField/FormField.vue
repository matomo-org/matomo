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
          modelModifiers,
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
          v-if="formField.inlineHelp || hasInlineHelpSlot"
        >
          <component
            v-if="inlineHelpComponent"
            :is="inlineHelpComponent"
            v-bind="inlineHelpBind"
          />

          <slot name="inline-help"></slot>
        </span>
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
  Component,
  markRaw,
} from 'vue';
import { useExternalPluginComponent } from 'CoreHome';
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
import FieldAngularJsTemplate from './FieldAngularJsTemplate.vue';

const TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
const CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
const CONTROL_TO_COMPONENT_MAP: Record<string, string> = {
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

type ProcessAvailableOptionsFn = (
  availableValues: Record<string, unknown>|null,
  type: string,
  uiControlAttributes?: Record<string, unknown>,
) => unknown[];

const CONTROL_TO_AVAILABLE_OPTION_PROCESSOR: Record<string, ProcessAvailableOptionsFn> = {
  FieldSelect: getSelectAvailableOptions,
  FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
  FieldRadio: processCheckboxAndRadioAvailableValues,
  FieldExpandableSelect: getExpandableSelectAvailableOptions,
};

interface ComponentReference {
  plugin: string;
  name: string;
}

interface FormField {
  availableValues: Record<string, unknown>;
  type: string;
  uiControlAttributes?: Record<string, unknown>;
  defaultValue: unknown;
  uiControl: string;
  component: Component | ComponentReference;
  inlineHelp?: string;
  inlineHelpBind?: unknown;
  templateFile?: string;
}

interface OptionLike {
  key?: string|number;
  value?: unknown;
}

export default defineComponent({
  props: {
    modelValue: null,
    modelModifiers: Object,
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
    const inlineHelpNode = ref<HTMLElement|null>(null);

    const setInlineHelp = (newVal?: string|HTMLElement|JQuery) => {
      let toAppend: HTMLElement|JQuery|string;

      if (!newVal
        || !inlineHelpNode.value
        || typeof (newVal as unknown as Record<string, unknown>).render === 'function'
      ) {
        return;
      }

      if (typeof newVal === 'string') {
        if (newVal.indexOf('#') === 0) {
          toAppend = window.$(newVal);
        } else {
          toAppend = window.vueSanitize(newVal);
        }
      } else {
        toAppend = newVal;
      }

      window.$(inlineHelpNode.value).html('').append(toAppend);
    };

    watch(() => (props.formField as FormField).inlineHelp, setInlineHelp);

    onMounted(() => {
      setInlineHelp((props.formField as FormField).inlineHelp);
    });

    return {
      inlineHelp: inlineHelpNode,
    };
  },
  computed: {
    inlineHelpComponent() {
      const formField = this.formField as FormField;

      const inlineHelpRecord = formField.inlineHelp as unknown as Record<string, unknown>;
      if (inlineHelpRecord && typeof inlineHelpRecord.render === 'function') {
        return formField.inlineHelp as Component;
      }
      return undefined;
    },
    inlineHelpBind() {
      return this.inlineHelpComponent ? this.formField.inlineHelpBind : undefined;
    },
    childComponent(): string|Component {
      const formField = this.formField as FormField;

      if (formField.component) {
        let component = formField.component as Component;

        if ((formField.component as ComponentReference).plugin) {
          const { plugin, name } = formField.component as ComponentReference;
          if (!plugin || !name) {
            throw new Error('Invalid component property given to piwik-field directive, must be '
              + '{plugin: \'...\',name: \'...\'}');
          }

          component = useExternalPluginComponent(plugin, name);
        }

        return markRaw(component);
      }

      // backwards compatibility w/ settings that use templateFile property
      if (formField.templateFile) {
        return markRaw(FieldAngularJsTemplate);
      }

      const { uiControl } = formField;

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
        || this.showDefaultValue
        || this.hasInlineHelpSlot;
    },
    showDefaultValue() {
      return this.defaultValuePretty
        && this.formField.uiControl !== 'checkbox'
        && this.formField.uiControl !== 'radio';
    },
    /**
     * @deprecated here for angularjs BC support. shouldn't be used directly, instead use
     *             GroupedSetting.vue.
     */
    showField() {
      if (!this.formField
        || !this.formField.condition
        || !(this.formField.condition instanceof Function)
      ) {
        return true;
      }

      return this.formField.condition();
    },
    processedModelValue() {
      const field = this.formField as FormField;

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
    defaultValue(): string {
      const { defaultValue } = this.formField as FormField;
      if (Array.isArray(defaultValue)) {
        return (defaultValue as unknown[]).join(',');
      }
      return defaultValue as string;
    },
    availableOptions() {
      const { childComponent } = this;
      if (typeof childComponent !== 'string') {
        return null;
      }

      const formField = this.formField as FormField;

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
      const formField = this.formField as FormField;
      let { defaultValue } = formField;
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

        return defaultValue ? `${defaultValue}` : '';
      }

      const prettyValues: unknown[] = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      (availableOptions || []).forEach((value) => {
        if (typeof (value as OptionLike).value !== 'undefined'
          && (defaultValue as unknown[]).indexOf((value as OptionLike).key) !== -1
        ) {
          prettyValues.push((value as OptionLike).value);
        }
      });

      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    },
    hasInlineHelpSlot() {
      if (!this.$slots['inline-help']) {
        return false;
      }

      const inlineHelpSlot = this.$slots['inline-help']();
      return !!inlineHelpSlot?.[0]?.children?.length;
    },
  },
  methods: {
    onChange(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
