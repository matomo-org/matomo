<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="modal" id="pluginDetailsModal">
    <div
      v-if="!isLoading"
      class="modal-content"
      :class="{ 'modal-content--simple-header': !hasHeaderMetadata }"
    >

      <div class="modal-content__header">
        <span class="btn-close modal-close"><i class="icon-close"></i></span>

        <h2>
          {{ plugin && plugin.displayName ? plugin.displayName : 'Plugin details' }}
        </h2>
        <div class="plugin-metadata-part1" v-if="hasHeaderMetadata">
          <h3 class="sr-only">Plugin details — part 1</h3>
          <dl>
            <div class="pair" v-if="showReviews">
              <dt>{{ translate('Marketplace_Reviews') }}</dt>
              <dd>
                <img class="star-icon reviews-icon"
                     src="plugins/Marketplace/images/star.svg"
                     alt=""
                /><a
                  v-on:click="scrollElementIntoView('#reviews')"
                >{{ pluginReviews.averageRating }}</a>
              </dd>
            </div>

            <div class="pair" v-if="!plugin.isBundle">
              <dt>{{ translate('CorePluginsAdmin_Version') }}</dt>
              <dd>{{ plugin.latestVersion }}</dd>
            </div>

            <div class="pair" v-if="plugin.numDownloads > 0">
              <dt>{{ translate('General_Downloads') }}</dt>
              <dd>{{ plugin.numDownloadsPretty }}</dd>
            </div>

            <div class="pair" v-if="plugin.lastUpdated && !plugin.isBundle">
              <dt>{{ translate('Marketplace_LastUpdated') }}</dt>
              <dd>{{ plugin.lastUpdated }}</dd>
            </div>

            <div class="pair" v-if="!plugin.isBundle">
              <dt>{{ translate('Marketplace_Developer') }}</dt>
              <dd>
                {{ pluginOwner }}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <div
        class="modal-content__main"
        :class="{'modal-content__main--with-free-trial': showFreeTrialDropdown }"
      >
        <div class="plugin-description">
          <MissingReqsNotice v-if="showMissingRequirementsNoticeIfApplicable" :plugin="plugin" />

          <div v-if="isMultiServerEnvironment" class="alert alert-warning">
            {{ translate('Marketplace_MultiServerEnvironmentWarning') }}
          </div>
          <div v-else-if="!isAutoUpdatePossible" class="alert alert-warning">
            {{
              translate(
                'Marketplace_AutoUpdateDisabledWarning',
                '\'[General]enable_auto_update=1\'',
                '\'config/config.ini.php\''
              )
            }}
          </div>

          <div v-if="showMissingLicenseDescription" class="alert alert-danger">
            {{ translate('Marketplace_PluginLicenseMissingDescription') }}
          </div>
          <div v-else-if="showExceededLicenseDescription" class="alert alert-warning">
            {{ translate('Marketplace_PluginLicenseExceededDescription') }}
          </div>
          <div v-else-if="plugin.licenseStatus === 'Pending' && !isMultiServerEnvironment"
               class="alert alert-warning"
               v-html="$sanitize(getPendingLicenseHelpText(plugin.displayName))"
          >
          </div>
          <div v-else-if="plugin.licenseStatus === 'Cancelled' && !isMultiServerEnvironment"
               class="alert alert-warning"
               v-html="$sanitize(getCancelledLicenseHelpText(plugin.displayName))"
          >
          </div>
          <div v-else-if="
          !plugin.hasDownloadLink
          && !isMultiServerEnvironment
          && (plugin.licenseStatus || !plugin.isPaid)"
               class="alert alert-warning"
               v-html="$sanitize(getDownloadLinkMissingHelpText(plugin.displayName))"
          >
          </div>

          <div v-html="$sanitize(pluginDescription)"></div>
        </div>

        <div class="plugin-metadata-part2">
          <hr />
          <h3 class="sr-only">Plugin details — part 2</h3>
          <dl>
            <div class="pair" v-if="!plugin.isBundle">
              <dt>{{ translate('CorePluginsAdmin_Version') }}</dt>
              <dd>{{ plugin.latestVersion }}</dd>
            </div>

            <div class="pair" v-if="pluginKeywords">
              <dt>{{ translate('Marketplace_PluginKeywords') }}</dt>
              <dd>{{ pluginKeywords.join(', ') }}</dd>
            </div>

            <template v-if="!plugin.isBundle">
              <div class="pair">
                <dt>{{ translate('Marketplace_Authors') }}</dt>
                <dd>
                  <template v-for="(author, index) in pluginAuthors" :key="`author-${index}`">
                    <a
                      v-if="author.homepage"
                      target="_blank"
                      rel="noreferrer noopener"
                      :href="author.homepage"
                    >{{ author.name }}</a>
                    <a
                      v-else-if="author.email && isValidEmail(author.email)"
                      :href="`mailto:${ encodeURIComponent(author.email) }`"
                    >{{ author.name }}</a>
                    <span v-else>{{ author.name }}</span>
                    <span v-if="index < pluginAuthors.length - 1">, </span>
                  </template>
                </dd>
              </div>

              <div class="pair">
                <dt>{{ translate('CorePluginsAdmin_Websites') }}</dt>
                <dd>
                  <a
                    v-if="plugin.homepage"
                    target="_blank"
                    rel="noreferrer noopener"
                    :href="plugin.homepage"
                  >{{ translate('Marketplace_PluginWebsite') }}</a>
                  <template v-if="pluginChangelogUrl">
                    <template v-if="plugin.homepage">, </template>
                    <a
                      target="_blank"
                      rel="noreferrer noopener"
                      :href="externalRawLink(pluginChangelogUrl)"
                    >{{ translate('CorePluginsAdmin_Changelog') }}</a>
                  </template>

                  <template v-if="plugin.repositoryUrl">
                    <template v-if="plugin.homepage || pluginChangelogUrl">, </template>
                    <a
                      target="_blank"
                      rel="noreferrer noopener"
                      :href="externalRawLink(plugin.repositoryUrl)"
                    >GitHub</a>
                  </template>
                </dd>
              </div>

              <div class="pair" v-if="pluginActivity && pluginActivity.numCommits">
                <dt>{{ translate('CorePluginsAdmin_Activity') }}</dt>
                <dd>
                  {{ plugin.activity.numCommits }} commits

                  <template
                    v-if="pluginActivity?.numContributors > 1">
                    {{
                      ' ' + translate('Marketplace_ByXDevelopers', pluginActivity.numContributors)
                    }}
                  </template>
                  <template
                    v-if="pluginActivity?.lastCommitDate">
                    {{
                      ' ' + translate('Marketplace_LastCommitTime', pluginActivity.lastCommitDate)
                    }}
                  </template>
                </dd>
              </div>

              <div class="pair" v-if="showLicenseName">
                <dt>{{ translate('Marketplace_License') }}</dt>
                <dd>
                  <a v-if="pluginLatestVersion.license?.url" rel="noreferrer noopener"
                     :href="pluginLatestVersion.license?.url"
                     target="_blank">{{ pluginLatestVersion.license?.name }}</a>
                  <span v-else>{{ pluginLatestVersion.license?.name }}</span>
                </dd>
              </div>

              <template v-if="pluginSupport.length">
                <div
                  class="pair"
                  v-for="(support, index) in pluginSupport"
                  :key="`support-${index}`"
                >
                  <template v-if="support.name && support.value">
                    <dt v-html="$sanitize(support.name)"></dt>
                    <dd v-if="this.isValidHttpUrl(support.value)">
                      <a
                        target="_blank"
                        rel="noreferrer noopener"
                        :href="externalRawLink($sanitize(support.value))"
                      >{{ $sanitize(support.value) }}</a>
                    </dd>
                    <dd v-else-if="this.isValidEmail(support.value)">
                      <a
                        :href="`mailto:${ encodeURIComponent(support.value) }`"
                      >{{ $sanitize(support.value) }}</a>
                    </dd>
                    <dd v-else v-html="$sanitize(support.value)"></dd>
                  </template>
                </div>
              </template>
            </template> <!-- v-if="!plugin.isBundle" -->
          </dl>
        </div>

        <div class="plugin-screenshots" v-if="pluginScreenshots.length">
          <hr />
          <h3>{{ translate('Marketplace_Screenshots') }}</h3>
          <div class="thumbnails">
            <figure
              v-for="screenshot in pluginScreenshots"
              :key="`screenshot-${screenshot}`"
            >
              <img :src="`${screenshot}?w=800`" width="800" alt="">
              <figcaption>{{ this.getScreenshotBaseName(screenshot) }}</figcaption>
            </figure>
          </div>
        </div>

        <div class="plugin-documentation" v-if="pluginDocumentation">
          <hr />
          <h3>{{ translate('General_Documentation') }}</h3>
          <div v-html="$sanitize(pluginDocumentation)"></div>
        </div>

        <div class="plugin-faq" v-if="pluginFaq">
          <hr />
          <h3>{{ translate('General_Faq') }}</h3>
          <div v-html="$sanitize(pluginFaq)"></div>
        </div>

        <div class="plugin-reviews" id="reviews" v-if="showReviews">
          <hr />
          <h3>{{ translate('Marketplace_Reviews') }}</h3>
          <iframe class="reviewIframe"
                  :style="pluginReviews.height ? `height: ${pluginReviews.height}px;` : '' "
                  :id="pluginReviews.embedUrl.replace(/[\W_]+/g, ' ')"
                  :src="pluginReviews.embedUrl"></iframe>
        </div>

      </div>
      <div
        class="modal-content__footer"
        :class="{'modal-content__footer--with-free-trial': showFreeTrialDropdown }"
      >
        <img v-if="showFreeTrialDropdown && isMatomoPlugin"
             class="matomo-badge matomo-badge-modal"
             src="plugins/Marketplace/images/matomo-badge.png"
             aria-label="Matomo plugin"
             alt=""
        />
        <div class="cta-container cta-container-modal">
          <div v-if="showFreeTrialDropdown" class="free-trial">
            <div class="free-trial-lead-in">{{ translate('Marketplace_TryFreeTrialTitle') }}</div>
            <select
              class="free-trial-dropdown"
              :title="`${translate('Marketplace_ShownPriceIsExclTax')} ${translate(
                'Marketplace_CurrentNumPiwikUsers',
                numUsers
                )}`"
              v-model="selectedPluginShopVariationUrl"
              @change="changeSelectedPluginShopVariationUrl"
            >
              <option v-for="(variation, index) in plugin.shop.variations" :key="`var-${index}`"
                      :value="variation.addToCartUrl"
                      :title="`${translate(
                      'Marketplace_PriceExclTax',
                      variation.price,
                      variation.currency
                    )} ${translate('Marketplace_CurrentNumPiwikUsers', numUsers)}`"
              >{{ variation.name }} - {{ variation.prettyPrice }} / {{ variation.period }}</option>
            </select>
          </div>

          <CTAContainer
            :is-super-user="isSuperUser"
            :is-plugins-admin-enabled="isPluginsAdminEnabled"
            :is-multi-server-environment="isMultiServerEnvironment"
            :is-valid-consumer="isValidConsumer"
            :is-auto-update-possible="isAutoUpdatePossible"
            :activate-nonce="activateNonce"
            :deactivate-nonce="deactivateNonce"
            :install-nonce="installNonce"
            :update-nonce="updateNonce"
            :plugin="plugin"
            :in-modal="true"
            :shop-variation-url="selectedShopVariationUrl"
            @requestTrial="emitTrialEvent('requestTrial')"
            @startFreeTrial="emitTrialEvent('startFreeTrial')"
          />
        </div>
        <img v-if="!showFreeTrialDropdown && isMatomoPlugin"
             class="matomo-badge matomo-badge-modal"
             src="plugins/Marketplace/images/matomo-badge.png"
             aria-label="Matomo plugin"
             alt=""
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl, translate, externalLink } from 'CoreHome';
import {
  IPluginShopDetails,
  IPluginShopReviews,
  IPluginShopVariation,
  PluginDetails,
  TObject,
  TObjectArray,
} from '../types';
import CTAContainer from '../PluginList/CTAContainer.vue';
import MissingReqsNotice from '../MissingReqsNotice/MissingReqsNotice.vue';
import ChangeEvent = JQuery.ChangeEvent;

const { $ } = window;

interface PluginDetailsModalState {
  isLoading: boolean;
  currentPluginShopVariationUrl: string;
}

export default defineComponent({
  components: { MissingReqsNotice, CTAContainer },
  props: {
    modelValue: {
      type: Object,
      default: () => ({}),
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
    hasSomeAdminAccess: {
      type: Boolean,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
  },
  data(): PluginDetailsModalState {
    return {
      isLoading: true,
      currentPluginShopVariationUrl: '',
    };
  },
  emits: [
    'requestTrial',
    'startFreeTrial',
    'update:modelValue',
  ],
  watch: {
    modelValue(newValue) {
      if (newValue) {
        this.showPluginDetailsDialog();
      }
    },
    isLoading(newValue) {
      if (newValue === false) {
        this.applyExternalTarget();
        this.applyIframeResize();
      }
    },
  },
  computed: {
    plugin(): PluginDetails {
      return this.modelValue as PluginDetails;
    },
    pluginLatestVersion(): TObject {
      const versions: TObjectArray = this.plugin.versions || [{}];
      return versions[versions.length - 1] as TObject;
    },
    pluginReadmeHtml(): TObject {
      return this.pluginLatestVersion?.readmeHtml as TObject || {};
    },
    pluginDescription(): string {
      return this.pluginReadmeHtml?.description as string || '';
    },
    pluginDocumentation(): string {
      return this.pluginReadmeHtml?.documentation as string || '';
    },
    pluginFaq(): string {
      return this.pluginReadmeHtml?.faq as string || '';
    },
    pluginShop(): IPluginShopDetails {
      return this.plugin.shop;
    },
    pluginShopVariations(): IPluginShopVariation[] {
      return this.pluginShop?.variations || [];
    },
    pluginReviews(): IPluginShopReviews | TObject {
      return this.pluginShop?.reviews || {};
    },
    pluginKeywords(): string[] {
      return this.plugin?.keywords || [];
    },
    pluginAuthors(): TObjectArray {
      const authors = this.plugin.authors || [];
      return authors.filter((author) => author.name);
    },
    pluginActivity(): TObject {
      return this.plugin.activity || {};
    },
    pluginChangelogUrl(): string {
      return this.plugin.changelog.url as string || '';
    },
    pluginSupport(): TObjectArray[] {
      return this.plugin.support || [];
    },
    isMatomoPlugin(): boolean {
      return ['piwik', 'matomo-org'].includes(this.plugin.owner);
    },
    pluginOwner(): string {
      return this.isMatomoPlugin ? 'Matomo' : this.plugin.owner;
    },
    showReviews(): boolean {
      return (this.pluginReviews
        && this.pluginReviews.embedUrl
        && this.pluginReviews.averageRating
      ) as boolean;
    },
    showMissingLicenseDescription(): boolean {
      return this.hasSomeAdminAccess && this.plugin.isMissingLicense;
    },
    showExceededLicenseDescription(): boolean {
      return this.hasSomeAdminAccess && this.plugin.hasExceededLicense;
    },
    showMissingRequirementsNoticeIfApplicable(): boolean {
      return this.isSuperUser && (this.plugin.isDownloadable || this.plugin.isInstalled);
    },
    showLicenseName(): boolean {
      const license: TObject = this.pluginLatestVersion?.license as TObject || {};
      return !!license.name;
    },
    showFreeTrialDropdown(): boolean {
      return (
        this.isSuperUser
        && !this.plugin.isMissingLicense
        && !this.plugin.isInstalled
        && !this.plugin.hasExceededLicense
        && this.plugin.isEligibleForFreeTrial
      ) as boolean;
    },
    pluginScreenshots(): string[] {
      return this.plugin.screenshots || [];
    },
    hasHeaderMetadata(): boolean {
      return (this.showReviews
        || !this.plugin.isBundle
        || (this.plugin.numDownloads || 0) > 0
        || (this.plugin.lastUpdated && !this.plugin.isBundle)
      ) as boolean;
    },
    pluginShopVariationsPretty(): string[] {
      return this.pluginShopVariations.map(
        (variation) => `${variation.name} - ${variation.prettyPrice} / ${variation.period}`,
      );
    },
    pluginShopRecommendedVariation(): IPluginShopVariation | null {
      const recommendedVariations = this.pluginShopVariations.filter((v) => v.recommended);
      const defaultVariation = this.pluginShopVariations.length
        ? this.pluginShopVariations[0]
        : null;
      return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
    },
    selectedPluginShopVariationUrl(): string {
      return this.currentPluginShopVariationUrl
        ? this.currentPluginShopVariationUrl
        : this.pluginShopRecommendedVariation?.addToCartUrl || '';
    },
    selectedShopVariationUrl(): string {
      return this.selectedPluginShopVariationUrl || '';
    },
  },
  methods: {
    changeSelectedPluginShopVariationUrl(event: ChangeEvent) {
      if (event) {
        this.currentPluginShopVariationUrl = event.target.value;
      }
    },
    applyExternalTarget() {
      setTimeout(() => {
        const root = this.$refs.root as HTMLElement;
        $('.modal-content__main a', root).each((index, a) => {
          const link = $(a).attr('href');

          if (link && link.indexOf('http') === 0) {
            $(a).attr('target', '_blank');
          }
        });
      });
    },
    scrollElementIntoView(selector: string) {
      setTimeout(() => {
        const root = this.$refs.root as HTMLElement;
        const elements = $(selector, root);

        if (elements.length && elements[0] && elements[0].scrollIntoView) {
          elements[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
      });
    },
    isValidEmail(email: string) {
      // regex from https://stackoverflow.com/a/46181
      // eslint-disable-next-line max-len
      return email.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    isValidHttpUrl(input: string) {
      try {
        const url = new URL(input);
        return url.protocol === 'http:' || url.protocol === 'https:';
      } catch (err) {
        return false;
      }
    },
    getProtocolAndDomain(url: string) {
      const urlObj = new URL(url);
      return `${urlObj.protocol}//${urlObj.hostname}`;
    },
    applyIframeResize() {
      setTimeout(() => {
        const { iFrameResize } = window;
        if (this.pluginReviews) {
          $(() => {
            const $iFrames = $('#pluginDetailsModal iframe.reviewIframe');
            for (let i = 0; i < $iFrames.length; i += 1) {
              // eslint-disable-next-line max-len
              iFrameResize({ checkOrigin: [this.getProtocolAndDomain(this.pluginReviews.embedUrl as string)] }, $iFrames[i]);
            }
          });
        }
      });
    },
    getScreenshotBaseName(screenshot: string) {
      const filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    emitTrialEvent(eventName: 'requestTrial'|'startFreeTrial') {
      const { plugin } = this;

      $('#pluginDetailsModal').modal('close');

      setTimeout(() => {
        this.$emit(eventName, plugin);
      }, 250);
    },
    showPluginDetailsDialog() {
      $('#pluginDetailsModal').modal({
        dismissible: true,
        onCloseEnd: () => {
          MatomoUrl.updateHash({
            ...MatomoUrl.hashParsed.value,
            showPlugin: null,
          });
          this.$emit('update:modelValue', null);
          this.isLoading = true;
        },
      }).modal('open');

      setTimeout(() => {
        this.isLoading = false;
      }, 10); // just to prevent showing the modal when the plugin data are not yet passed in
    },
    getPendingLicenseHelpText(pluginName: string) {
      return translate(
        'Marketplace_PluginLicenseStatusPending',
        pluginName,
        externalLink('https://shop.matomo.org/my-account/'),
        '</a>',
      );
    },
    getCancelledLicenseHelpText(pluginName: string) {
      return translate(
        'Marketplace_PluginLicenseStatusCancelled',
        pluginName,
        externalLink('https://shop.matomo.org/my-account/'),
        '</a>',
      );
    },
    getDownloadLinkMissingHelpText(pluginName: string) {
      return translate(
        'Marketplace_PluginDownloadLinkMissingDescription',
        pluginName,
        externalLink('https://matomo.org/faq/plugins/faq_21/'),
        '</a>',
      );
    },
  },
});
</script>
