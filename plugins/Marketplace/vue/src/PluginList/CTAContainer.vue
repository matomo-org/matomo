<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <template v-if="isSuperUser">
    <div v-if="plugin.isMissingLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseMissing') }}
      <span
        style="white-space:nowrap"
      >(<a tabindex="7"
           class="plugin-details"
           href="#"
           v-plugin-name="{ pluginName: plugin.name }"
           :title="translate('General_MoreDetails')"
      >{{ translate('General_Help') }}</a>)</span>
    </div>

    <div v-else-if="plugin.hasExceededLicense"
         class="alert alert-danger alert-no-background">
      {{ translate('Marketplace_LicenseExceeded') }}
      <span
        style="white-space:nowrap"
      >(<a tabindex="7"
           class="plugin-details"
           href="#"
           v-plugin-name="{ pluginName: plugin.name }"
           :title="translate('General_MoreDetails')"
      >{{ translate('General_Help') }}</a>)</span>
    </div>

    <a v-else-if="plugin.canBeUpdated && 0 == plugin.missingRequirements.length
                                  && isAutoUpdatePossible"
       tabindex="7" class="btn btn-block"
       :href="linkTo({
                          module: 'Marketplace',
                          action: 'updatePlugin',
                          pluginName: plugin.name,
                          nonce: updateNonce
                       })">
      {{ translate('CoreUpdater_UpdateTitle') }}
    </a>

    <template v-else-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible">

      <div v-if="plugin.canBeUpdated && 0 === plugin.missingRequirements.length"
           class="alert alert-warning alert-no-background">
        {{ translate('Marketplace_CannotUpdate') }}
        <span
          style="white-space:nowrap"
        >(<a tabindex="7"
             class="plugin-details"
             href="#"
             v-plugin-name="{ pluginName: plugin.name }"
             :title="translate('General_MoreDetails')"
        >{{ translate('General_Help') }}</a><DownloadButton
          :plugin="plugin"
          :show-or="true"
          :is-auto-update-possible="isAutoUpdatePossible"
        />)</span>
      </div>

      <div v-else-if="plugin.isInstalled"
           class="alert alert-success alert-no-background">
        {{ translate('General_Installed') }}
        (<DownloadButton
        :plugin="plugin"
        :show-or="false"
        :is-auto-update-possible="isAutoUpdatePossible"
      />)
      </div>

      <a v-else-if="plugin.isEligibleForFreeTrial && isValidConsumer"
         tabindex="7"
         class="btn btn-block purchaseable"
         href="#"
         v-plugin-start-free-trial="{ pluginName: plugin.name }"
         :title="translate('Marketplace_StartFreeTrial')"
      >
        {{ translate('Marketplace_StartFreeTrial') }}
      </a>

      <a v-else-if="!plugin.isDownloadable"
         tabindex="7"
         class="btn btn-block"
         href="#"
         v-plugin-name="{ pluginName: plugin.name }"
         :title="translate('General_MoreDetails')"
      >
        {{ translate('General_MoreDetails') }}
      </a>

      <div v-else
           class="alert alert-warning alert-no-background">
        {{ translate('Marketplace_CannotInstall') }}
        <span
          style="white-space:nowrap"
        >(<a tabindex="7"
             class="plugin-details"
             href="#"
             v-plugin-name="{ pluginName: plugin.name }"
             :title="translate('General_MoreDetails')"
        >{{ translate('General_Help') }}</a><DownloadButton
          :plugin="plugin"
          :show-or="false"
          :is-auto-update-possible="isAutoUpdatePossible"
        />)</span>
      </div>
    </template>

    <div v-else-if="plugin.isInstalled"
         class="alert alert-success alert-no-background">
      {{ translate('General_Installed') }}

      <template v-if="!plugin.isInvalid && !isMultiServerEnvironment && isPluginsAdminEnabled">
        (<a v-if="plugin.isActivated"
           tabindex="7"
           :href="linkTo({
                              module: 'CorePluginsAdmin',
                              action: 'deactivate',
                              pluginName: plugin.name,
                              nonce: deactivateNonce,
                              redirectTo: 'referrer' })"
        >{{ translate('CorePluginsAdmin_Deactivate') }}</a
        ><template v-else-if="plugin.missingRequirements.length > 0">
          -
        </template
        ><a v-else
           tabindex="7"
           :href="linkTo({
                              module: 'CorePluginsAdmin',
                              action: 'activate',
                              pluginName: plugin.name,
                              nonce: activateNonce,
                              redirectTo: 'referrer' })"
        >{{ translate('CorePluginsAdmin_Activate') }}</a>)
      </template>
    </div>

    <a v-else-if="plugin.isEligibleForFreeTrial && isValidConsumer"
       tabindex="7"
       class="btn btn-block purchaseable"
       href="#"
       v-plugin-start-free-trial="{ pluginName: plugin.name }"
       :title="translate('Marketplace_StartFreeTrial')"
    >
      {{ translate('Marketplace_StartFreeTrial') }}
    </a>

    <a v-else-if="plugin.isPaid && !plugin.isDownloadable"
       tabindex="7"
       class="btn btn-block"
       href="#"
       v-plugin-name="{ pluginName: plugin.name }"
       :title="translate('General_MoreDetails')"
    >
      {{ translate('General_MoreDetails') }}
    </a>

    <a v-else
       tabindex="7"
       :href="linkTo({
                          'module': 'Marketplace',
                          'action': 'installPlugin',
                          'pluginName': plugin.name,
                          'nonce': installNonce
                       })"
       class="btn btn-block">
      {{ translate('Marketplace_ActionInstall') }}
    </a>
  </template>
  <a v-else
     tabindex="7"
     class="btn btn-block"
     href="#"
     v-plugin-name="{ pluginName: plugin.name }"
     :title="translate('General_MoreDetails')"
  >
    {{ translate('General_MoreDetails') }}
  </a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import { PluginName, PluginStartFreeTrial } from 'CorePluginsAdmin';
import DownloadButton from './DownloadButton.vue';

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
  },
  components: {
    DownloadButton,
  },
  directives: {
    PluginName,
    PluginStartFreeTrial,
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
});
</script>
