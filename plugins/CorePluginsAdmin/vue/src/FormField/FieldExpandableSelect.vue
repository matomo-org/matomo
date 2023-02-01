<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="expandableSelector" v-focus-anywhere-but-here="{ blur: onBlur }">
    <div @click="showSelect = !showSelect" class="select-wrapper">
      <svg class="caret" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
        <path d="M7 10l5 5 5-5z"></path><path d="M0 0h24v24H0z" fill="none"></path>
      </svg>
      <input type="text" class="select-dropdown" readonly="readonly" :value="modelValueText"/>
    </div>

    <div v-show="showSelect" class="expandableList z-depth-2">

      <div class="searchContainer">
        <input
          type="text"
          placeholder="Search"
          v-model="searchTerm"
          class="expandableSearch browser-default"
          v-focus-if="{ focused: showSelect }"
        />
      </div>
      <ul class="collection firstLevel">
        <li
          v-for="(options, index) in availableOptions"
          class="collection-item"
          v-show="options.values.filter(x =>
           x.value.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1).length"
          :key="index"
        >
          <h4
            class="expandableListCategory"
            @click="onCategoryClicked(options)"
          >
            {{ options.group }}
            <span
              class="secondary-content"
              :class='{
                "icon-arrow-right": showCategory !== options.group,
                "icon-arrow-bottom": showCategory === options.group
              }'
            />
          </h4>

          <ul v-show="showCategory === options.group || searchTerm" class="collection secondLevel">
            <li
              class="expandableListItem collection-item valign-wrapper"
              v-for="children in options.values.filter(x =>
              x.value.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1)"
              :key="children.key"
              @click="onValueClicked(children)"
            >
              <span class="primary-content">{{ children.value }}</span>
              <span
                v-show="children.tooltip"
                :title="children.tooltip"
                class="secondary-content icon-help"
              ></span>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FocusAnywhereButHere, FocusIf } from 'CoreHome';
import AbortableModifiers from './AbortableModifiers';

interface SelectValueInfo {
  key: unknown;
}

interface AvailableOptions {
  group: string;
  key: string|number;
  value: unknown;
  tooltip?: string;
}

interface Option {
  key: string|number;
  value: unknown;
  tooltip?: string;
}

interface OptionGroup {
  group: string;
  values: Option[];
}

export function getAvailableOptions(
  availableValues: Record<string, unknown>|null,
): OptionGroup[] {
  const flatValues: OptionGroup[] = [];

  if (!availableValues) {
    return flatValues;
  }

  const groups: Record<string, OptionGroup> = {};
  Object.values(availableValues).forEach((uncastedValue) => {
    const value = uncastedValue as AvailableOptions;
    const group = value.group || '';

    if (!(group in groups) || !groups[group]) {
      groups[group] = { values: [], group };
    }

    const formatted: Option = { key: value.key, value: value.value };

    if ('tooltip' in value && value.tooltip) {
      formatted.tooltip = value.tooltip;
    }

    groups[group].values.push(formatted);
  });

  Object.values(groups).forEach((group) => {
    if (group.values.length) {
      flatValues.push(group);
    }
  });

  return flatValues;
}

export default defineComponent({
  props: {
    modelValue: [Number, String],
    modelModifiers: Object,
    availableOptions: Array,
    title: String,
  },
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: '',
    };
  },
  computed: {
    modelValueText() {
      if (this.title) {
        return this.title;
      }

      const key = this.modelValue;
      const availableOptions = (this.availableOptions || []) as OptionGroup[];

      let keyItem!: { key: string|number, value: unknown }|undefined;
      availableOptions.some((option) => {
        keyItem = option.values.find((item) => item.key === key);
        return keyItem; // stop iterating if found
      });

      if (keyItem) {
        return keyItem.value ? `${keyItem.value}` : '';
      }
      return key ? `${key}` : '';
    },
  },
  methods: {
    onBlur() {
      this.showSelect = false;
    },
    onCategoryClicked(options: AvailableOptions) {
      if (this.showCategory === options.group) {
        this.showCategory = '';
      } else {
        this.showCategory = options.group;
      }
    },
    onValueClicked(selectedValue: SelectValueInfo) {
      this.showSelect = false;

      if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
        this.$emit('update:modelValue', selectedValue.key);
        return;
      }

      const emitEventData = {
        value: selectedValue.key,
        abort() {
          // empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        },
      };

      this.$emit('update:modelValue', emitEventData);
    },
  },
});
</script>
