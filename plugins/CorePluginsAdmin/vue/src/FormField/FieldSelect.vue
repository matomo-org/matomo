<template>
  <select
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

export default defineComponent({
  props: {
    value: null,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Object,
    uiControlAttributes: Object,
  },
  emits: ['update:modelValue'],
  computed: {
    groupedOptions() {
      const availableOptions = this.availableOptions as Record<string, OptionGroup>;
      if (!availableOptions[0] || !availableOptions[0].group) {
        return;
      }

      const groups = {};
      Object.values(availableOptions).forEach((entry) => {
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
});
</script>
