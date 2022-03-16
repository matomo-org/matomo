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
      {% if isValidConsumer %}
      {% if isSuperUser %}
      {{ raw(translate('Marketplace_PaidPluginsWithLicenseKeyIntro', '')) }}
      <br />
      <div class="licenseToolbar valign-wrapper">
        {{ raw(defaultLicenseKeyFields) }}
        <SaveButton
          class="valign"
          id="remove_license_key"
          @confirm="removeLicense()"
          :value="e(translate('Marketplace_RemoveLicenseKey'), 'html_attr')"
        />
        <a
          class="btn valign"
          :href="linkTo({'action': 'subscriptionOverview'})"
        >
          {{ translate('Marketplace_ViewSubscriptions') }}
        </a>
        {% if isAutoUpdatePossible and isPluginsAdminEnabled and paidPluginsToInstallAtOnce|length %}
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
            {% for pluginName in paidPluginsToInstallAtOnce %}
            <li>{{ pluginName }}</li>
            {% endfor %}
          </ul>
          <p>
            <input
              role="install"
              type="button"
              :data-href="linkTo({'action': 'installAllPaidPlugins', 'nonce': installNonce})"
              :value="translate('Marketplace_InstallAllPurchasedPluginsAction', paidPluginsToInstallAtOnce).length"
            >
            <input
              role="cancel"
              type="button"
              :value="translate('General_Cancel')"
            />
            </input>
          </p>
        </div>
        {% endif %}
      </div>
      <ActivityIndicator :loading="isUpdating" />
      {% endif %}
      {% else %}
      {% if isSuperUser %}
      {{ raw(translate('Marketplace_PaidPluginsNoLicenseKeyIntro', '<a target="'_blank'" rel="'noreferrer" noopener' href="'https://matomo.org/recommends/premium-plugins/'">', '</a>')) }}
      <br />
      <div class="licenseToolbar valign-wrapper">
        {{ raw(defaultLicenseKeyFields) }}
      </div>
      <ActivityIndicator :loading="isUpdating" />
      {% else %}
      {{ raw(translate('Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess', '<a target="'_blank'" rel="'noreferrer" noopener' href="'https://matomo.org/recommends/premium-plugins/'">', '</a>')) }}
      {% endif %}
      {% endif %}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  translate,
  ActivityIndicator
} from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';


interface LicenseKeyState {
  licenseKey: string;
  enableUpdate: boolean;
  isUpdating: boolean;
}

export default defineComponent({
  props: {
    isValidConsumer: {
      type: null, // TODO
      required: true,
    },
    isSuperUser: {
      type: null, // TODO
      required: true,
    },
    defaultLicenseKeyFields: {
      type: null, // TODO
      required: true,
    },
    isAutoUpdatePossible: {
      type: null, // TODO
      required: true,
    },
    isPluginsAdminEnabled: {
      type: null, // TODO
      required: true,
    },
    paidPluginsToInstallAtOnce: {
      type: null, // TODO
      required: true,
    },
    pluginName: {
      type: null, // TODO
      required: true,
    },
    installNonce: {
      type: null, // TODO
      required: true,
    },
  },
  components: {
    SaveButton,
    ActivityIndicator,
  },
  data(): LicenseKeyState {
    return {
      licenseKey: '',
      enableUpdate: false,
      isUpdating: false,
    };
  },
  methods: {
    // TODO
    updateLicenseKey(action, licenseKey, onSuccessMessage) {
      AjaxHelper.withTokenInUrl();
      AjaxHelper.post({
        module: 'API',
        method: `Marketplace.${action}`,
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }).then((response) => {
        this.isUpdating = false;
    
        if (response && response.value) {
          const UI = require('piwik/UI');
    
          const notification = new UI.Notification();
          notification.show(onSuccessMessage, {
            context: 'success'
          });
          Matomo.helper.redirect();
        }
      }, () => {
        this.isUpdating = false;
      });
    },
    // TODO
    removeLicense() {
      Matomo.helper.modalConfirm('#confirmRemoveLicense', {
        yes: () => {
          this.enableUpdate = false;
          this.isUpdating = true;
          this.updateLicenseKey('deleteLicenseKey', '', translate('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
  },
});
</script>
