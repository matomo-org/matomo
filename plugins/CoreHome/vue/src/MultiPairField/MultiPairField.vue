<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="multiPairField form-group">
    <div
      v-for="(index, item) in modelValue"
      class="multiPairFieldTable multiple valign-wrapper"
      :class="{ [`multiPairFieldTable${index}`]: true, [`has${fieldCount}Fields`]: true }"
      :key="index"
    >
      <Field
        :full-width="true"
        v-if="field1"
        class="fieldUiControl fieldUiControl1"
        :class="{ hasMultiFields: field1.templateFile && field2.templateFile }"
        v-model="item.field1.key"
        :options="field1.availableValues"
        @change="onEntryChange()"
        :placeholder="' '"
        :uicontrol="field1.uiControl"
        :name="`${name}-p1-${index}`"
        :data-title="field1.title"
      >
      </Field>
      <Field
        :full-width="true"
        v-if="field2"
        class="fieldUiControl fieldUiControl2"
        :options="field2.availableValues"
        @change="onEntryChange()"
        v-model="item.field2.key"
        :placeholder="' '"
        :uicontrol="field2.uiControl"
        :name="`${name}-p2-${index}`"
        :data-title="field2.title"
      >
      </Field>
      <Field
        :full-width="true"
        v-if="field3"
        class="fieldUiControl fieldUiControl3"
        :options="field3.availableValues"
        @change="onEntryChange()"
        v-model="item.field3.key"
        :placeholder="' '"
        :uicontrol="field3.uiControl"
        :data-title="field3.title"
      >
      </Field>
      <Field
        :full-width="true"
        v-if="field4"
        class="fieldUiControl fieldUiControl4"
        :options="field4.availableValues"
        @change="onEntryChange()"
        v-model="item.field4.key"
        :placeholder="' '"
        :uicontrol="field4.uiControl"
        :data-title="field4.title"
      >
      </Field>
      <span
        @click="removeEntry(index)"
        class="icon-minus valign"
        v-show="index + 1 !== modelValue.length"
        :title="translate('General_Remove')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineAsyncComponent, defineComponent } from 'vue';

// async since this is a a recursive component
const Field = defineAsyncComponent(() => {
  return new Promise((resolve) => {
    window.$(document).ready(() => resolve(window.CorePluginsAdmin.Field));
  });
});

// TODO: fieldCount is computed of field
export default defineComponent({
  props: {
    modelValue: Array,
    name: String,
    field1: String,
    field2: String,
    field3: String,
    field4: String,
  },
  components: {
    Field,
  },
  computed: {
    fieldCount() {
      if (this.field1 && this.field2 && this.field3 && this.field4) {
        return 4;
      } else if (this.field1 && this.field2 && this.field3) {
        return 3;
      } else if (this.field1 && this.field2) {
        return 2;
      } else if (this.field1) {
        return 1;
      } else {
        return 0;
      }
    },
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue(newValue) {
      // make sure there is always an empty new value
      if (!newValue.length || this.isEmptyValue(newValue.pop())) {
        this.$emit('update:modelValue', [...newValue, this.makeEmptyValue()]);
        // TODO
      }
      // TODO
      /*
                  if (angular.isArray($scope.formValue)) {
                var obj = {};
                if ($scope.field1 && $scope.field1.key) {
                    obj[$scope.field1.key] = '';
                }
                if ($scope.field2 && $scope.field2.key) {
                    obj[$scope.field2.key] = '';
                }
                if ($scope.field3 && $scope.field3.key) {
                    obj[$scope.field3.key] = '';
                }
                if ($scope.field4 && $scope.field4.key) {
                    obj[$scope.field4.key] = '';
                }
                $scope.formValue.push(obj);
            }

       */
    },
  },
  methods: {
    onEntryChange() {
      // TODO
    },
    addEntry() {
      // TODO
    },
    removeEntry(index: number) {
      if (index > -1) {
        const newValue = this.modelValue.filter((x, i) => i !== index);
        this.$emit('update:modelValue', newValue);
      }
    },
    isEmptyValue(value: Record<string, unknown>) {
      // TODO
    },
    makeEmptyValue(): Record<string, unknown> {
      // TODO
    },
  },
});
</script>
