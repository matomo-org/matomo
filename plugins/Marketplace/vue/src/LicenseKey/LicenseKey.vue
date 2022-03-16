<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- REMOVE DUPLICATE CODE IN TEMPLATE
- test in UI
- check uses:
  ./plugins/Marketplace/templates/licenseform.twig
  ./plugins/Marketplace/angularjs/licensekey/licensekey.controller.js
- create PR
</todo>

<template>
  <div class="marketplace-max-width">
    <div class="marketplace-paid-intro">
      <div v-if="isValidConsumer">
        <div v-if="isSuperUser">
          {{ translate('Marketplace_PaidPluginsWithLicenseKeyIntro', '') }}
          <br />
          <div class="licenseToolbar valign-wrapper">
            <DefaultLicenseKeyFields
              :model-value="licenseKey"
              @update:model-value="licenseKey = $event; updatedLicenseKey()"
              @confirm="updateLicense()"
            />
            <SaveButton
              class="valign"
              id="remove_license_key"
              @confirm="removeLicense()"
              :value="translate('Marketplace_RemoveLicenseKey')"
            />
            <a
              class="btn valign"
              :href="subscriptionOverviewLink"
            >
              {{ translate('Marketplace_ViewSubscriptions') }}
            </a>
            <div v-if="showInstallAllPaidPlugins">
              <a
                href="javascript:;"
                class="btn installAllPaidPlugins valign"
              >
                {{ translate('Marketplace_InstallPurchasedPlugins') }}
              </a>
              <div
                class="ui-confirm"
                id="installAllPaidPluginsAtOnce"
              >
                <h2>{{ translate('Marketplace_InstallAllPurchasedPlugins') }}</h2>
                <p>
                  {{ translate('Marketplace_InstallThesePlugins') }}
                  <br /><br />
                </p>
                <ul>
                  <li v-for="pluginName in paidPluginsToInstallAtOnce" :key="pluginName">
                    {{ pluginName }}
                  </li>
                </ul>
                <p>
                  <input
                    role="install"
                    type="button"
                    :href="installAllPaidPluginsLink"
                    :value="translate(
                      'Marketplace_InstallAllPurchasedPluginsAction',
                      paidPluginsToInstallAtOnce,
                    ).length"
                  />
                  <input
                    role="cancel"
                    type="button"
                    :value="translate('General_Cancel')"
                  />
                </p>
              </div>
            </div>
          </div>
          <ActivityIndicator :loading="isUpdating" />
        </div>
      </div>
      <div v-else>
        <div v-if="isSuperUser">
          <span v-html="$sanitize(noLicenseKeyIntroText)"></span>
          <br />
          <div class="licenseToolbar valign-wrapper">
            <DefaultLicenseKeyFields
              :model-value="licenseKey"
              @update:model-value="licenseKey = $event; updatedLicenseKey()"
              @confirm="updateLicense()"
            />
          </div>
          <ActivityIndicator :loading="isUpdating" />
        </div>
        <div v-else>
          <span v-html="$sanitize(noLicenseKeyIntroNoSuperUserAccessText)"></span>
        </div>
      </div>
    </div>

    <div class="ui-confirm" id="confirmRemoveLicense" ref="confirmRemoveLicense">
      <h2>{{ 'Marketplace_ConfirmRemoveLicense'|translate }}</h2>
      <input role="yes" type="button" value="{{ 'General_Yes'|translate }}"/>
      <input role="no" type="button" value="{{ 'General_No'|translate }}"/>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  ActivityIndicator,
  MatomoUrl,
  NotificationsStore,
} from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';
import DefaultLicenseKeyFields from './DefaultLicenseKeyFields';

interface LicenseKeyState {
  licenseKey: string;
  enableUpdate: boolean;
  isUpdating: boolean;
}

export default defineComponent({
  props: {
    isValidConsumer: {
      type: Boolean,
      required: true,
    },
    isSuperUser: {
      type: Boolean,
      required: true,
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true,
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true,
    },
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true,
    },
    installNonce: {
      type: String,
      required: true,
    },
  },
  components: {
    SaveButton,
    ActivityIndicator,
    DefaultLicenseKeyFields,
  },
  data(): LicenseKeyState {
    return {
      licenseKey: '',
      enableUpdate: false,
      isUpdating: false,
    };
  },
  methods: {
    updateLicenseKey(action: string, licenseKey: string, onSuccessMessage: string) {
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
            message: onSuccessMessage,
            context: 'success',
            type: 'transient',
          });
          Matomo.helper.redirect();
        }
      }, () => {
        this.isUpdating = false;
      });
    },
    removeLicense() {
      Matomo.helper.modalConfirm(this.refs.confirmRemoveLicense as HTMLElement, {
        yes: () => {
          this.enableUpdate = false;
          this.isUpdating = true;
          this.updateLicenseKey(
            'deleteLicenseKey',
            '',
            translate('Marketplace_LicenseKeyDeletedSuccess'),
          );
        },
      });
    },
    showInstallAllPaidPlugins() {
      return this.isAutoUpdatePossible
        && this.isPluginsAdminEnabled
        && this.paidPluginsToInstallAtOnce.length;
    },
    updatedLicenseKey() {
      this.enableUpdate = !!this.licenseKey;
    },
    updateLicense() {
      this.enableUpdate = false;
      this.isUpdating = true;

      this.updateLicenseKey(
        'saveLicenseKey',
        this.licenseKey,
        translate('Marketplace_LicenseKeyActivatedSuccess'),
      );
    },
  },
  computed: {
    subscriptionOverviewLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        action: 'subscriptionOverview',
      })}`;
    },
    noLicenseKeyIntroText() {
      return translate(
        'Marketplace_PaidPluginsNoLicenseKeyIntro',
        '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/recommends/premium-plugins/">',
        '</a>',
      );
    },
    noLicenseKeyIntroNoSuperUserAccessText() {
      return translate(
        'Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess',
        '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/recommends/premium-plugins/">',
        '</a>',
      );
    },
    installAllPaidPluginsLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        action: 'installAllPaidPlugins',
        nonce: this.installNonce,
      })}`
    },
  },
});
</script>
