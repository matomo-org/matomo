<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="groupedOptions" class="matomo-field-select">
    <select
      ref="select"
      class="grouped"
      :multiple="multiple"
      :name="name"
      @change="onChange($event)"
      v-bind="uiControlAttributes"
    >
      <optgroup
        v-for="[group, options] in groupedOptions"
        :key="group"
        :label="group"
      >
        <option
          v-for="option in options"
          :key="option.key"
          :value="`string:${option.key}`"
          :selected="multiple
            ? modelValue && modelValue.indexOf(option.key) !== -1
            : modelValue === option.key"
          :disabled="option.disabled"
        >
          {{ option.value }}
        </option>
      </optgroup>
    </select>
    <label :for="name" v-html="title"></label>
  </div>
  <div v-if="!groupedOptions && options" class="matomo-field-select">
    <select
      class="ungrouped"
      ref="select"
      :multiple="multiple"
      :name="name"
      @change="onChange($event)"
      v-bind="uiControlAttributes"
    >
      <option
        v-for="option in options"
        :key="option.key"
        :value="`string:${option.key}`"
        :selected="multiple
            ? modelValue && modelValue.indexOf(option.key) !== -1
            : modelValue === option.key"
        :disabled="option.disabled"
      >
        {{ option.value }}
      </option>
    </select>
    <label :for="name" v-html="title"></label>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';

interface OptionGroup {
  group?: string;
  key: string;
  value: unknown;
  disabled?: boolean;
}

function initMaterialSelect(
  select: HTMLSelectElement,
  modelValue: string|number|string[],
  placeholder: string,
  uiControlOptions = {},
  multiple: boolean,
) {
  if (!select) {
    return;
  }

  const $select = window.$(select);

  // reset selected since materialize removes them
  Array.from(select.options).forEach((opt) => {
    if (multiple) {
      opt.selected = modelValue
        && (modelValue as string[]).indexOf(
          opt.value.replace(/^string:/, ''),
        ) !== -1;
    } else {
      opt.selected = `string:${modelValue}` === opt.value;
    }
  });

  $select.formSelect(uiControlOptions);

  // add placeholder to input
  if (placeholder) {
    const $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (Array.isArray(availableValues)
    || !(typeof availableValues === 'object')
  ) {
    return false;
  }

  return Object.values(availableValues).some((v) => typeof v === 'object');
}

function hasOption(flatValues: OptionGroup[], key: string) {
  return flatValues.some((f) => f.key === key);
}

export function getAvailableOptions(
  givenAvailableValues?: Record<string, unknown>|null,
  type: string,
  uiControlAttributes: Record<string, unknown>,
): OptionGroup[] {
  if (!givenAvailableValues) {
    return [];
  }

  let hasGroups = true;

  let availableValues = givenAvailableValues as Record<string, Record<string|number, unknown>>;
  if (!hasGroupedValues(availableValues)) {
    availableValues = { '': givenAvailableValues };
    hasGroups = false;
  }

  const flatValues = [];
  Object.entries(availableValues).forEach(([group, values]) => {
    Object.entries(values).forEach(([valueObjKey, value]) => {
      if (typeof value === 'object' && typeof value.key !== 'undefined') {
        flatValues.push(value);
        return;
      }

      let key: number = valueObjKey as number;
      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({ group: hasGroups ? group : undefined, key, value });
    });
  });

  // for selects w/ a placeholder, add an option to unset the select
  if (uiControlAttributes.placeholder
    && !hasOption(flatValues, '')
  ) {
    return [{ key: '', value: '' }, ...flatValues];
  }

  return flatValues;
}

function handleOldAngularJsValues(value: unknown) {
  if (typeof value === 'string') {
    return value.replace(/^string:/, '');
  }
  return value;
}

export default defineComponent({
  props: {
    modelValue: null,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    uiControlOptions: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    options() {
      // if modelValue is empty, but there is no empty value allowed in availableOptions,
      // add one temporarily until something is set
      if (this.availableOptions
        && !hasOption(this.availableOptions, '')
        && (typeof this.modelValue === 'undefined'
          || this.modelValue === null
          || this.modelValue === '')
      ) {
        return [
          { key: '', value: this.modelValue, group: this.hasGroups ? '' : undefined },
          ...this.availableOptions,
        ];
      }
      return this.availableOptions;
    },
    hasGroups() {
      const { availableOptions } = this;
      return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== 'undefined';
    },
    groupedOptions() {
      if (!this.hasGroups) {
        return null;
      }

      const { options } = this;
      const groups = {};
      options.forEach((entry) => {
        groups[entry.group] = groups[entry.group] || [];
        groups[entry.group].push(entry);
      });

      const result = Object.entries(groups);
      result.sort((lhs, rhs) => {
        if (lhs[0] < rhs[0]) {
          return -1;
        }

        if (lhs[0] > rhs[0]) {
          return 1;
        }

        return 0;
      });
      return result;
    },
  },
  methods: {
    onChange(event: Event) {
      const element = event.target as HTMLSelectElement;

      let newValue: string|number|(string|number)[];
      if (this.multiple) {
        newValue = Array.from(element.options).filter((e) => e.selected).map((e) => e.value);
        newValue = newValue.map(handleOldAngularJsValues);
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      this.$emit('update:modelValue', newValue);

      // if modelValue does not change, select will still have the changed value, but we
      // want it to have the value determined by modelValue. so we force an update.
      nextTick(() => {
        if (this.modelValue !== newValue) {
          this.onModelValueChange(this.modelValue);
        }
      });
    },
    onModelValueChange(newVal: string|number|string[]) {
      window.$(this.$refs.select as HTMLSelectElement).val(newVal);
      setTimeout(() => {
        initMaterialSelect(
          this.$refs.select,
          newVal,
          this.uiControlAttributes.placeholder,
          this.uiControlOptions,
          this.multiple,
        );
      });
    },
  },
  watch: {
    modelValue(newVal: string|number|string[]) {
      this.onModelValueChange(newVal);
    },
    'uiControlAttributes.disabled': {
      handler(newVal, oldVal) {
        setTimeout(() => {
          if (newVal !== oldVal) {
            initMaterialSelect(
              this.$refs.select,
              this.modelValue,
              this.uiControlAttributes.placeholder,
              this.uiControlOptions,
              this.multiple,
            );
          }
        });
      },
    },
    availableOptions(newVal, oldVal) {
      if (newVal !== oldVal) {
        setTimeout(() => {
          initMaterialSelect(
            this.$refs.select,
            this.modelValue,
            this.uiControlAttributes.placeholder,
            this.uiControlOptions,
            this.multiple,
          );
        });
      }
    },
  },
  mounted() {
    setTimeout(() => {
      initMaterialSelect(
        this.$refs.select,
        this.modelValue,
        this.uiControlAttributes.placeholder,
        this.uiControlOptions,
        this.multiple,
      );
    });
  },
});
</script>
