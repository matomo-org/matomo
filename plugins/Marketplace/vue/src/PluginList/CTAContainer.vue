<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <template v-if="isSuperUser">
    <div v-if="!showActionsOnly && plugin.isMissingLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseMissing') }}
      <span
        style="white-space:nowrap"
      >(<HelpLink :plugin-name="plugin.name" />)</span>
    </div>

    <div v-else-if="!showActionsOnly && plugin.hasExceededLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseExceeded') }}
      <span
        style="white-space:nowrap"
      >(<HelpLink :plugin-name="plugin.name" />)</span>
    </div>

    <template
      v-else-if="!showActionsOnly && plugin.canBeUpdated && 0 == plugin.missingRequirements.length"
    >
      <a v-if="isAutoUpdatePossible"
         tabindex="7"
         class="btn btn-block"
         :href="linkToUpdate(plugin.name)"
      >{{ translate('CoreUpdater_UpdateTitle') }}</a>
      <div v-else
           class="alert alert-warning alert-no-background">
        {{ translate('Marketplace_CannotUpdate') }}
        <span
          style="white-space:nowrap"
        >(<HelpLink :plugin-name="plugin.name" />
          <DownloadButton
            :plugin="plugin"
            :show-or="true"
            :is-auto-update-possible="isAutoUpdatePossible"
          />)</span>
      </div>
    </template>

    <div v-else-if="!showActionsOnly && plugin.isInstalled"
         class="alert alert-success alert-no-background">
      {{ translate('General_Installed') }}

      <template v-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible">
        (<DownloadButton
          :plugin="plugin"
          :show-or="false"
          :is-auto-update-possible="isAutoUpdatePossible"
        />)
      </template>
      <template v-else-if="!plugin.isInvalid && !isMultiServerEnvironment && isPluginsAdminEnabled">
        (<a v-if="plugin.isActivated"
            tabindex="7"
            :href="linkToDeactivate(plugin.name)"
        >{{ translate('CorePluginsAdmin_Deactivate') }}</a
        ><template v-else-if="plugin.missingRequirements.length > 0">
          -
        </template
        ><a v-else
            tabindex="7"
            :href="linkToActivate(plugin.name)"
        >{{ translate('CorePluginsAdmin_Activate') }}</a>)
      </template>
    </div>

    <a v-else-if="plugin.isEligibleForFreeTrial"
       tabindex="7"
       class="btn btn-block purchaseable"
       href=""
       @click.prevent="this.$emit('startFreeTrial');"
       :title="translate('Marketplace_StartFreeTrial')"
    >{{ translate('Marketplace_StartFreeTrial') }}</a>

    <MoreDetailsButton
      v-else-if="!showActionsOnly && !plugin.isDownloadable && (
                   plugin.isPaid
                   || plugin.missingRequirements.length > 0
                   || !isAutoUpdatePossible
                 )"
      :plugin-name="plugin.name"
    />

    <!-- eslint-disable-next-line max-len-->
    <div v-else-if="!showActionsOnly && (plugin.missingRequirements.length > 0 || !isAutoUpdatePossible)"
      class="alert alert-warning alert-no-background"
    >
      {{ translate('Marketplace_CannotInstall') }}
      <span
        style="white-space:nowrap"
      >(<HelpLink :plugin-name="plugin.name" />
        <DownloadButton
          :plugin="plugin"
          :show-or="true"
          :is-auto-update-possible="isAutoUpdatePossible"
        />)</span>
    </div>

    <a v-else
       tabindex="7"
       :href="linkToInstall(plugin.name)"
       class="btn btn-block"
    >
      {{ translate('Marketplace_ActionInstall') }}
    </a>
  </template>

  <template v-else>
    <MoreDetailsButton
      v-if="!showActionsOnly"
      :plugin-name="plugin.name"
    />
  </template>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';
import DownloadButton from './DownloadButton.vue';
import HelpLink from './HelpLink.vue';
import MoreDetailsButton from './MoreDetailsButton.vue';

export default defineComponent({
  props: {
    plugin: {
      type: Object,
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
    installNonce: {
      type: String,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true,
    },
    isValidConsumer: {
      type: Boolean,
      required: true,
    },
    isMultiServerEnvironment: {
      type: Boolean,
      required: true,
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true,
    },
    isSuperUser: {
      type: Boolean,
      required: true,
    },
    showActionsOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  emits: ['startFreeTrial'],
  components: {
    DownloadButton,
    HelpLink,
    MoreDetailsButton,
  },
  directives: {
    PluginName,
  },
  methods: {
    linkToActivate(pluginName: string) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'activate',
        redirectTo: 'referrer',
        nonce: this.activateNonce,
        pluginName,
      });
    },
    linkToDeactivate(pluginName: string) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        redirectTo: 'referrer',
        nonce: this.deactivateNonce,
        pluginName,
      });
    },
    linkToInstall(pluginName: string) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'installPlugin',
        nonce: this.installNonce,
        pluginName,
      });
    },
    linkToUpdate(pluginName: string) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'updatePlugin',
        nonce: this.updateNonce,
        pluginName,
      });
    },
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
});
</script>
