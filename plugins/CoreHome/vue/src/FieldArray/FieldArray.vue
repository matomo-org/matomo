<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="fieldArray form-group">
    <div
      class="fieldArrayTable multiple valign-wrapper"
      v-for="(item, index) in modelValue"
      :class="{[`fieldArrayTable${index}`]: true}"
      :key="index"
    >
      <div
        v-if="field.uiControl"
        class="fieldUiControl"
      >
        <Field
          :full-width="true"
          :model-value="item"
          :options="field.availableValues"
          @update:modelValue="onEntryChange($event, index)"
          :model-modifiers="field.modelModifiers"
          :placeholder="' '"
          :uicontrol="field.uiControl"
          :title="field.title"
          :name="`${name}-${index}`"
          :id="`${id}-${index}`"
          :template-file="field.templateFile"
          :component="field.component"
        >
        </Field>
      </div>
      <span
        @click="removeEntry(index)"
        class="icon-minus valign"
        v-show="index + 1 !== (modelValue || []).length"
        :title="translate('General_Remove')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';

// async since this is a recursive component
const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

export interface FieldArrayControl {
  uiControl?: string;
  availableValues?: unknown;
  modelModifiers?: Record<string, boolean>;
  title?: string;
  templateFile?: string;
  component?: unknown;
}

export default defineComponent({
  props: {
    modelValue: Array as PropType<unknown[]>,
    name: String,
    id: String,
    field: {
      type: Object as PropType<FieldArrayControl>,
      required: true,
    },
    rows: String,
  },
  components: {
    Field,
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue(newValue) {
      this.checkEmptyModelValue(newValue);
    },
  },
  mounted() {
    this.checkEmptyModelValue(this.modelValue);
  },
  methods: {
    checkEmptyModelValue(newValue?: unknown[]) {
      // make sure there is always an empty new value
      if ((!newValue || !newValue.length || newValue.slice(-1)[0] !== '')
        && (!this.rows || (this.modelValue || []).length < parseInt(this.rows, 10))) {
        this.$emit('update:modelValue', [...(newValue || []), '']);
      }
    },
    onEntryChange(newValue: unknown, index: number) {
      const newArrayValue = [...(this.modelValue || [])];
      newArrayValue[index] = newValue;

      this.$emit('update:modelValue', newArrayValue);
    },
    removeEntry(index: number) {
      if (index > -1 && this.modelValue) {
        const newValue = this.modelValue.filter((x, i) => i !== index);
        this.$emit('update:modelValue', newValue);
      }
    },
  },
});
</script>
