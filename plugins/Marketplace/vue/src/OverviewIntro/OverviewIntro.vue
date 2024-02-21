<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline
        :feature-name="translate('CorePluginsAdmin_Marketplace')"
      >
        {{ translate('Marketplace_Marketplace') }}
      </EnrichedHeadline>
    </h2>

    <p>
      <span v-if="!isSuperUser">
        {{ restrictedMessage }}
      </span>
      <span v-else-if="showThemes">
        {{ translate('CorePluginsAdmin_ThemesDescription') }}
        <span v-html="$sanitize(installingNewThemeText)"></span>
      </span>
      <span v-else>
        {{ translate('CorePluginsAdmin_PluginsExtendPiwik') }}
        <span v-html="$sanitize(installingNewPluginText)"></span>
      </span>
      <span
        ref="noticeRemoveMarketplaceFromMenu"
        v-if="isSuperUser && inReportingMenu"
        v-html="$sanitize(noticeRemoveMarketplaceFromMenuText)"
      ></span>
    </p>
    <LicenseKey
      :is-valid-consumer="isValidConsumer"
      :is-super-user="isSuperUser"
      :is-auto-update-possible="isAutoUpdatePossible"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :has-license-key="hasLicenseKey"
      :paid-plugins-to-install-at-once="paidPluginsToInstallAtOnce"
      :install-nonce="installNonce"
    />

    <UploadPluginDialog
      :is-plugin-upload-enabled="isPluginUploadEnabled"
      :upload-limit="uploadLimit"
      :install-nonce="installNonce"
    />

    <Marketplace
      :plugin-type-options="pluginTypeOptions"
      :default-sort="defaultSort"
      :plugin-sort-options="pluginSortOptions"
      :num-available-plugins-by-type="numAvailablePluginsByType"
      :is-auto-update-possible="isAutoUpdatePossible"
      :is-super-user="isSuperUser"
      :is-multi-server-environment="isMultiServerEnvironment"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :is-valid-consumer="isValidConsumer"
      :deactivate-nonce="deactivateNonce"
      :activate-nonce="activateNonce"
      :install-nonce="installNonce"
      :update-nonce="updateNonce"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentIntro, EnrichedHeadline, MatomoUrl, translate,
} from 'CoreHome';
import { PluginName, UploadPluginDialog } from 'CorePluginsAdmin';
import Marketplace from '../Marketplace/Marketplace.vue';
import LicenseKey from '../LicenseKey/LicenseKey.vue';

export default defineComponent({
  props: {
    inReportingMenu: Boolean,
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    hasLicenseKey: Boolean,
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true,
    },
    installNonce: {
      type: String,
      required: true,
    },
    activateNonce: {
      type: String,
      required: true,
    },
    deactivateNonce: {
      type: String,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    pluginTypeOptions: {
      type: Object,
      required: true,
    },
    defaultSort: {
      type: String,
      required: true,
    },
    pluginSortOptions: {
      type: Object,
      required: true,
    },
    numAvailablePluginsByType: {
      type: Object,
      required: true,
    },
  },
  components: {
    EnrichedHeadline,
    UploadPluginDialog,
    LicenseKey,
    Marketplace,
  },
  directives: {
    ContentIntro,
    PluginName,
  },
  mounted() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      const pluginLink = (this.$refs.noticeRemoveMarketplaceFromMenu as HTMLElement)
        .querySelector('[matomo-plugin-name]') as HTMLElement;
      PluginName.mounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel',
        },
      });
    }
  },
  beforeUnmount() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      const pluginLink = (this.$refs.noticeRemoveMarketplaceFromMenu as HTMLElement)
        .querySelector('[matomo-plugin-name]') as HTMLElement;
      PluginName.unmounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel',
        },
      });
    }
  },
  computed: {
    installingNewThemeText() {
      return translate(
        'Marketplace_InstallingNewThemesViaMarketplaceOrUpload',
        '<a href="#" class="uploadPlugin">',
        '</a>',
      );
    },
    installingNewPluginText() {
      return translate(
        'Marketplace_InstallingNewPluginsViaMarketplaceOrUpload',
        '<a href="#" class="uploadPlugin">',
        '</a>',
      );
    },
    noticeRemoveMarketplaceFromMenuText() {
      return translate(
        'Marketplace_NoticeRemoveMarketplaceFromReportingMenu',
        '<a href="#" matomo-plugin-name="WhiteLabel">',
        '</a>',
      );
    },
    showThemes(): boolean {
      return MatomoUrl.hashParsed.value.pluginType as string === 'themes';
    },
    restrictedMessage(): string {
      return this.showThemes
        ? translate('Marketplace_NotAllowedToBrowseMarketplaceThemes')
        : translate('Marketplace_NotAllowedToBrowseMarketplacePlugins');
    },
  },
});
</script>
