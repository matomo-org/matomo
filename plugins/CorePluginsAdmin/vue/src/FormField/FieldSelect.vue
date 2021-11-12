<template>
  <select
    ref="select"
    :multiple="multiple"
    :name="name"
    @change="onChange($event)"
    v-bind="uiControlAttributes"
  >
    <optgroup
      v-if="groupedOptions"
      v-for="[group, options] in groupedOptions"
      :key="group"
      :label="group"
    >
      <option v-for="option in options" :key="option.key" :value="option.key" :selected="value === option.key">
        {{ option.value }}
      </option>
    </optgroup>

    <option
      v-if="!groupedOptions"
      v-for="option in availableOptions"
      :key="option.key"
      :value="option.key"
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
  key: String;
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

export default defineComponent({
  props: {
    value: null,
    multiple: Boolean,
    name: String,
    title: String,
    availableValues: Object,
    uiControlAttributes: Object,
    uiControlOptions: Object,
  },
  emits: ['update:modelValue'],
  computed: {
    availableOptions(): OptionGroup[] {
      if (!this.availableValues) {
        return [];
      }

      let availableValues: Record<string, unknown> = this.availableValues;

      if (!hasGroupedValues(availableValues)) {
        availableValues = { '': availableValues };
      }

      const flatValues = [];
      Object.entries(availableValues).forEach(([values, group]) => {
        Object.entries(values).forEach(([value, valueObjKey]) => {
          if (typeof value === 'object' && typeof value.key !== 'undefined') {
            flatValues.push(value);
            return;
          }

          let key = valueObjKey;
          if (this.type === 'integer' && typeof key === 'string') {
            key = parseInt(key, 10);
          }

          flatValues.push({ group, key, value });
        });
      });
      return flatValues;
    },
    groupedOptions() {
      const availableOptions = this.availableOptions;
      if (!availableOptions[0] || !availableOptions[0].group) {
        return;
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
        } else if (lhs[0] > rhs[0]) {
          return 1;
        } else {
          return 0;
        }
      });
      return result;
    },
  },
  methods: {
    onChange(event: Event) {
      this.$emit('update:modelValue', (event.target as HTMLSelectElement).value);
    },
  },
  watch: {
    modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        // TODO: $timeout here
        initMaterialSelect(this.$refs.select, this.uiControlAttributes.placeholder, this.uiControlOptions);
      }
    },
    // TODO: Test this
    'uiControlAttributes.disabled': {
      handler(newVal, oldVal) {
        // TODO: $timeout here
        if (newVal !== oldVal) {
          initMaterialSelect(this.$refs.select, this.uiControlAttributes.placeholder, this.uiControlOptions);
        }
      },
    },
    availableOptions(newVal, oldVal) {
      // TODO: $timeout here
      if (newVal !== oldVal) {
        initMaterialSelect(this.$refs.select, this.uiControlAttributes.placeholder, this.uiControlOptions);
      }
    },
  },
  mounted() {
    initMaterialSelect(this.$refs.select, this.uiControlAttributes.placeholder, this.uiControlOptions);
  },
});
</script>
