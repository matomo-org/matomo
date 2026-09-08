<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="expandableSelector" v-focus-anywhere-but-here="{ blur: onBlur }">
    <div
      @click="toggleSelect()"
      class="select-wrapper expandableSelector__wrapper"
      :class="{ 'expandableSelector__wrapper--expanded': showSelect }"
    >
      <input type="text" class="select-dropdown" readonly :value="modelValueText"/>
      <span class="expandableSelector__chevron icon icon-chevron-down" />
    </div>

    <Teleport to="body">
    <div
      v-show="showSelect"
      class="expandableList expandableSelector__list"
      :class="{ 'expandableSelector__list--above': openAbove }"
      :data-name="name"
      :style="listStyle"
      ref="expandableList"
      @mousedown="isMouseDownInsideList = true"
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
    </Teleport>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Matomo, FocusAnywhereButHere, FocusIf } from 'CoreHome';
import AbortableModifiers from './AbortableModifiers';

/** Space left between the field and its list, matching what the stylesheet used to reserve. */
const LIST_GAP = 8;

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
    // the list is rendered at the page level, away from anything identifying the field it belongs
    // to, so the field's name is carried across as a data attribute for callers to target
    name: String,
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
  beforeUnmount() {
    this.trackTrigger(false);
  },
  data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: '',
      optionsListMaxHeight: 0,
      openAbove: false,
      triggerResize: null as ResizeObserver|null,
      listStyle: {} as Record<string, string>,
      // the list is teleported out of this component, so a click in it reads as a click outside
      isMouseDownInsideList: false,
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
        this.trackTrigger(true);
        return;
      }

      this.trackTrigger(false);
    },
    /**
     * A floating list is positioned against the viewport, so anything that moves the field has to
     * move the list with it. Capture, because the ancestor that scrolls is usually not the window.
     */
    trackTrigger(isOpen: boolean) {
      const method = isOpen ? 'addEventListener' : 'removeEventListener';
      window[method]('scroll', this.fitOptionsList, true);
      window[method]('resize', this.fitOptionsList);

      if (this.triggerResize) {
        this.triggerResize.disconnect();
        this.triggerResize = null;
      }

      if (!isOpen) {
        return;
      }

      const wrapper = (this.$el as HTMLElement).querySelector('.select-wrapper');

      // The field settles to its final height a moment after opening, so a list placed against
      // the height it had at that instant ends up a pixel out. Follow the field's size instead of
      // guessing the frame it stops changing on.
      if (wrapper) {
        this.triggerResize = new ResizeObserver(() => this.fitOptionsList());
        this.triggerResize.observe(wrapper);
      }
    },
    positionList() {
      const wrapper = (this.$el as HTMLElement).querySelector('.select-wrapper');

      if (!wrapper) {
        return;
      }

      const rect = wrapper.getBoundingClientRect();

      // The list sizes itself: its own min-width sits on the inner list, and capping the wrapper
      // to the field clipped that off, taking the category chevrons and help icons with it.
      this.listStyle = {
        left: `${rect.left}px`,
        ...(this.openAbove
          ? { bottom: `${window.innerHeight - rect.top + LIST_GAP}px` }
          : { top: `${rect.bottom + LIST_GAP}px` }),
      };
    },
    fitOptionsList() {
      const list = this.$refs.optionsList as HTMLElement|undefined;
      const dropdown = this.$refs.expandableList as HTMLElement|undefined;

      if (!list || !dropdown) {
        return;
      }

      const wrapper = (this.$el as HTMLElement).querySelector('.select-wrapper');

      if (!wrapper) {
        return;
      }

      const minUsableHeight = 150;
      const margin = 16;
      const wrapperRect = wrapper.getBoundingClientRect();

      // the search box sits between the top of the dropdown and the top of the list. It is the
      // distance between two rects of the same element, so it holds wherever the dropdown is
      // currently positioned - which matters because this decides where to position it.
      const chromeAboveList = list.getBoundingClientRect().top - dropdown.getBoundingClientRect().top;
      const roomFor = (edge: number) => Math.floor(edge - chromeAboveList) - margin;

      const spaceBelow = roomFor(window.innerHeight - wrapperRect.bottom - LIST_GAP);
      const spaceAbove = roomFor(wrapperRect.top - LIST_GAP);

      // every branch states which side it picked: this runs again whenever the field moves or
      // resizes, and a branch that left the previous answer standing would strand the list above
      // a field that has since scrolled into open space.
      if (spaceBelow >= minUsableHeight) {
        this.openAbove = false;
        this.optionsListMaxHeight = spaceBelow;
      } else if (spaceAbove > spaceBelow) {
        // not enough room below: open above the field when that side offers more
        this.openAbove = true;
        this.optionsListMaxHeight = Math.max(minUsableHeight, spaceAbove);
      } else {
        // keep a usable minimum on the larger side; the list scrolls for the rest
        this.openAbove = false;
        this.optionsListMaxHeight = Math.max(minUsableHeight, spaceBelow);
      }

      this.positionList();
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
      // the directive tests whether the click landed inside this component's element, and the
      // list no longer is one, so a click in the list has to be recognised here instead
      if (this.isMouseDownInsideList) {
        this.isMouseDownInsideList = false;
        return;
      }

      this.showSelect = false;
      this.trackTrigger(false);
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
      this.isMouseDownInsideList = false;
      this.trackTrigger(false);

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
