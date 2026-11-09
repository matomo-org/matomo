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
      :action-as-button="hasActivationToggle"
    >
      <template v-if="plugin.missingRequirements.length > 0 || !isAutoUpdatePossible">
        <DownloadButton
          :plugin="plugin"
          :show-as-button="!inModal"
          :is-auto-update-possible="isAutoUpdatePossible"
        />
      </template>
      <template v-else-if="hasActivationToggle">
        <MarketplaceButton
          v-if="inModal"
          :href="activationToggleUrl"
          :outline="true"
          :full-width="true"
        >{{ activationToggleLabel }}</MarketplaceButton>
        <a v-else
           class="btn btn-block"
           :href="activationToggleUrl"
        >{{ activationToggleLabel }}</a>
      </template>
    </CTAStatus>

    <button v-else-if="plugin.isEligibleForFreeTrial && !inModal && isPluginsAdminEnabled"
       type="button"
       class="btn btn-block purchaseable"
       :title="translate('Marketplace_StartFreeTrial')"
       @click="$emit('openDetailsModal')"
    >{{ translate('Marketplace_StartFreeTrial') }}</button>

    <a v-else-if="plugin.isEligibleForFreeTrial && inModal && shopVariationUrl"
       class="btn btn-block addToCartLink" target="_blank"
       :title="translate('Marketplace_ClickToCompletePurchase')"
       rel="noreferrer noopener"
       :href="shopVariationUrl"
    >{{ translate('Marketplace_AddToCart') }}</a>

    <MoreDetailsAction
      v-else-if="!inModal && !plugin.isDownloadable && (
                   plugin.isPaid
                   || plugin.isNewBundle
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

  <template v-else-if="arePluginRequestsActive">
    <!-- not a plugin without a licence, which the Super User is shown as such: an ended trial is still installed -->
    <CTAStatus
      v-if="plugin.isInstalled && !plugin.isMissingLicense"
      tone="success"
      :label="translate('General_Installed')"
      :in-modal="inModal"
      :has-action="!inModal"
    >
      <MoreDetailsAction
        :show-as-button="true"
        :label="translate('General_MoreDetails')"
        @action="$emit('openDetailsModal')"
      />
    </CTAStatus>

    <CTAStatus
      v-else-if="requestStatusLabel"
      :tone="plugin.requestStatus === 'declined' || isTrialEnded ? 'warning' : 'success'"
      :label="requestStatusLabel"
      :in-modal="inModal"
      :has-action="!isTrialEnded"
    >
      <a
        class="ctaContainer__viewRequest"
        :href="manageRequestsUrl"
        target="_blank"
        rel="noopener"
      >{{
        translate('Marketplace_ViewRequest')
      }}</a>
    </CTAStatus>

    <a v-else-if="plugin.canTrialBeRequested && !plugin.isMissingLicense"
       class="btn btn-block purchaseable"
       href=""
       @click.prevent="$emit('requestTrial');"
       :title="translate('Marketplace_RequestTrial')"
    >{{ translate('Marketplace_RequestTrial') }}</a>

    <a v-else-if="plugin.canInstallBeRequested"
       class="btn btn-block"
       href=""
       @click.prevent="$emit('requestTrial');"
       :title="translate('Marketplace_RequestInstall')"
    >{{ translate('Marketplace_RequestInstall') }}</a>

    <MoreDetailsAction
      v-else-if="!inModal"
      :show-as-button="true"
      :label="translate('General_MoreDetails')"
      @action="$emit('openDetailsModal')"
    />
  </template>

  <a v-else-if="plugin.isTrialRequested"
     class="btn btn-block purchaseable disabled"
     href=""
     :title="translate('Marketplace_TrialRequested')"
  >{{ translate('Marketplace_TrialRequested') }}</a>

  <a v-else-if="plugin.canTrialBeRequested && !plugin.isMissingLicense"
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
import { MatomoUrl, translate } from 'CoreHome';
import MarketplaceButton from '../MarketplaceButton/MarketplaceButton.vue';
import CTAStatus from './CTAStatus.vue';
import DownloadButton from './DownloadButton.vue';
import MoreDetailsAction from './MoreDetailsAction.vue';
import manageRequestsUrl from '../ManageRequests/manageRequestsUrl';

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
    /** Users who are not super users request trials and installs, and follow them on a page. */
    arePluginRequestsActive: {
      type: Boolean,
      default: false,
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
  ],
  components: {
    CTAStatus,
    DownloadButton,
    MarketplaceButton,
    MoreDetailsAction,
  },
  computed: {
    isInstallRequest(): boolean {
      return this.plugin.requestType === 'install';
    },
    /** An approved trial whose licence is gone again, so the plugin is no longer usable. */
    isTrialEnded(): boolean {
      return this.plugin.requestStatus === 'approved'
        && !this.isInstallRequest
        && this.plugin.isMissingLicense;
    },
    /** The current user's request of the plugin, if they made one. */
    requestStatusLabel(): string {
      if (this.isTrialEnded) {
        return translate('Marketplace_TrialEnded');
      }

      const labels: Record<string, [string, string]> = {
        pending: ['Marketplace_TrialRequested', 'Marketplace_InstallRequested'],
        approved: ['Marketplace_TrialApproved', 'Marketplace_InstallApproved'],
        declined: ['Marketplace_TrialDeclined', 'Marketplace_InstallDeclined'],
      };
      const label = labels[this.plugin.requestStatus];

      return label ? translate(label[this.isInstallRequest ? 1 : 0]) : '';
    },
    manageRequestsUrl(): string {
      return manageRequestsUrl();
    },
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
        || this.hasActivationToggle;
    },
    /** Whether the installed state offers Activate or Deactivate rather than a download. */
    hasActivationToggle(): boolean {
      return this.plugin.missingRequirements.length === 0
        && this.isAutoUpdatePossible
        && !this.plugin.isInvalid
        && !this.isMultiServerEnvironment
        && this.isPluginsAdminEnabled;
    },
    activationToggleUrl(): string {
      return this.plugin.isActivated
        ? this.linkToDeactivate(this.plugin.name)
        : this.linkToActivate(this.plugin.name);
    },
    activationToggleLabel(): string {
      return this.plugin.isActivated
        ? translate('CorePluginsAdmin_Deactivate')
        : translate('CorePluginsAdmin_Activate');
    },
  },
  methods: {
    linkToActivate(pluginName: string) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'activate',
        redirectTo: 'referrer',
        referrer: this.marketplaceUrl(),
        nonce: this.activateNonce,
        pluginName,
      });
    },
    linkToDeactivate(pluginName: string) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        redirectTo: 'referrer',
        referrer: this.marketplaceUrl(),
        nonce: this.deactivateNonce,
        pluginName,
      });
    },
    linkToInstall(pluginName: string) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'installPlugin',
        referrer: this.marketplaceUrl(),
        nonce: this.installNonce,
        pluginName,
      });
    },
    linkToUpdate(pluginName: string) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'updatePlugin',
        referrer: this.marketplaceUrl(),
        nonce: this.updateNonce,
        pluginName,
      });
    },
    /**
     * This page as the one to come back to, fragment and all: the Marketplace keeps its tab, search
     * and open plugin in the hash, which the Referer header these actions would otherwise return
     * to never carries. Built from MatomoUrl rather than read off window.location so the links
     * re-render as the hash changes, instead of keeping the one they were first drawn with.
     */
    marketplaceUrl() {
      const { origin, pathname } = window.location;
      const query = MatomoUrl.stringify(MatomoUrl.urlParsed.value);
      const hash = MatomoUrl.stringify(MatomoUrl.hashParsed.value);

      return `${origin}${pathname}?${query}${hash ? `#?${hash}` : ''}`;
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
