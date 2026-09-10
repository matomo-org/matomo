<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <template v-if="isSuperUser">
    <CTAStatus
      v-if="plugin.isMissingLicense"
      tone="danger"
      :label="translate('Marketplace_LicenseMissing')"
      :in-modal="inModal"
      :has-action="!inModal"
    >
      <MoreDetailsAction
        :show-as-button="true"
        :label="translate('General_MoreDetails')"
        @action="$emit('openDetailsModal')"
      />
    </CTAStatus>

    <a v-else-if="inModal && plugin.hasExceededLicense && plugin.consumer.loginUrl"
       class="btn btn-block"
       tabindex="7"
       target="_blank"
       rel="noreferrer noopener"
       :href="externalRawLink(plugin.consumer.loginUrl)"
    >{{ translate('Marketplace_UpgradeSubscription') }}</a>

    <CTAStatus
      v-else-if="plugin.hasExceededLicense"
      tone="danger"
      :label="translate('Marketplace_LicenseExceeded')"
      :in-modal="inModal"
      :has-action="!inModal"
    >
      <MoreDetailsAction
        :show-as-button="true"
        :label="translate('General_MoreDetails')"
        @action="$emit('openDetailsModal')"
      />
    </CTAStatus>

    <template
      v-else-if="plugin.canBeUpdated && 0 == plugin.missingRequirements.length"
    >
      <a v-if="isAutoUpdatePossible && isPluginsAdminEnabled"
         tabindex="7"
         class="btn btn-block"
         :href="linkToUpdate(plugin.name)"
      >{{ translate('CoreUpdater_UpdateTitle') }}</a>
      <CTAStatus
        v-else
        tone="warning"
        :label="translate('Marketplace_CannotUpdate')"
        :in-modal="inModal"
        :has-action="!inModal || isDownloadableWithoutAutoUpdate"
      >
        <MoreDetailsAction
          v-if="!inModal"
          :show-as-button="true"
          :label="translate('General_MoreDetails')"
          @action="$emit('openDetailsModal')"
        />
        <DownloadButton
          :plugin="plugin"
          :show-as-button="!inModal"
          :is-auto-update-possible="isAutoUpdatePossible"
        />
      </CTAStatus>
    </template>

    <CTAStatus
      v-else-if="plugin.isInstalled"
      tone="success"
      :label="translate('General_Installed')"
      :in-modal="inModal"
      :has-action="hasInstalledAction"
    >
      <template v-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible">
        <DownloadButton
          :plugin="plugin"
          :show-as-button="!inModal"
          :is-auto-update-possible="isAutoUpdatePossible"
        />
      </template>
      <template v-else-if="!plugin.isInvalid && !isMultiServerEnvironment && isPluginsAdminEnabled">
        <a v-if="plugin.isActivated"
           tabindex="7"
           :class="{ 'btn btn-block': !inModal }"
           :href="linkToDeactivate(plugin.name)"
        >{{ translate('CorePluginsAdmin_Deactivate') }}</a>
        <template v-else-if="plugin.missingRequirements.length > 0">
          -
        </template>
        <a v-else
           tabindex="7"
           :class="{ 'btn btn-block': !inModal }"
           :href="linkToActivate(plugin.name)"
        >{{ translate('CorePluginsAdmin_Activate') }}</a>
      </template>
    </CTAStatus>

    <button v-else-if="plugin.isEligibleForFreeTrial && !inModal && isPluginsAdminEnabled"
       type="button"
       tabindex="7"
       class="btn btn-block purchaseable"
       :title="translate('Marketplace_StartFreeTrial')"
       @click="$emit('openDetailsModal')"
    >{{ translate('Marketplace_StartFreeTrial') }}</button>

    <a v-else-if="plugin.isEligibleForFreeTrial && inModal"
       class="btn btn-block addToCartLink" target="_blank"
       :title="translate('Marketplace_ClickToCompletePurchase')"
       rel="noreferrer noopener"
       :href="shopVariationUrl"
    >{{ translate('Marketplace_AddToCart') }}</a>

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

    <CTAStatus
      v-else-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible"
      tone="warning"
      :label="translate('Marketplace_CannotInstall')"
      :in-modal="inModal"
      :has-action="!inModal || isDownloadableWithoutAutoUpdate"
    >
      <MoreDetailsAction
        v-if="!inModal"
        :show-as-button="true"
        :label="translate('General_MoreDetails')"
        @action="$emit('openDetailsModal')"
      />
      <DownloadButton
        :plugin="plugin"
        :show-as-button="!inModal"
        :is-auto-update-possible="isAutoUpdatePossible"
      />
    </CTAStatus>

    <a v-else-if="isPluginsAdminEnabled && plugin.hasDownloadLink"
       tabindex="7"
       :href="linkToInstall(plugin.name)"
       class="btn btn-block"
    >
      {{ translate('Marketplace_ActionInstall') }}
    </a>

    <template v-else>
      <MoreDetailsAction
        v-if="!inModal"
        :show-as-button="true"
        :label="translate('General_MoreDetails')"
        @action="$emit('openDetailsModal')"
      />
    </template>
  </template>

  <a v-else-if="plugin.isTrialRequested"
     tabindex="7"
     class="btn btn-block purchaseable disabled"
     href=""
     :title="translate('Marketplace_TrialRequested')"
  >{{ translate('Marketplace_TrialRequested') }}</a>

  <a v-else-if="plugin.canTrialBeRequested && !plugin.isMissingLicense"
     tabindex="7"
     class="btn btn-block purchaseable"
     href=""
     @click.prevent="$emit('requestTrial');"
     :title="translate('Marketplace_RequestTrial')"
  >{{ translate('Marketplace_RequestTrial') }}</a>

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
import CTAStatus from './CTAStatus.vue';
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
    shopVariationUrl: {
      type: String,
      required: false,
      default: '',
    },
  },
  emits: [
    'openDetailsModal',
    'requestTrial',
    'startFreeTrial',
  ],
  components: {
    CTAStatus,
    DownloadButton,
    MoreDetailsAction,
  },
  computed: {
    /** The one case DownloadButton renders anything: an update it cannot apply for you. */
    isDownloadableWithoutAutoUpdate(): boolean {
      return this.plugin.missingRequirements.length === 0
        && this.plugin.isDownloadable
        && !this.isAutoUpdatePossible;
    },
    /**
     * Whether the installed state offers anything beside the word "Installed". Both template
     * branches in one expression, because CTAStatus draws the brackets before rendering them.
     */
    hasInstalledAction(): boolean {
      return this.plugin.missingRequirements.length > 0
        || !this.isAutoUpdatePossible
        || (!this.plugin.isInvalid && !this.isMultiServerEnvironment && this.isPluginsAdminEnabled);
    },
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
        idSite: MatomoUrl.parsed.value.idSite,
        ...params,
      })}`;
    },
  },
});
</script>
