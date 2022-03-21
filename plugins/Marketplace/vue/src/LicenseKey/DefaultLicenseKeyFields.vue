<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="valign licenseKeyText">
    <Field
      uicontrol="text"
      name="license_key"
      :full-width="true"
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      :placeholder="licenseKeyPlaceholder"
    ></Field>
  </div>
  <SaveButton
     class="valign"
     @confirm="$emit('confirm')"
     :disabled="!enableUpdate"
     :value="saveButtonText"
     id="submit_license_key"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    modelValue: String,
    isValidConsumer: Boolean,
    hasLicenseKey: Boolean,
    enableUpdate: Boolean,
  },
  emits: ['update:modelValue', 'confirm'],
  components: {
    Field,
    SaveButton,
  },
  computed: {
    licenseKeyPlaceholder() {
      return this.isValidConsumer
        ? translate('Marketplace_LicenseKeyIsValidShort')
        : translate('Marketplace_LicenseKey');
    },
    saveButtonText() {
      return this.hasLicenseKey
        ? translate('CoreUpdater_UpdateTitle')
        : translate('Marketplace_ActivateLicenseKey');
    },
  },
});
</script>
