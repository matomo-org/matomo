<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<!-- TODO: decide if/how to wrap long lines or keep the max-len rule exclusion -->
<!-- eslint-disable max-len -->
<template>
  <div ref="root" class="modal" id="pluginDetails">
    <p class="btn-close modal-close"><i class="icon-close"></i></p>

    <div v-if="isLoading" class="modal-content">
      <div class="Piwik_Popover_Loading">
        <div class="Piwik_Popover_Loading_Name">Loading details...</div>
      </div>
    </div>

    <div v-else class="modal-content">

      <h2>{{ plugin && plugin.displayName ? plugin.displayName : 'Plugin details' }}</h2>
      <div class="pluginDetails">

        <div class="row">
          <div class="col s12 m9">
            <p class="description">
              <img v-if="plugin.featured" class="featuredIcon"
                   :title="translate('Marketplace_FeaturedPlugin')"
                   src="plugins/Marketplace/images/rating_important.png"
                   alt=""
              />

              {{ plugin.description }}
            </p>
            <div class="contentDetails">
              <div id="pluginDetailsTabs" class="row">
                <div class="col s12">
                  <ul class="tabs">
                    <li class="tab col s3"><a href="#tabs-description">{{ translate('General_Description') }}</a></li>

                    <li v-if="pluginFaq" class="tab col s3"><a href="#tabs-faq">{{  translate('General_Faq') }}</a></li>

                    <li v-if="pluginDocumentation" class="tab col s3"><a href="#tabs-documentation">{{ translate('General_Documentation') }}</a></li>

                    <li v-if="pluginScreenshots.length" class="tab col s3"><a href="#tabs-screenshots">{{ translate('Marketplace_Screenshots') }}</a></li>

                    <li v-if="showReviews" class="tab col s3"><a href="#tabs-reviews">{{ translate('Marketplace_Reviews') }}</a></li>
                  </ul>
                </div>

                <div id="tabs-description" class="tab-content col s12">
                  <MissingReqsNotice v-if="showMissingRequirementsNoticeIfApplicable" :plugin="plugin" />

                  <div v-if="isMultiServerEnvironment" class="alert alert-warning">{{ translate('Marketplace_MultiServerEnvironmentWarning') }}</div>
                  <div v-else-if="!isAutoUpdatePossible" class="alert alert-warning">{{ translate('Marketplace_AutoUpdateDisabledWarning', '\'[General]enable_auto_update=1\'', '\'config/config.ini.php\'') }}</div>

                  <div v-if="showMissingLicenseDescription" class="alert alert-danger">{{ translate('Marketplace_PluginLicenseMissingDescription') }}</div>
                  <div v-else-if="showExceededLicenceDescription" class="alert alert-warning">{{ translate('Marketplace_PluginLicenseExceededDescription') }}</div>

                  <div v-html="$sanitize(pluginDescription)"></div>
                </div>

                <div v-if="pluginFaq" id="tabs-faq" class="tab-content col s12" v-html="$sanitize(pluginFaq)"></div>

                <div v-if="pluginDocumentation" id="tabs-documentation" class="tab-content col s12" v-html="$sanitize(pluginDocumentation)"></div>

                <div v-if="pluginScreenshots.length" id="tabs-screenshots" class="tab-content col s12">
                  <div class="thumbnails">
                    <div class="thumbnail" v-for="screenshot in pluginScreenshots" :key="`screenshot-${screenshot}`">
                      <a :href="screenshot" target="_blank"><img :src="`${screenshot}?w=400`" width="400" alt=""></a>
                      <p>
                        {{ this.getScreenshotBaseName(screenshot) }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-if="showReviews" id="tabs-reviews" class="tab-content col s12">
                  <iframe class="reviewIframe"
                          :style=" pluginReviews.height ? `height: ${pluginReviews.height}px;` : '' "
                          :id="pluginReviews.embedUrl.replace(/[\W_]+/g, ' ')"
                          :src="pluginReviews.embedUrl"></iframe>
                </div>
              </div>
            </div>
          </div>
          <div class="col s12 m3">
            <div class="cta-container row">
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
                :show-actions-only="true"
                @startFreeTrial="this.$emit('startFreeTrial', plugin.name)"
              />
            </div>

            <div class="actionButton row" v-if="showPluginVariations">
              <h4 class="actionButtonHeading">{{ translate('Marketplace_TryFreeTrialTitle') }}</h4>
              <div class="input-field variationPicker">
                  <select :title="`${encodeURIComponent(translate('Marketplace_ShownPriceIsExclTax'))} ${encodeURIComponent(translate('Marketplace_CurrentNumPiwikUsers', numUsers))}`">
                      <option
                        v-for="(variation, index) in pluginShopVariations"
                        :key="`variation-${index}`"
                        :value="variation.addToCartUrl"
                        :title="`${numUsers} ${encodeURIComponent(translate('Marketplace_PriceExclTax', variation.price, variation.currency))} ${ encodeURIComponent(translate('Marketplace_CurrentNumPiwikUsers', numUsers)) }`"
                        :selected="!!variation.recommended"
                      >{{ variation.name }} - {{ variation.prettyPrice }} / {{ variation.period }}</option>
                  </select>
              </div>

              <a class="btn btn-block addToCartLink" target="_blank"
                 title="{{ encodeURIComponent(translate('Marketplace_ClickToCompletePurchase')) }}"
                 rel="noreferrer noopener"
                 :href="encodeURIComponent(pluginShop.url ?? '')"
              >{{ translate('Marketplace_AddToCart') }}</a>
            </div>

            <div class="metadata row">
              <p v-if="plugin.specialOffer" style="color: green;" v-html="$sanitize(plugin.specialOffer)"></p>

              <dl>
                <template v-if="!plugin.isBundle">
                <dt>{{ translate('CorePluginsAdmin_Version') }}</dt>
                <dd>{{ plugin.latestVersion }}</dd>
                </template>

                <template v-if="pluginKeywords">
                <dt>{{ translate('Marketplace_PluginKeywords') }}</dt>
                <dd>{{ pluginKeywords.join(', ') }}</dd>
                </template>

                <template v-if="plugin.lastUpdated && !plugin.isBundle">
                <dt>{{ translate('Marketplace_LastUpdated') }}</dt>
                <dd>{{ plugin.lastUpdated }}</dd>
                </template>

                <template v-if="plugin.numDownloads">
                <dt>{{ translate('General_Downloads') }}</dt>
                <dd title="{{ translate('Marketplace_NumDownloadsLatestVersion', pluginLatestVersion.numDownloads) }}">{{ plugin.numDownloads }}</dd>
                </template>

                <template v-if="!plugin.isBundle">
                <dt>{{ translate('Marketplace_Developer') }}</dt>
                <dd>
                  <img v-if="isMatomoPlugin" title="Matomo" alt="Matomo" style="padding-bottom:2px;height:12px;" src="plugins/Morpheus/images/logo-dark.svg" />
                  <span v-else>{{ plugin.owner }}</span>
                </dd>

                <template v-if="showLicenceName">
                <dt>{{ translate('Marketplace_License') }}</dt>
                <dd>
                  <a v-if="pluginLatestVersion.license?.url" rel="noreferrer noopener"
                     :href="pluginLatestVersion.license?.url"
                     target="_blank">{{ pluginLatestVersion.license?.name }}</a>
                  <span v-else>{{ pluginLatestVersion.license?.name }}</span>
                </dd>
                </template>

                <dt>{{ translate('Marketplace_Authors') }}</dt>
                <dd>
                  <template v-for="(author, index) in pluginAuthors" :key="`author-${index}`">
                    <a v-if="author.homepage" target="_blank" rel="noreferrer noopener" :href="author.homepage">{{ author.name }}</a>
                    <a v-else-if="author.email" :href="`mailto:${ encodeURIComponent(author.email) }`">{{ author.name }}</a>
                    <span v-else>{{ author.name }}</span>
                    <span v-if="index < pluginAuthors.length - 1">, </span>
                  </template>
                </dd>
                </template>

                <dt>{{ translate('CorePluginsAdmin_Websites') }}</dt>
                <dd>
                  <a v-if="plugin.homepage" target="_blank" rel="noreferrer noopener" :href="plugin.homepage">{{ translate('Marketplace_PluginWebsite') }}</a>
                  <template v-if="pluginChangelogUrl">
                    <template v-if="plugin.homepage">, </template>
                    <a target="_blank" rel="noreferrer noopener" :href="externalRawLink(pluginChangelogUrl)">{{ translate('CorePluginsAdmin_Changelog') }}</a>
                  </template>

                  <template v-if="plugin.repositoryUrl">
                    <template v-if="plugin.homepage || pluginChangelogUrl">, </template>
                    <a target="_blank" rel="noreferrer noopener" :href="externalRawLink(plugin.repositoryUrl)">GitHub</a>
                  </template>
                </dd>

                <template v-if="pluginActivity && pluginActivity.numCommits">
                <dt>{{ translate('CorePluginsAdmin_Activity') }}</dt>
                <dd>
                  {{ plugin.activity.numCommits }} commits

                  <template v-if="pluginActivity?.numContributors > 1">{{ ' ' + translate('Marketplace_ByXDevelopers', pluginActivity.numContributors) }}</template>
                  <template v-if="pluginActivity?.lastCommitDate">{{ ' ' + translate('Marketplace_LastCommitTime', pluginActivity.lastCommitDate) }}</template>
                </dd>
                </template>

                <template v-if="pluginSupport.length">
                  <template v-for="(support, index) in pluginSupport" :key="`support-${index}`">
                  <dt v-if="support.name && support.value" v-html="$sanitize(support.name)"></dt>
                  <dd v-if="support.name && support.value" v-html="$sanitize(support.value)"></dd>
                  </template>
                </template>

              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import CTAContainer from '../PluginList/CTAContainer.vue';
import MissingReqsNotice from '../MissingReqsNotice/MissingReqsNotice.vue';

const { $ } = window;

interface PluginDetailsState {
  isLoading: boolean;
  pluginDetails: string;
  fetchRequest: Promise<void>|null;
  fetchRequestAbortController: AbortController|null;
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
  data(): PluginDetailsState {
    return {
      isLoading: true,
      pluginDetails: '',
      fetchRequest: null,
      fetchRequestAbortController: null,
    };
  },
  emits: ['update:modelValue', 'startFreeTrial'],
  watch: {
    modelValue(newValue) {
      if (newValue) {
        this.showPluginDetailsDialog();
      }
    },
    isLoading(newValue) {
      if (newValue === false) {
        this.applyExternalTarget();
        this.applyTabs();
        this.applySelect();
        this.applyIframeResize();
      }
    },
  },
  computed: {
    plugin() {
      return this.modelValue;
    },
    pluginLatestVersion() {
      const versions: [] = this.plugin.versions || [];
      return versions.pop();
    },
    pluginReadmeHtml() {
      return this.pluginLatestVersion ? this.pluginLatestVersion.readmeHtml : {};
    },
    pluginDescription() {
      return this.pluginReadmeHtml ? this.pluginReadmeHtml.description : '';
    },
    pluginDocumentation() {
      return this.pluginReadmeHtml ? this.pluginReadmeHtml.documentation : '';
    },
    pluginFaq() {
      return this.pluginReadmeHtml ? this.pluginReadmeHtml.faq : '';
    },
    pluginShop() {
      return this.plugin.shop || {};
    },
    pluginShopVariations() {
      return this.pluginShop?.variations || [];
    },
    pluginReviews() {
      return this.pluginShop.reviews || {};
    },
    pluginKeywords() {
      return this.plugin.keywords || [];
    },
    pluginAuthors() {
      const authors = this.plugin.authors || [];
      return authors.filter((author) => author.name);
    },
    pluginActivity() {
      return this.plugin.activity || {};
    },
    pluginChangelogUrl() {
      return this.plugin.changelog.url || '';
    },
    pluginSupport() {
      return this.plugin.support || [];
    },
    isMatomoPlugin() {
      return ['piwik', 'matomo-org'].includes(this.plugin.owner);
    },
    showReviews() {
      return this.pluginReviews && this.pluginReviews.embedUrl;
    },
    showMissingLicenseDescription() {
      return this.hasSomeAdminAccess && typeof this.plugin.isMissingLicense !== 'undefined' && this.plugin.isMissingLicense;
    },
    showExceededLicenceDescription() {
      return this.hasSomeAdminAccess && typeof this.plugin.hasExceededLicense !== 'undefined' && this.plugin.hasExceededLicense;
    },
    showMissingRequirementsNoticeIfApplicable() {
      return this.isSuperUser && (this.plugin.isDownloadable || this.plugin.isInstalled);
    },
    showLicenceName() {
      return this.pluginLatestVersion
        && this.pluginLatestVersion.license
        && this.pluginLatestVersion.license.name;
    },
    showPluginVariations() {
      return (!this.plugin.isDownloadable || !this.isSuperUser)
        && !this.plugin.isEligibleForFreeTrial
        && this.plugin.isPaid
        && this.pluginShop
        && this.pluginShopVariations.length;
    },
    pluginScreenshots() {
      return this.plugin.screenshots || [];
    },
  },
  methods: {
    applyTabs() {
      const root = this.$refs.root as HTMLElement;

      setTimeout(() => {
        $('#pluginDetailsTabs .tabs', root).tabs();
      });
    },
    applySelect() {
      setTimeout(() => {
        const $variationPicker = $('.pluginDetails .variationPicker select');
        if ($variationPicker.val()) {
          $('.addToCartLink').attr('href', $variationPicker.val());
        }
        $variationPicker.on('change', () => {
          $('.addToCartLink').attr('href', $variationPicker.val());
        });

        if ($variationPicker.length) {
          $variationPicker.material_select();
        }
      });
    },
    applyExternalTarget() {
      setTimeout(() => {
        const root = this.$refs.root as HTMLElement;
        $('.pluginDetails a', root).each((index, a) => {
          const link = $(a).attr('href');

          if (link && link.indexOf('http') === 0) {
            $(a).attr('target', '_blank');
          }
        });
      });
    },
    getProtocolAndDomain(url: string) {
      const urlObj = new URL(url);
      return `${urlObj.protocol}//${urlObj.hostname}`;
    },
    applyIframeResize() {
      setTimeout(() => {
        const iFrameResize = window.iFrameResize || null;
        if (this.pluginReviews && iFrameResize) {
          $(() => {
            const $iFrames = $('.pluginDetails iframe.reviewIframe');
            for (let i = 0; i < $iFrames.length; i += 1) {
              // eslint-disable-next-line max-len
              iFrameResize({ checkOrigin: [this.getProtocolAndDomain(this.pluginReviews.embedUrl)] }, $iFrames[i]);
            }
          });
        }
      });
    },
    getScreenshotBaseName(screenshot: string) {
      const filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    showPluginDetailsDialog() {
      $('#pluginDetails').modal({
        dismissible: true,
        onCloseEnd: () => {
          this.$emit('update:modelValue', null);
          this.isLoading = true;
        },
      }).modal('open');

      setTimeout(() => {
        this.isLoading = false;
      }, 250);
    },
  },
});
</script>
