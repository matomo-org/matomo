<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="expandableSelector" v-focus-anywhere-but-here="{ blur: onBlur }">
    <div
      @click="toggleSelect()"
      class="select-wrapper"
      :class="{ expanded: showSelect }"
    >
      <input type="text" class="select-dropdown" readonly :value="modelValueText"/>
      <span class="select-chevron icon icon-chevron-down" />
    </div>

    <div
      v-show="showSelect"
      class="expandableList"
      :class="{ 'expandableList--above': openAbove }"
      ref="expandableList"
    >

      <div class="searchContainer">
        <input
          type="text"
          placeholder="Search"
          v-model="searchTerm"
          class="expandableSearch browser-default"
          v-focus-if="{ focused: showSelect }"
        />
      </div>
      <ul
        class="collection firstLevel"
        ref="optionsList"
        :style="optionsListStyle"
      >
        <li
          v-for="(options, index) in availableOptions"
          class="collection-item"
          v-show="visibleChildren(options).length"
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
                "icon-chevron-right": showCategory !== options.group,
                "icon-chevron-down": showCategory === options.group
              }'
            />
          </h4>

          <ul v-show="showCategory === options.group || searchTerm" class="collection secondLevel">
            <li
              class="expandableListItem collection-item valign-wrapper"
              v-for="children in visibleChildren(options)"
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
import { defineComponent, PropType } from 'vue';
import { Matomo, FocusAnywhereButHere, FocusIf } from 'CoreHome';
import AbortableModifiers from './AbortableModifiers';

export interface SelectValueInfo {
  key: unknown;
}

export interface AvailableOptions {
  group: string;
  key: string|number;
  value: unknown;
  tooltip?: string;
}

export interface Option {
  key: string|number;
  value: unknown;
  tooltip?: string;
}

export interface OptionGroup {
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
    availableOptions: Array as PropType<OptionGroup[]>,
    title: String,
    searchOnGroup: {
      type: Boolean,
      default: false,
    },
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
      optionsListMaxHeight: 0,
      openAbove: false,
    };
  },
  computed: {
    optionsListStyle() {
      if (!this.optionsListMaxHeight) {
        return {};
      }
      return { maxHeight: `${this.optionsListMaxHeight}px` };
    },
    searchTermLowercase() {
      return this.searchTerm.toLowerCase();
    },
    searchTermNormalized() {
      return this.normalize(this.searchTerm);
    },
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
    toggleSelect() {
      this.showSelect = !this.showSelect;
      this.openAbove = false;

      if (this.showSelect) {
        this.$nextTick(() => this.fitOptionsList());
      }
    },
    fitOptionsList() {
      const list = this.$refs.optionsList as HTMLElement|undefined;
      const dropdown = this.$refs.expandableList as HTMLElement|undefined;

      if (!list || !dropdown) {
        return;
      }

      const minUsableHeight = 150;
      const margin = 16;
      const listRect = list.getBoundingClientRect();
      const spaceBelow = Math.floor(window.innerHeight - listRect.top) - margin;

      if (spaceBelow >= minUsableHeight) {
        this.optionsListMaxHeight = spaceBelow;
        return;
      }

      // not enough room below: open above the field when that side offers more
      const dropdownRect = dropdown.getBoundingClientRect();
      const wrapperRect = (this.$el as HTMLElement)
        .querySelector('.select-wrapper')!.getBoundingClientRect();
      const chromeAboveList = listRect.top - dropdownRect.top;
      const spaceAbove = Math.floor(wrapperRect.top - 8 - chromeAboveList) - margin;

      if (spaceAbove > spaceBelow) {
        this.openAbove = true;
        this.optionsListMaxHeight = Math.max(minUsableHeight, spaceAbove);
        return;
      }

      // keep a usable minimum on the larger side; the page scrolls for the rest
      this.optionsListMaxHeight = Math.max(minUsableHeight, spaceBelow);
    },
    normalize(value: string) {
      return Matomo.helper.normalize(value);
    },
    isSearchMatch(value: unknown) {
      const stringValue = `${value ?? ''}`;
      return this.normalize(stringValue).indexOf(this.searchTermNormalized) !== -1
        || stringValue.toLowerCase().indexOf(this.searchTermLowercase) !== -1;
    },
    visibleChildren(options: OptionGroup) {
      if (this.searchOnGroup && this.isSearchMatch(options.group)) {
        return options.values;
      }
      return options.values.filter((x) => this.isSearchMatch(x.value));
    },
    onBlur() {
      this.showSelect = false;
    },
    onCategoryClicked(options: OptionGroup) {
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
