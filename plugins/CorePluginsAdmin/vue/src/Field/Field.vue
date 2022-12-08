<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <FormField
    :form-field="field"
    :model-value="modelValue"
    @update:model-value="onChange($event)"
    :model-modifiers="modelModifiers"
  >
    <template v-slot:inline-help>
      <slot name="inline-help"></slot>
    </template>
  </FormField>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FormField from '../FormField/FormField.vue';

const UI_CONTROLS_TO_TYPE: Record<string, string> = {
  multiselect: 'array',
  checkbox: 'boolean',
  site: 'object',
  number: 'integer',
};

export default defineComponent({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uicontrol: String,
    name: String,
    defaultValue: null,
    options: [Object, Array],
    description: String,
    introduction: String,
    title: String,
    inlineHelp: [String, Object],
    inlineHelpBind: Object,
    disabled: Boolean,
    uiControlAttributes: {
      type: Object,
      default: () => ({}),
    },
    uiControlOptions: {
      type: Object,
      default: () => ({}),
    },
    autocomplete: String,
    condition: Function,
    varType: String,
    autofocus: Boolean,
    tabindex: Number,
    fullWidth: Boolean,
    maxlength: Number,
    required: Boolean,
    placeholder: String,
    rows: Number,
    min: Number,
    max: Number,
    component: null,
    templateFile: String,
  },
  emits: ['update:modelValue'],
  components: {
    FormField,
  },
  computed: {
    type() {
      if (this.varType) {
        return this.varType;
      }

      const uicontrol = this.uicontrol as string;
      if (uicontrol && UI_CONTROLS_TO_TYPE[uicontrol]) {
        return UI_CONTROLS_TO_TYPE[uicontrol];
      }

      return 'string';
    },
    field() {
      return {
        uiControl: this.uicontrol,
        type: this.type,
        name: this.name,
        defaultValue: this.defaultValue,
        availableValues: this.options,
        description: this.description,
        introduction: this.introduction,
        inlineHelp: this.inlineHelp,
        inlineHelpBind: this.inlineHelpBind,
        title: this.title,
        component: this.component,
        templateFile: this.templateFile, // BC for angularjs code that uses <Field> indirectly
        uiControlAttributes: {
          ...this.uiControlAttributes,
          disabled: this.disabled,
          autocomplete: this.autocomplete,
          tabindex: this.tabindex,
          autofocus: this.autofocus,
          rows: this.rows,
          required: this.required,
          maxlength: this.maxlength,
          placeholder: this.placeholder,
          min: this.min,
          max: this.max,
        },
        fullWidth: this.fullWidth,
        uiControlOptions: this.uiControlOptions,
      };
    },
  },
  methods: {
    onChange(newValue: unknown) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
