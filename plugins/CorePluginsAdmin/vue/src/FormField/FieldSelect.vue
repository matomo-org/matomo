<template>
  <select
    v-if="groupedOptions"
    ref="select"
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
        :value="option.key"
        :selected="multiple ? value && value.indexOf(option.key) !== -1 : value === option.key"
      >
        {{ option.value }}
      </option>
    </optgroup>
  </select>
  <select
    v-if="!groupedOptions"
    ref="select"
    :multiple="multiple"
    :name="name"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  >
    <option
      v-for="option in availableOptions"
      :key="option.key"
      :value="option.key"
      :selected="multiple ? value && value.indexOf(option.key) !== -1 : value === option.key"
    >
      {{ option.value }}
    </option>
  </select>
  <label :for="name" v-html="title"></label>
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

  function hasOption(key) {
    return flatValues.some((f) => f.key === key);
  }

  // for selects w/ a placeholder, add an option to unset the select
  if (uiControlAttributes.placeholder
    && !hasOption('')
  ) {
    return [{ key: '', value: '' }, ...flatValues];
  }

  return flatValues;
}

export default defineComponent({
  props: {
    value: null,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Object,
    uiControlAttributes: Object,
    uiControlOptions: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    groupedOptions() {
      const { availableOptions } = this;
      if (!availableOptions[0] || !availableOptions[0].group) {
        return null;
      }

      const groups = {};
      availableOptions.forEach((entry) => {
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
      } else {
        newValue = element.value;
      }

      this.$emit('update:modelValue', newValue);
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        // TODO: $timeout here
        initMaterialSelect(
          this.$refs.select,
          this.uiControlAttributes.placeholder,
          this.uiControlOptions,
        );
      }
    },
    // TODO: Test this
    'uiControlAttributes.disabled': {
      handler(newVal, oldVal) {
        // TODO: $timeout here
        if (newVal !== oldVal) {
          initMaterialSelect(
            this.$refs.select,
            this.uiControlAttributes.placeholder,
            this.uiControlOptions,
          );
        }
      },
    },
    availableOptions(newVal, oldVal) {
      // TODO: $timeout here
      if (newVal !== oldVal) {
        initMaterialSelect(
          this.$refs.select,
          this.uiControlAttributes.placeholder,
          this.uiControlOptions,
        );
      }
    },
  },
  mounted() {
    initMaterialSelect(
      this.$refs.select,
      this.uiControlAttributes.placeholder,
      this.uiControlOptions,
    );
  },
});
</script>
