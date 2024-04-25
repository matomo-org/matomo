<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <template v-if="isSuperUser">
    <div v-if="plugin.isMissingLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseMissing') }}
      <span v-if="!inModal"
        style="white-space:nowrap"
      >(<MoreDetailsAction @action="$emit('openDetailsModal')"/>)</span>
    </div>

    <div v-else-if="plugin.hasExceededLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseExceeded') }}
      <span v-if="!inModal"
        style="white-space:nowrap"
      >(<MoreDetailsAction @action="$emit('openDetailsModal')"/>)</span>
    </div>

    <template
      v-else-if="plugin.canBeUpdated && 0 == plugin.missingRequirements.length"
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
          v-if="!inModal ||
              (plugin.missingRequirements.length === 0
              && plugin.isDownloadable && !isAutoUpdatePossible
              )"
        >(<MoreDetailsAction @action="$emit('openDetailsModal')" v-if="!inModal" />
          <DownloadButton
            :plugin="plugin"
            :show-or="!inModal"
            :is-auto-update-possible="isAutoUpdatePossible"
          />)</span>
      </div>
    </template>

    <div v-else-if="plugin.isInstalled"
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
       @click.prevent="$emit('startFreeTrial');"
       @keyup.enter="$emit('startFreeTrial')"
       :title="translate('Marketplace_StartFreeTrial')"
    >{{ translate('Marketplace_StartFreeTrial') }}</a>

    <MoreDetailsAction
      v-else-if="!inModal && !plugin.isDownloadable && (
                   plugin.isPaid
                   || plugin.missingRequirements.length > 0
                   || !isAutoUpdatePossible
                 )"
      :show-as-button="true"
      :label="translate('General_MoreDetails')"
      @action="$emit('openDetailsModal')"
    />

    <div
      v-else-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible"
      class="alert alert-warning alert-no-background"
    >
      {{ translate('Marketplace_CannotInstall') }}
      <span
        style="white-space:nowrap"
        v-if="!inModal ||
              (plugin.missingRequirements.length === 0
              && plugin.isDownloadable && !isAutoUpdatePossible
              )"
      >(<MoreDetailsAction @action="$emit('openDetailsModal')" v-if="!inModal" />
        <DownloadButton
          :plugin="plugin"
          :show-or="!inModal"
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
    <MoreDetailsAction
      v-if="!inModal"
      :show-as-button="true"
      :label="translate('General_MoreDetails')"
      @action="$emit('openDetailsModal')"
    />
  </template>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';
import DownloadButton from './DownloadButton.vue';
import MoreDetailsAction from './MoreDetailsAction.vue';

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
    inModal: {
      type: Boolean,
      required: true,
    },
  },
  emits: ['startFreeTrial', 'openDetailsModal'],
  components: {
    MoreDetailsAction,
    DownloadButton,
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
