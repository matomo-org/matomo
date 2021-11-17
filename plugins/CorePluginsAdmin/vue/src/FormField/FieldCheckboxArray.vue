<template>
  <div>
    <label class="fieldRadioTitle" v-show="title">{{ title }}</label>
    <p
      v-for="(checkboxModel, $index) in availableOptions"
      :key="$index"
      class="checkbox"
    >
      <label>
        <input
          :value="checkboxModel.key"
          :checked="!!checkboxStates[$index]"
          @change="onChangeState($index)"
          v-bind="uiControlAttributes"
          type="checkbox"
          :id="`${name}${checkboxModel.key}`"
          :name="checkboxModel.name"
        />
        <span>{{ checkboxModel.value }}</span>

        <span class="form-description" v-show="checkboxModel.description">
          {{ checkboxModel.description }}
        </span>
      </label>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { processCheckboxAndRadioAvailableValues } from './utilities';

// TODO: how often is this called
function getCheckboxStates(availableOptions, modelValue) {
  return Object.values(availableOptions).map((o) => modelValue.indexOf(o.key) !== -1);
}

export default defineComponent({
  props: {
    modelValue: Object,
    name: String,
    title: String,
    availableValues: Array,
    uiControlAttributes: Object,
    type: String,
  },
  emits: ['update:modelValue'],
  computed: {
    checkboxStates() {
      return getCheckboxStates(this.availableOptions, this.modelValue);
    },
    availableOptions() {
      return processCheckboxAndRadioAvailableValues(this.availableValues, this.type);
    },
  },
  mounted() {
    window.Materialize.updateTextFields();
  },
  methods: {
    onChange(changedIndex: number) {
      const checkboxStates = [...this.checkboxStates];
      checkboxStates[changedIndex] = !checkboxStates[changedIndex];

      const newValue = [];
      Object.values(this.availableOptions).forEach((option, index) => {
        if (checkboxStates[index]) {
          newValue.push(option.key);
        }
      });

      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
