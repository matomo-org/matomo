<template>
  <div class="expandableSelector" v-focus-anywhere-but-here="{ blur: onBlur }">
    <div @click="showSelect = !showSelect" class="select-wrapper">
      <svg class="caret" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
        <path d="M7 10l5 5 5-5z"></path><path d="M0 0h24v24H0z" fill="none"></path>
      </svg>
      <input type="text" class="select-dropdown" readonly="readonly" :value="title"/>
    </div>

    <div v-show="showSelect" class="expandableList z-depth-2">

      <div class="searchContainer">
        <input
          type="text"
          placeholder="Search"
          v-model="searchTerm"
          class="expandableSearch browser-default"
          v-focus-if="showSelect"
        />
      </div>
      <ul class="collection firstLevel">
        <li
          v-for="(options, index) in availableOptions"
          class="collection-item"
          v-show="options.values.filter(x => x.indexOf(searchTerm) !== -1).length"
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
              v-for="children in options.values.filter(x => x.indexOf(searchTerm) !== -1)"
              :key="children.key"
              @click="onValueClicked($event, children)"
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

interface SelectValueInfo {
  key: unknown;
}

interface AvailableOptions {
  group: string;
  key: unknown;
  value: unknown;
  tooltip?: string;
}

export default defineComponent({
  props: {
    title: String,
    availableValues: Array,
  },
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  // emits modelValue update, but doesn't sync to input value. same as angularjs directive.
  emits: ['update:modelValue'],
  computed: {
    availableOptions() {
      const availableValues = this.availableValues;
      const flatValues = [];

      const groups = {};
      Object.values(availableValues).forEach((uncastedValue) => {
        const value = uncastedValue as AvailableOptions;
        const group = value.group || '';

        if (!(group in groups) || !groups[group]) {
          groups[group] = { values: [], group };
        }

        const formatted: Record<string, unknown> = { key: value.key, value: value.value };

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
    },
  },
  data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: '',
    };
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
      this.$emit('update:modelValue', selectedValue.key);
      this.showSelect = false;
    },
  },
});
</script>
