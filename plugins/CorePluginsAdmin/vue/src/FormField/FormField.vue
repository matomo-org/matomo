<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="form-group row"
    v-show="formField.showField"
  >
    <h3
      v-if="formField.introduction"
      class="col s12"
    >{{ formField.introduction }}</h3>
    <div
      class="col s12"
      :class="{
        'input-field': formField.uiControl !== 'checkbox' && formField.uiControl !== 'radio',
        'file-field': formField.uiControl === 'file',
        'm6': !formField.fullWidth
      }"
      ng-include="formField.templateFile"
      onload="templateLoaded()"
    >
    </div>
    <div
      class="col s12"
      :class="{'m6': !formField.fullWidth}"
    >
      <div
        v-if="showFormHelp"
        class="form-help"
      >
        <div
          v-show="formField.description"
          class="form-description"
        >{{ formField.description }}</div>
        <span
          class="inline-help"
          v-html="$sanitize(formField.inlineHelp)"
        />
        <span v-show="showDefaultValue">
          <br />
          {{ translate('General_Default') }}:
          <span>{{ formField.defaultValuePretty.substring(0, 50) }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    formField: Object,
    allSettings: String,
  },
  computed: {
    showFormHelp() {
      return this.formField.description
        || this.formField.inlineHelp
        || (this.formField.defaultValue
          && this.formField.uiControl != 'checkbox'
          && this.formField.uiControl != 'radio');
    },
    showDefaultValue() {
      return this.formField.defaultValuePretty
        && this.formField.uiControl != 'checkbox'
        && this.formField.uiControl != 'radio';
    },
  },
});
</script>
