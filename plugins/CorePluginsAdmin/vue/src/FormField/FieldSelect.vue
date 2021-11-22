<template>
  <div v-if="groupedOptions">
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
        >
          {{ option.value }}
        </option>
      </optgroup>
    </select>
    <label :for="name" v-html="title"></label>
  </div>
  <div v-if="!groupedOptions && options">
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
      >
        {{ option.value }}
      </option>
    </select>
    <label :for="name" v-html="title"></label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

// TODO: test both use cases, grouped and ungrouped for multiselect (put in UI demo)
// TODO: check that the value for multiselect is the key and not the value
interface OptionGroup {
  group?: string;
  key: string;
  value: unknown;
}

function initMaterialSelect(select: HTMLElement, placeholder: string, uiControlOptions = {}) {
  const $select = window.$(select);

  $select.formSelect(uiControlOptions);

  // add placeholder to input
  if (placeholder) {
    const $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (availableValues instanceof Array
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

  let availableValues = givenAvailableValues as Record<string, Record<string|number, unknown>>;
  if (!hasGroupedValues(availableValues)) {
    availableValues = { '': givenAvailableValues };
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

      flatValues.push({ group, key, value });
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
        return [{ key: '', value: this.modelValue, group: '' }, ...this.availableOptions];
      }
      return this.availableOptions;
    },
    groupedOptions() {
      const { options } = this;
      if (!options || !options[0] || typeof options[0].group === 'undefined') {
        return null;
      }

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
        // TODO: check Array.from compatibility
        newValue = Array.from(element.options).filter((e) => e.selected).map((e) => e.value);
        newValue = newValue.map(handleOldAngularJsValues);
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      this.$emit('update:modelValue', newValue);
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        window.$(this.$refs.select).val(newVal);
        setTimeout(() => {
          initMaterialSelect(
            this.$refs.select,
            this.uiControlAttributes.placeholder,
            this.uiControlOptions,
          );
        });
      }
    },
    'uiControlAttributes.disabled': {
      handler(newVal, oldVal) {
        setTimeout(() => {
          if (newVal !== oldVal) {
            initMaterialSelect(
              this.$refs.select,
              this.uiControlAttributes.placeholder,
              this.uiControlOptions,
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
            this.uiControlAttributes.placeholder,
            this.uiControlOptions,
          );
        });
      }
    },
  },
  mounted() {
    setTimeout(() => {
      initMaterialSelect(
        this.$refs.select,
        this.uiControlAttributes.placeholder,
        this.uiControlOptions,
      );
    });
  },
});
</script>
