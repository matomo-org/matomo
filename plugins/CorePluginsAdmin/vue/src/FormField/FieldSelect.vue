<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="groupedOptions" class="matomo-field-select">
    <select
      ref="select"
      class="grouped"
      :multiple="multiple"
      :name="name"
      :id="id"
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
    <label :for="id" v-html="$sanitize(title)"></label>
  </div>
  <div v-if="!groupedOptions && options" class="matomo-field-select">
    <select
      class="ungrouped"
      ref="select"
      :multiple="multiple"
      :name="name"
      :id="id"
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
    <label :for="id" v-html="$sanitize(title)"></label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import AbortableModifiers from './AbortableModifiers';

export interface OptionGroup {
  group?: string;
  key: string|number;
  value: unknown;
  disabled?: boolean;
}

// distance between the field and its open panel, matching FieldExpandableSelect.less
const DROPDOWN_GAP = 8;

function initMaterialSelect(
  select: HTMLSelectElement|undefined|null,
  modelValue: string|number|string[],
  placeholder: string|undefined,
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
      opt.selected = !!modelValue
        && (modelValue as unknown[]).indexOf(opt.value.replace(/^string:/, '')) !== -1;
    } else {
      opt.selected = `string:${modelValue}` === opt.value;
    }
  });

  const callerDropdownOptions = (uiControlOptions as Record<string, unknown>)
    .dropdownOptions as Record<string, unknown>|undefined;

  // Materialize's select forces coverTrigger to false itself, so the panel already opens
  // below the field; only the gap in front of it is ours to add.
  $select.formSelect({
    ...uiControlOptions,
    dropdownOptions: {
      ...callerDropdownOptions,
      onOpenStart(trigger: Element) {
        // mirrors MetricsPicker: an `expanded` modifier the stylesheet hangs the open
        // state off, since Materialize marks the panel but not the control
        trigger.closest('.select-wrapper')?.classList.add('expanded');

        // Materialize positions the panel flush against the trigger, and no CSS offset can
        // change that: it clears the inline top, measures the panel, and writes back the
        // difference, so a margin is measured and then cancelled. Placement happens
        // synchronously after this callback, so correcting on the next animation frame
        // lands after the final top is set and before the panel is first painted. The
        // same measure-and-cancel behaviour discards this correction on the next open,
        // so it cannot accumulate.
        window.requestAnimationFrame(() => {
          const wrapper = trigger.closest('.select-wrapper') as HTMLElement|null;
          // the trigger points at its panel by id, which still resolves when a configured
          // container has moved the panel out of the wrapper
          const panelId = trigger.getAttribute('data-target');
          const panel = (panelId ? document.getElementById(panelId) : null)
            || wrapper?.querySelector('ul.dropdown-content') as HTMLElement|null;
          if (!wrapper || !panel) {
            return;
          }

          const wrapperRect = wrapper.getBoundingClientRect();
          const openedAbove = panel.getBoundingClientRect().top < wrapperRect.top;
          // the trigger sits inside the wrapper's border, which the gap has to clear too
          const borderWidth = parseFloat(getComputedStyle(wrapper).borderTopWidth) || 0;
          const offset = DROPDOWN_GAP + borderWidth;

          const currentTop = parseFloat(panel.style.top) || 0;
          panel.style.top = `${currentTop + (openedAbove ? -offset : offset)}px`;
        });

        if (typeof callerDropdownOptions?.onOpenStart === 'function') {
          (callerDropdownOptions.onOpenStart as (el: Element) => void).call(this, trigger);
        }
      },
      onCloseStart(trigger: Element) {
        trigger.closest('.select-wrapper')?.classList.remove('expanded');

        if (typeof callerDropdownOptions?.onCloseStart === 'function') {
          (callerDropdownOptions.onCloseStart as (el: Element) => void).call(this, trigger);
        }
      },
    },
  });

  // add placeholder to input
  if (placeholder) {
    const $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues: unknown) {
  if (Array.isArray(availableValues)
    || !(typeof availableValues === 'object')
  ) {
    return false;
  }

  return Object.values(availableValues as Record<string, unknown>).some(
    (v) => typeof v === 'object',
  );
}

function hasOption(flatValues: OptionGroup[], key: string) {
  return flatValues.some((f) => f.key === key);
}

export function getAvailableOptions(
  givenAvailableValues: Record<string, unknown>|null,
  type: string,
  uiControlAttributes?: Record<string, unknown>,
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

  const flatValues: OptionGroup[] = [];
  Object.entries(availableValues).forEach(([group, values]) => {
    Object.entries(values).forEach(([valueObjKey, value]) => {
      if (value && typeof value === 'object' && typeof (value as OptionGroup).key !== 'undefined') {
        flatValues.push(value as OptionGroup);
        return;
      }

      let key: number|string = valueObjKey;
      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({ group: hasGroups ? group : undefined, key, value });
    });
  });

  // for selects w/ a placeholder, add an option to unset the select
  if (uiControlAttributes?.placeholder
    && !hasOption(flatValues, '')
  ) {
    return [{ key: '', value: '' }, ...flatValues];
  }

  return flatValues;
}

function handleOldAngularJsValues<T>(value: T): T {
  if (typeof value === 'string') {
    return value.replace(/^string:/, '') as unknown as T;
  }
  return value;
}

export default defineComponent({
  props: {
    modelValue: null,
    modelModifiers: Object,
    multiple: Boolean,
    name: String,
    title: String,
    id: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    uiControlOptions: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    options(): OptionGroup[]|undefined {
      // if modelValue is empty, but there is no empty value allowed in availableOptions,
      // add one temporarily until something is set
      const availableOptions = this.availableOptions as OptionGroup[]|undefined;
      if (availableOptions
        && !hasOption(availableOptions, '')
        && (typeof this.modelValue === 'undefined'
          || this.modelValue === null
          || this.modelValue === '')
      ) {
        return [
          { key: '', value: this.modelValue, group: this.hasGroups ? '' : undefined },
          ...availableOptions,
        ];
      }
      return availableOptions;
    },
    hasGroups() {
      const availableOptions = this.availableOptions as OptionGroup[]|undefined;
      return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== 'undefined';
    },
    groupedOptions() {
      const { options } = this;

      if (!this.hasGroups || !options) {
        return null;
      }

      const groups: Record<string, OptionGroup[]> = {};
      (options as OptionGroup[]).forEach((entry) => {
        const group = entry.group!;
        groups[group] = groups[group] || [];
        groups[group].push(entry);
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
        newValue = newValue.map((x) => handleOldAngularJsValues(x));
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      const emitEventData = {
        value: newValue,
        abort: () => {
          this.onModelValueChange(this.modelValue);
        },
      };

      this.$emit('update:modelValue', emitEventData);
    },
    onModelValueChange(newVal: string|number|string[]) {
      window.$(this.$refs.select as HTMLSelectElement).val(newVal);
      setTimeout(() => {
        initMaterialSelect(
          this.$refs.select as HTMLSelectElement,
          newVal,
          this.uiControlAttributes?.placeholder,
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
      handler(newVal?: boolean, oldVal?: boolean) {
        setTimeout(() => {
          if (newVal !== oldVal) {
            initMaterialSelect(
              this.$refs.select as HTMLSelectElement,
              this.modelValue,
              this.uiControlAttributes?.placeholder,
              this.uiControlOptions,
              this.multiple,
            );
          }
        });
      },
    },
    availableOptions(newVal?: OptionGroup[], oldVal?: OptionGroup[]) {
      if (newVal !== oldVal) {
        setTimeout(() => {
          initMaterialSelect(
            this.$refs.select as HTMLSelectElement,
            this.modelValue,
            this.uiControlAttributes?.placeholder,
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
        this.$refs.select as HTMLSelectElement,
        this.modelValue,
        this.uiControlAttributes?.placeholder,
        this.uiControlOptions,
        this.multiple,
      );
    });
  },
});
</script>
