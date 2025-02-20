<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Marketplace_LicenseKey')"
    class="manage-license-key"
  >
    <div class="manage-license-key-intro" v-html="$sanitize(manageLicenseKeyIntro)"></div>

    <InstallAllPaidPluginsButton
      :disabled="isUpdating"
    />

    <div class="manage-license-key-input">
      <Field
        uicontrol="text"
        name="license_key"
        v-model="licenseKey"
        :placeholder="licenseKeyPlaceholder"
        :full-width="true"
      ></Field>
    </div>
    <SaveButton
      @confirm="updateLicense()"
      :value="saveButtonText"
      :disabled="!licenseKey || isUpdating"
      id="submit_license_key"
    />
    <SaveButton
      v-if="hasValidLicense"
      id="remove_license_key"
      @confirm="removeLicense()"
      :disabled="isUpdating"
      :value="translate('General_Remove')"
    />
    <ActivityIndicator :loading="isUpdating" />
  </ContentBlock>

  <div class="ui-confirm" id="confirmRemoveLicense" ref="confirmRemoveLicense">
    <h2>{{ translate('Marketplace_ConfirmRemoveLicense') }}</h2>
    <input role="yes" type="button" :value="translate('General_Yes')"/>
    <input role="no" type="button" :value="translate('General_No')"/>
  </div>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  ActivityIndicator,
  ContentBlock,
  MatomoUrl,
  NotificationsStore,
  externalLink,
} from 'CoreHome';
import { Field, SaveButton, InstallAllPaidPluginsButton } from 'CorePluginsAdmin';

interface LicenseKeyState {
  licenseKey: string;
  hasValidLicense: boolean;
  isUpdating: boolean;
}

export default defineComponent({
  props: {
    hasValidLicenseKey: Boolean,
  },
  components: {
    Field,
    ContentBlock,
    SaveButton,
    ActivityIndicator,
    InstallAllPaidPluginsButton,
  },
  data(): LicenseKeyState {
    return {
      licenseKey: '',
      hasValidLicense: this.hasValidLicenseKey,
      isUpdating: false,
    };
  },
  methods: {
    updateLicenseKey(action: string, licenseKey: string, onSuccessMessage: string) {
      NotificationsStore.remove('ManageLicenseKeySuccess');
      AjaxHelper.post(
        {
          module: 'API',
          method: `Marketplace.${action}`,
          format: 'JSON',
        },
        {
          licenseKey: this.licenseKey,
        },
        { withTokenInUrl: true },
      ).then((response) => {
        this.isUpdating = false;

        if (response && response.value) {
          NotificationsStore.show({
            id: 'ManageLicenseKeySuccess',
            message: onSuccessMessage,
            context: 'success',
            type: 'toast',
          });
          this.hasValidLicense = action !== 'deleteLicenseKey';
          this.licenseKey = '';
        }
      }, () => {
        this.isUpdating = false;
      });
    },
    removeLicense() {
      Matomo.helper.modalConfirm(this.$refs.confirmRemoveLicense as HTMLElement, {
        yes: () => {
          this.isUpdating = true;
          this.updateLicenseKey(
            'deleteLicenseKey',
            '',
            translate('Marketplace_LicenseKeyDeletedSuccess'),
          );
        },
      });
    },
    updateLicense() {
      this.isUpdating = true;

      this.updateLicenseKey(
        'saveLicenseKey',
        this.licenseKey,
        translate('Marketplace_LicenseKeyActivatedSuccess'),
      );
    },
  },
  computed: {
    manageLicenseKeyIntro() {
      const marketplaceLink = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'overview',
      })}`;

      return translate(
        'Marketplace_ManageLicenseKeyIntro',
        `<a href="${marketplaceLink}">`,
        '</a>',
        externalLink('https://shop.matomo.org/my-account'),
        '</a>',
      );
    },
    licenseKeyPlaceholder() {
      return this.hasValidLicense
        ? translate('Marketplace_LicenseKeyIsValidShort')
        : translate('Marketplace_LicenseKey');
    },
    saveButtonText() {
      return this.hasValidLicense
        ? translate('CoreUpdater_UpdateTitle')
        : translate('Marketplace_ActivateLicenseKey');
    },
  },
});
</script>
