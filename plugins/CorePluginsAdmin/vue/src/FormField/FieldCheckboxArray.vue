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
          @change="updateCheckboxArrayValue($index, $event)"
          v-bind="uiControlAttributes"
          type="checkbox"
          :id="`${name}${checkboxModel.key}`"
          :name="checkboxModel.name"
          :checked="!!modelValue[$index.toString()]"
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

export default defineComponent({
  props: {
    modelValue: Object,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
  },
  emits: ['update:modelValue'],
  methods: {
    updateCheckboxArrayValue(index: string, event: Event) {
      if (event.target.checked !== this.modelValue[index]) {
        this.$emit('update:modelValue', { ...this.modelValue, [index]: event.target.checked });
      }
    },
  },
});
</script>
