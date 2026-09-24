<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="marketplacePluginDetails">
    <button
      class="marketplacePluginDetails__back"
      type="button"
      @click="$emit('back')"
    >
      <span class="icon-chevron-left" aria-hidden="true" />
      <span>{{ translate('Mobile_NavigationBack') }}</span>
    </button>

    <PluginDetailsSkeleton v-if="isLoading" />

    <div v-else class="marketplacePluginDetails__content">
      <section class="marketplacePluginDetails__head">
        <div class="marketplacePluginDetails__cover" v-if="plugin.coverImage">
          <img
            class="marketplacePluginDetails__coverImage"
            :class="{
              'marketplacePluginDetails__coverImage--placeholder': isPlaceholderCover,
            }"
            :src="`${plugin.coverImage}?w=468&h=238`"
            alt=""
            decoding="async"
          >
        </div>

        <div class="marketplacePluginDetails__info">
          <h1 class="marketplacePluginDetails__title" ref="heading" tabindex="-1">
            {{ plugin.displayName || plugin.name }}
          </h1>

          <p class="marketplacePluginDetails__lede" v-if="plugin.description">
            {{ plugin.description }}
          </p>

          <div class="marketplacePluginDetails__facts">
            <span class="marketplacePluginDetails__labels">
              <span
                class="marketplacePluginDetails__pill marketplacePluginDetails__pill--matomo"
                v-if="isMatomoPlugin"
              >
                <MatomoGlyph />
                {{ translate('Marketplace_CategoryMatomo') }}
              </span>
              <span class="marketplacePluginDetails__pill" v-if="categoryLabel">
                {{ categoryLabel }}
              </span>
            </span>

            <template v-if="showReviews">
              <span class="marketplacePluginDetails__rating">
                <span class="marketplacePluginDetails__ratingStar" aria-hidden="true">★</span>
                {{ pluginReviews.averageRating }}
              </span>
              <button
                class="marketplacePluginDetails__reviewsLink"
                type="button"
                v-if="reviewCountLabel"
                @click="scrollElementIntoView('.marketplacePluginDetails__reviews')"
              >{{ reviewCountLabel }}</button>
            </template>

            <span
              class="marketplacePluginDetails__fact"
              v-if="(plugin.numDownloads || 0) > 0"
            >{{ translate('Marketplace_NumDownloads', String(plugin.numDownloadsPretty)) }}</span>

            <span
              class="marketplacePluginDetails__fact"
              v-if="plugin.lastUpdated && !plugin.isBundle"
            >{{ translate('Marketplace_UpdatedOn', plugin.lastUpdated) }}</span>
          </div>
        </div>
      </section>

      <div class="marketplacePluginDetails__columns">
        <main class="marketplacePluginDetails__main">
          <div class="marketplacePluginDetails__alerts">
            <MissingReqsNotice v-if="showMissingRequirementsNoticeIfApplicable" :plugin="plugin" />

            <div
              v-if="showDeploymentWarnings && isMultiServerEnvironment"
              class="alert alert-warning"
            >
              {{ translate('Marketplace_MultiServerEnvironmentWarning') }}
            </div>
            <div
              v-else-if="showDeploymentWarnings && !isAutoUpdatePossible"
              class="alert alert-warning"
            >
              {{
                translate(
                  'Marketplace_AutoUpdateDisabledWarning',
                  '\'[General]enable_auto_update=1\'',
                  '\'config/config.ini.php\'',
                )
              }}
            </div>

            <div v-if="showMissingLicenseDescription" class="alert alert-danger">
              {{ translate('Marketplace_PluginLicenseMissingDescription') }}
            </div>
            <div v-else-if="showExceededLicenseDescription" class="alert alert-warning">
              {{ translate('Marketplace_PluginLicenseExceededDescription') }}
            </div>
            <div
              v-else-if="plugin.licenseStatus === 'Pending' && !isMultiServerEnvironment"
              class="alert alert-warning"
              v-html="$sanitize(getPendingLicenseHelpText(plugin.displayName))"
            />
            <div
              v-else-if="plugin.licenseStatus === 'Cancelled' && !isMultiServerEnvironment"
              class="alert alert-warning"
              v-html="$sanitize(getCancelledLicenseHelpText(plugin.displayName))"
            />
            <div
              v-else-if="
                !plugin.hasDownloadLink
                  && !isMultiServerEnvironment
                  && (plugin.licenseStatus || !plugin.isPaid)"
              class="alert alert-warning"
              v-html="$sanitize(getDownloadLinkMissingHelpText(plugin.displayName))"
            />

            <div v-if="fetchErrorMessage" class="alert alert-danger">
              {{ fetchErrorMessage }}
            </div>
          </div>

          <div class="marketplacePluginDetails__cards">
            <section class="marketplacePluginDetails__card" v-if="pluginDescription">
              <h2 class="marketplacePluginDetails__cardTitle">
                {{ translate('Marketplace_AboutThisPlugin') }}
              </h2>
              <div class="marketplacePluginDetails__readme" v-html="$sanitize(pluginDescription)" />
            </section>

            <section class="marketplacePluginDetails__card" v-if="pluginScreenshots.length">
              <h2 class="marketplacePluginDetails__cardTitle">
                {{ translate('Marketplace_Screenshots') }}
              </h2>
              <div class="marketplacePluginDetails__shots">
                <figure
                  class="marketplacePluginDetails__shot"
                  v-for="screenshot in pluginScreenshots"
                  :key="`screenshot-${screenshot}`"
                >
                  <button
                    class="marketplacePluginDetails__shotButton"
                    type="button"
                    :title="getScreenshotBaseName(screenshot)"
                    @click="openLightbox(screenshot)"
                  >
                    <img
                      class="marketplacePluginDetails__shotImage"
                      :src="`${screenshot}?w=480`"
                      alt=""
                      loading="lazy"
                      decoding="async"
                    >
                  </button>
                  <figcaption class="marketplacePluginDetails__shotCaption">
                    {{ getScreenshotBaseName(screenshot) }}
                  </figcaption>
                </figure>
              </div>
            </section>

            <section class="marketplacePluginDetails__card" v-if="pluginDocumentation">
              <h2 class="marketplacePluginDetails__cardTitle">
                {{ translate('General_Documentation') }}
              </h2>
              <div
                class="marketplacePluginDetails__readme"
                v-html="$sanitize(pluginDocumentation)"
              />
            </section>

            <section class="marketplacePluginDetails__card" v-if="pluginFaq">
              <h2 class="marketplacePluginDetails__cardTitle">{{ translate('General_Faq') }}</h2>
              <div class="marketplacePluginDetails__readme" v-html="$sanitize(pluginFaq)" />
            </section>

            <section
              class="marketplacePluginDetails__card marketplacePluginDetails__reviews"
              v-if="showReviews"
            >
              <h2 class="marketplacePluginDetails__cardTitle">
                {{ translate('Marketplace_Reviews') }}
              </h2>
              <div class="marketplacePluginDetails__reviewSummary">
                <strong class="marketplacePluginDetails__reviewScore">
                  {{ pluginReviews.averageRating }}
                </strong>
                <span class="marketplacePluginDetails__ratingStar" aria-hidden="true">★</span>
                <span class="marketplacePluginDetails__reviewCount" v-if="reviewCountLabel">
                  {{ reviewCountLabel }}
                </span>
              </div>
              <iframe
                class="marketplacePluginDetails__reviewFrame"
                :title="translate('Marketplace_Reviews')"
                :style="pluginReviews.height ? `height: ${pluginReviews.height}px;` : '' "
                :src="pluginReviews.embedUrl" />
            </section>
          </div>
        </main>

        <aside class="marketplacePluginDetails__aside">
          <section
            class="marketplacePluginDetails__card marketplacePluginDetails__buy"
            :class="{ 'marketplacePluginDetails__buy--highlighted': showPricingCard }"
          >
            <ShopPricing
              v-if="showShopPricing"
              :plugin="plugin"
              :num-users="numUsers"
              :offers-free-trial="plugin.isEligibleForFreeTrial || plugin.isNewBundle"
              :use-period-tabs="plugin.isNewBundle"
              :stacked="true"
              :prominent="showPricingCard"
            />

            <template v-else>
              <div class="marketplacePluginDetails__freeSummary" v-if="showFreeLabel">
                <div class="marketplacePluginDetails__free">
                  {{ translate('Marketplace_Free') }}
                </div>
                <div class="marketplacePluginDetails__freeLicense" v-if="freeLicenseLabel">
                  {{ freeLicenseLabel }}
                </div>
              </div>

              <div class="marketplacePluginDetails__cta">
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
                  @requestTrial="$emit('requestTrial', plugin)"
                />
              </div>
            </template>
          </section>

          <section class="marketplacePluginDetails__card">
            <h2 class="marketplacePluginDetails__cardTitle">{{ translate('General_Details') }}</h2>
            <dl class="marketplacePluginDetails__meta">
              <div class="marketplacePluginDetails__metaRow" v-if="!plugin.isBundle">
                <dt class="marketplacePluginDetails__metaLabel">
                  {{ translate('CorePluginsAdmin_Version') }}
                </dt>
                <dd class="marketplacePluginDetails__metaValue">{{ plugin.latestVersion }}</dd>
              </div>

              <div
                class="marketplacePluginDetails__metaRow"
                v-if="plugin.lastUpdated && !plugin.isBundle"
              >
                <dt class="marketplacePluginDetails__metaLabel">
                  {{ translate('Marketplace_LastUpdated') }}
                </dt>
                <dd class="marketplacePluginDetails__metaValue">{{ plugin.lastUpdated }}</dd>
              </div>

              <template v-if="!plugin.isBundle">
                <!--
                  The authors, linked, under the one heading: the owner is a repository account
                  ("matomo-org", "openmost") and the author is who it is. The owner only stands in
                  where no author is named.
                -->
                <div class="marketplacePluginDetails__metaRow">
                  <dt class="marketplacePluginDetails__metaLabel">
                    {{ translate('Marketplace_Developer') }}
                  </dt>
                  <dd class="marketplacePluginDetails__metaValue">
                    <template v-if="pluginAuthors.length">
                      <template v-for="(author, index) in pluginAuthors" :key="`author-${index}`">
                        <a
                          class="marketplacePluginDetails__metaLink"
                          v-if="author.homepage"
                          target="_blank"
                          rel="noreferrer noopener"
                          :href="author.homepage"
                        >{{ author.name }}</a>
                        <a
                          class="marketplacePluginDetails__metaLink"
                          v-else-if="author.email && isValidEmail(author.email)"
                          :href="`mailto:${ encodeURIComponent(author.email) }`"
                        >{{ author.name }}</a>
                        <span v-else>{{ author.name }}</span>
                        <span v-if="index < pluginAuthors.length - 1">, </span>
                      </template>
                    </template>
                    <template v-else>{{ pluginOwner }}</template>
                  </dd>
                </div>

                <div class="marketplacePluginDetails__metaRow" v-if="requiredMatomoVersion">
                  <dt class="marketplacePluginDetails__metaLabel">
                    {{ translate('Marketplace_Requires') }}
                  </dt>
                  <dd class="marketplacePluginDetails__metaValue" :title="requiredMatomoConstraint">
                    {{ translate('Marketplace_RequiresMatomoVersion', requiredMatomoVersion) }}
                  </dd>
                </div>

                <div
                  class="marketplacePluginDetails__metaRow"
                  v-if="plugin.homepage || pluginChangelogUrl || plugin.repositoryUrl"
                >
                  <dt class="marketplacePluginDetails__metaLabel">
                    {{ translate('CorePluginsAdmin_Websites') }}
                  </dt>
                  <dd class="marketplacePluginDetails__metaValue">
                    <a
                      class="marketplacePluginDetails__metaLink"
                      v-if="plugin.homepage"
                      target="_blank"
                      rel="noreferrer noopener"
                      :href="plugin.homepage"
                    >{{ translate('Marketplace_PluginWebsite') }}</a>
                    <template v-if="pluginChangelogUrl">
                      <template v-if="plugin.homepage"> · </template>
                      <a
                        class="marketplacePluginDetails__metaLink"
                        target="_blank"
                        rel="noreferrer noopener"
                        :href="externalRawLink(pluginChangelogUrl)"
                      >{{ translate('CorePluginsAdmin_Changelog') }}</a>
                    </template>

                    <template v-if="plugin.repositoryUrl">
                      <template v-if="plugin.homepage || pluginChangelogUrl"> · </template>
                      <a
                        class="marketplacePluginDetails__metaLink"
                        target="_blank"
                        rel="noreferrer noopener"
                        :href="externalRawLink(plugin.repositoryUrl)"
                      >{{ translate('General_Source') }}</a>
                    </template>
                  </dd>
                </div>

                <div class="marketplacePluginDetails__metaRow" v-if="showLicenseName">
                  <dt class="marketplacePluginDetails__metaLabel">
                    {{ translate('Marketplace_License') }}
                  </dt>
                  <dd class="marketplacePluginDetails__metaValue">
                    <a
                      class="marketplacePluginDetails__metaLink"
                      v-if="pluginLatestVersion.license?.url"
                      rel="noreferrer noopener"
                      :href="pluginLatestVersion.license?.url"
                      target="_blank">{{ pluginLatestVersion.license?.name }}</a>
                    <span v-else>{{ pluginLatestVersion.license?.name }}</span>
                  </dd>
                </div>
              </template>

              <div class="marketplacePluginDetails__metaRow" v-if="pluginKeywords.length">
                <dt class="marketplacePluginDetails__metaLabel">
                  {{ translate('Marketplace_PluginKeywords') }}
                </dt>
                <dd class="marketplacePluginDetails__metaValue">
                  <div class="marketplacePluginDetails__keywordList">
                    <span
                      class="marketplacePluginDetails__keywordItem"
                      v-for="keyword in pluginKeywords"
                      :key="`keyword-${keyword}`"
                    >{{ keyword }}</span>
                  </div>
                </dd>
              </div>
            </dl>
          </section>
        </aside>
      </div>
    </div>

    <MatomoModal
      v-model="lightboxOpen"
      classes="marketplacePluginDetails__lightbox"
      :aria-label="lightboxCaption"
    >
      <img
        class="marketplacePluginDetails__lightboxImage"
        v-if="lightboxScreenshot"
        :src="lightboxScreenshot"
        :alt="lightboxCaption"
      >
    </MatomoModal>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  AjaxHelper,
  MatomoModal,
  translate,
  externalLink,
} from 'CoreHome';
import {
  IPluginShopDetails,
  IPluginShopReviews,
  IPluginShopVariation,
  PluginCard,
  PluginDetails,
  TObject,
  TObjectArray,
} from '../types';
import CTAContainer from '../PluginList/CTAContainer.vue';
import MatomoGlyph from '../PluginCard/MatomoGlyph.vue';
import PluginDetailsSkeleton from './PluginDetailsSkeleton.vue';
import { pluginCategories } from '../PluginGrid/pluginGrouping';
import { categoryLabel as labelForCategory } from '../PluginGrid/categoryLabels';
import ShopPricing from './ShopPricing.vue';
import { hasShopPricing } from './shopPricing';
import MissingReqsNotice from '../MissingReqsNotice/MissingReqsNotice.vue';

/**
 * The stand-in `Plugins::addPluginCoverImage()` falls back to for a plugin with no screenshot.
 * Line art on a white ground, so a dark theme has to invert it the way it does every other Matomo
 * illustration - a real screenshot must not be touched. Kept in step with PluginCard.vue.
 */
const PLACEHOLDER_COVER = 'plugins/Marketplace/images/categories/uncategorised.png';

/**
 * Licence names that are open source. The name is free text from the plugin's author, in more
 * than one spelling ("GPL v3+", "GPLv3+"), and a free plugin can still ship under a commercial
 * licence - so only these are called open source.
 */
const OPEN_SOURCE_LICENSE = /\b(?:[AL]?GPL|MIT|Apache|BSD|MPL)/i;

export interface PluginVersion {
  readmeHtml?: { description?: string; documentation?: string; faq?: string };
  license?: { url?: string; name?: string };
  requires?: Record<string, string>;
}

export interface PluginAuthor {
  name?: string;
  email?: string;
  homepage?: string;
}

export interface PluginDetailsState {
  isLoading: boolean;
  fetchedDetails: PluginDetails|null;
  fetchAbortController: AbortController|null;
  fetchErrorMessage: string;
  lightboxScreenshot: string;
}

export default defineComponent({
  components: {
    CTAContainer,
    MatomoGlyph,
    MatomoModal,
    MissingReqsNotice,
    PluginDetailsSkeleton,
    ShopPricing,
  },
  props: {
    /**
     * The card row for the plugin being shown. The details request fills in everything the
     * catalogue listing leaves out; see the `plugin` computed.
     */
    pluginCard: {
      type: Object as PropType<PluginCard>,
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
      fetchedDetails: null,
      fetchAbortController: null,
      fetchErrorMessage: '',
      lightboxScreenshot: '',
    };
  },
  emits: [
    'back',
    'requestTrial',
  ],
  watch: {
    // the page is mounted per plugin, but the hash can name another one while it is open - a
    // second click from the plugin management table does exactly that
    'pluginCard.name': function onPluginChange() {
      this.fetchPluginDetails();
    },
    isLoading(newValue) {
      if (newValue === false) {
        this.focusHeading();
        this.applyExternalTarget();
        this.applyIframeResize();
      }
    },
  },
  mounted() {
    // Where the window ends up is the Marketplace page's business - it owns the scroll position on
    // both sides of the change between the two views. Focus follows once there is a heading to put
    // it on; see focusHeading(), since until the details arrive this is a skeleton.
    this.fetchPluginDetails();
  },
  unmounted() {
    this.abortDetailsFetch();
    this.teardownIframeResize();
  },
  computed: {
    plugin(): PluginDetails {
      // the plugin list only carries the fields its cards render, so everything else arrives from
      // getPluginDetails once the modal opens
      return {
        ...(this.pluginCard as PluginCard),
        ...(this.fetchedDetails || {}),
      } as PluginDetails;
    },
    pluginLatestVersion(): PluginVersion {
      const versions: TObjectArray = this.plugin.versions || [{}];
      return versions[versions.length - 1] as PluginVersion;
    },
    pluginReadmeHtml(): { description?: string; documentation?: string; faq?: string } {
      return this.pluginLatestVersion?.readmeHtml || {};
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
    pluginReviews(): IPluginShopReviews {
      return (this.pluginShop?.reviews || {}) as IPluginShopReviews;
    },
    pluginKeywords(): string[] {
      return this.plugin?.keywords || [];
    },
    pluginAuthors(): PluginAuthor[] {
      const authors = (this.plugin.authors || []) as PluginAuthor[];
      return authors.filter((author) => author.name);
    },
    pluginChangelogUrl(): string {
      return (this.plugin.changelog?.url as string) || '';
    },
    isMatomoPlugin(): boolean {
      return ['piwik', 'matomo-org'].includes(this.plugin.owner);
    },
    pluginOwner(): string {
      return this.isMatomoPlugin ? 'Matomo' : this.plugin.owner;
    },
    /**
     * The Matomo constraint the latest version declares, as written: `>=6.0.0-b1,<7.0.0-b1`.
     * Older plugins still name it `piwik`.
     */
    requiredMatomoConstraint(): string {
      const requires = this.pluginLatestVersion?.requires || {};
      return requires.matomo || requires.piwik || '';
    },
    /**
     * The lowest Matomo the latest version runs on, cut to major.minor - "6.0" rather than
     * "6.0.0-b1", since the beta suffix only marks where the range opens. The upper bound is
     * left to the tooltip: it is always the next major, and "or newer" is what readers check.
     */
    requiredMatomoVersion(): string {
      const lowerBound = /(?:^|,)\s*>=\s*(\d+\.\d+)/.exec(this.requiredMatomoConstraint);
      return lowerBound ? lowerBound[1] : '';
    },
    showReviews(): boolean {
      return !!(this.pluginReviews
        && this.pluginReviews.embedUrl
        && this.pluginReviews.averageRating
      );
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
    showDeploymentWarnings(): boolean {
      // both warnings tell you that you will have to download the plugin and deploy it yourself.
      // A bundle is a licence purchase with no download of its own — the plugins it covers are
      // installed individually afterwards — so neither warning is actionable for one.
      return !this.plugin.isBundle;
    },
    showShopPricing(): boolean {
      return (
        this.isSuperUser
        && !this.plugin.isMissingLicense
        && !this.plugin.isInstalled
        && !this.plugin.hasExceededLicense
        && (this.plugin.isEligibleForFreeTrial || this.plugin.isNewBundle)
        // the variations come from the details request, so there are none to pick from when it
        // failed and the modal is left with the card row alone
        && hasShopPricing(this.plugin)
      ) as boolean;
    },
    /**
     * A plugin's price is shown as the pricing card on plugins.matomo.org. A bundle keeps the
     * panel it had, whose billing period toggle a plugin never shows.
     */
    showPricingCard(): boolean {
      return this.showShopPricing && !this.plugin.isNewBundle;
    },
    /** The one category chip the head shows, chosen the way a card chooses its own. */
    categoryLabel(): string {
      if (this.plugin.isBundle) {
        return translate('Marketplace_Bundles');
      }

      return labelForCategory(pluginCategories(this.plugin as PluginCard)[0] ?? '');
    },
    isPlaceholderCover(): boolean {
      return (this.plugin.coverImage || '').endsWith(PLACEHOLDER_COVER);
    },
    /**
     * How many reviews the score is an average of, or nothing when the shop did not say.
     *
     * `reviewCount` is the written reviews the embed lists, which is what the reader is being
     * pointed at; `ratingCount` also counts scores left without one. Neither is guaranteed to be
     * in the response, so the label is dropped rather than shown as a zero.
     */
    reviewCountLabel(): string {
      const count = Number(this.pluginReviews?.reviewCount ?? 0);

      if (!Number.isFinite(count) || count < 1) {
        return '';
      }

      return count === 1
        ? translate('Marketplace_OneReview')
        : translate('Marketplace_NumReviews', String(count));
    },
    /**
     * Whether the purchase panel says "Free" above its button. Only for a plugin that costs
     * nothing to begin with - one already paid for shows its status and its action alone.
     */
    showFreeLabel(): boolean {
      return !this.plugin.isPaid && !this.plugin.isInstalled && !this.plugin.isBundle;
    },
    /** The licence under "Free", called open source only when it is one. */
    freeLicenseLabel(): string {
      const name = this.pluginLatestVersion?.license?.name || '';

      if (!name || !OPEN_SOURCE_LICENSE.test(name)) {
        return name;
      }

      return translate('Marketplace_OpenSourceLicense', name);
    },
    lightboxOpen: {
      get(): boolean {
        return !!this.lightboxScreenshot;
      },
      set(open: boolean) {
        if (!open) {
          this.lightboxScreenshot = '';
        }
      },
    },
    lightboxCaption(): string {
      return this.lightboxScreenshot
        ? this.getScreenshotBaseName(this.lightboxScreenshot)
        : '';
    },
    pluginScreenshots(): string[] {
      return this.plugin.screenshots || [];
    },
    pluginShopRecommendedVariation(): IPluginShopVariation | null {
      const recommendedVariations = this.pluginShopVariations.filter((v) => v.recommended);
      const defaultVariation = this.pluginShopVariations.length
        ? this.pluginShopVariations[0]
        : null;
      return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
    },
    selectedShopVariationUrl(): string {
      return this.pluginShopRecommendedVariation?.addToCartUrl || '';
    },
  },
  methods: {
    /**
     * Moves focus onto the plugin's name, off the card in the catalogue behind that no longer has
     * anything to do with what is on screen. Without `preventScroll` the browser would scroll the
     * heading into view itself and undo the jump to the top of the page.
     */
    focusHeading() {
      this.$nextTick(() => {
        (this.$refs.heading as HTMLElement|undefined)?.focus({ preventScroll: true });
      });
    },
    /** Opens the readme's own links in a new tab, as every other link off this page does. */
    applyExternalTarget() {
      this.$nextTick(() => {
        const root = this.$refs.root as HTMLElement|undefined;
        const links = root?.querySelectorAll<HTMLAnchorElement>(
          '.marketplacePluginDetails__readme a[href^="http"]',
        ) ?? [];

        links.forEach((link) => {
          link.setAttribute('target', '_blank');
          link.setAttribute('rel', 'noreferrer noopener');
        });
      });
    },
    scrollElementIntoView(selector: string) {
      this.$nextTick(() => {
        const root = this.$refs.root as HTMLElement|undefined;
        const element = root?.querySelector(selector);

        if (element && typeof element.scrollIntoView === 'function') {
          element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
      });
    },
    isValidEmail(email: string) {
      // regex from https://stackoverflow.com/a/46181
      // eslint-disable-next-line max-len
      return email.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    getProtocolAndDomain(url: string) {
      const urlObj = new URL(url);
      return `${urlObj.protocol}//${urlObj.hostname}`;
    },
    reviewIframes(): HTMLIFrameElement[] {
      const root = this.$refs.root as HTMLElement|undefined;

      return [...(root?.querySelectorAll('.marketplacePluginDetails__reviewFrame') ?? [])] as HTMLIFrameElement[];
    },
    applyIframeResize() {
      this.$nextTick(() => {
        const { iFrameResize } = window;
        if (!this.pluginReviews?.embedUrl || !iFrameResize) {
          return;
        }

        const checkOrigin = [this.getProtocolAndDomain(this.pluginReviews.embedUrl as string)];
        this.reviewIframes().forEach((iframe) => iFrameResize({ checkOrigin }, iframe));
      });
    },
    /**
     * iframe-resizer attaches listeners to the window for each frame it is given. The page is
     * unmounted on every navigation back to the catalogue, so without this they accumulate.
     */
    teardownIframeResize() {
      this.reviewIframes().forEach((iframe) => {
        (iframe as unknown as { iFrameResizer?: { close: () => void } }).iFrameResizer?.close();
      });
    },
    openLightbox(screenshot: string) {
      this.lightboxScreenshot = screenshot;
    },
    getScreenshotBaseName(screenshot: string) {
      const filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    abortDetailsFetch() {
      if (this.fetchAbortController) {
        this.fetchAbortController.abort();
        this.fetchAbortController = null;
      }
    },
    fetchPluginDetails() {
      const pluginName = (this.pluginCard as PluginCard)?.name;

      if (!pluginName) {
        return;
      }

      this.abortDetailsFetch();
      this.isLoading = true;
      this.fetchErrorMessage = '';
      // details from the plugin opened before must not survive into this one, or a failed
      // request would show the new plugin's card data beside the old one's shop and versions
      this.fetchedDetails = null;

      const abortController = new AbortController();
      this.fetchAbortController = abortController;

      AjaxHelper.post(
        {
          module: 'Marketplace',
          action: 'getPluginDetails',
          format: 'JSON',
        },
        { pluginName },
        {
          withTokenInUrl: true,
          abortController,
          // the page renders the failure itself, in fetchErrorMessage
          createErrorNotification: false,
        },
      ).then((response) => {
        if (this.fetchAbortController !== abortController) {
          return; // superseded, so this belongs to a plugin the user is no longer looking at
        }

        this.fetchedDetails = response as PluginDetails;
      }).catch((response) => {
        if (this.fetchAbortController !== abortController) {
          return;
        }

        this.fetchErrorMessage = (response?.message as string)
          || translate('General_ErrorRequest', '', '');
      }).finally(() => {
        if (this.fetchAbortController !== abortController) {
          return; // superseded or aborted, whoever replaced it owns the loading state
        }

        this.fetchAbortController = null;
        this.isLoading = false;
      });
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
