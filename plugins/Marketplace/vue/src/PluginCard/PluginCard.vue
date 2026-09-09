<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <article
    class="pluginCard"
    :data-plugin="plugin.name"
  >
    <div class="pluginCard__plate">
      <div class="pluginCard__shot">
        <img
          v-if="!coverImageFailed"
          :src="coverImageUrl(440, 240)"
          :srcset="coverImageSrcset"
          sizes="(max-width: 767px) 100vw, (max-width: 1279px) 50vw, 320px"
          alt=""
          width="440"
          height="240"
          loading="lazy"
          decoding="async"
          @error="coverImageFailed = true"
        >
      </div>
    </div>

    <div class="pluginCard__chips">
      <span class="pluginCard__chip pluginCard__chip--matomo" v-if="isByMatomo">
        <span class="pluginCard__badge"><MatomoGlyph /></span>
        {{ translate('Marketplace_ByMatomo') }}
      </span>
      <span class="pluginCard__chip" v-if="categoryLabel">{{ categoryLabel }}</span>
    </div>

    <h3 class="pluginCard__title">
      <!-- PluginCard.less stretches this link over the whole card, so the card body is clickable
           without a second click handler and the keyboard path is this one anchor. The href is a
           real deep link, so middle-click and "copy link" work. -->
      <a
        class="pluginCard__titleLink"
        :href="detailsHref"
        :title="plugin.displayName"
        @click.prevent="$emit('openDetails', plugin)"
      >{{ plugin.displayName }}</a>
    </h3>

    <p class="pluginCard__description">{{ plugin.description }}</p>

    <!-- A bundle spends this row on its seat tier instead: all three bundles were published on
         the same day by the same owner, so the update date and author say nothing, while the tier
         is what a visitor compares one bundle against another on. Same class, so a row of mixed
         cards still lines up. -->
    <div class="pluginCard__meta" v-if="bundleSeatsLabel">
      <span class="pluginCard__seats">
        <span class="pluginCard__seatsIcon icon-ok" aria-hidden="true" />
        {{ bundleSeatsLabel }}
      </span>
    </div>

    <div class="pluginCard__meta" v-else>
      <span class="pluginCard__updated" v-if="plugin.lastUpdated">
        <span class="pluginCard__updatedIcon icon-clock" aria-hidden="true" />
        {{ translate('Marketplace_UpdatedOn', plugin.lastUpdated) }}
      </span>
      <!-- a Matomo plugin is already credited by the chip above; repeating it here is noise -->
      <span
        v-if="!isByMatomo"
        class="pluginCard__owner"
        :title="ownerName"
      >{{ ownerName }}</span>
    </div>

    <!-- cta-container is CTAContainer's own hook in marketplace.less, kept so its button and
         inline-alert states keep their styling inside the new card -->
    <div class="pluginCard__actions cta-container">
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
        :in-modal="false"
        @openDetailsModal="$emit('openDetails', plugin)"
        @requestTrial="$emit('requestTrial', plugin)"
        @startFreeTrial="$emit('startFreeTrial', plugin)"
      />
    </div>
  </article>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MatomoUrl, translate } from 'CoreHome';
import CTAContainer from '../PluginList/CTAContainer.vue';
import MatomoGlyph from './MatomoGlyph.vue';
import { PluginCard as PluginCardType } from '../types';
import { ownerLabel, pluginCategories } from '../PluginGrid/pluginGrouping';
import { categoryLabel as labelForCategory } from '../PluginGrid/categoryLabels';

export interface PluginCardState {
  coverImageFailed: boolean;
}

export default defineComponent({
  props: {
    plugin: {
      type: Object as PropType<PluginCardType>,
      required: true,
    },
    isAutoUpdatePossible: { type: Boolean, required: true },
    isSuperUser: { type: Boolean, required: true },
    isValidConsumer: { type: Boolean, required: true },
    isMultiServerEnvironment: { type: Boolean, required: true },
    isPluginsAdminEnabled: { type: Boolean, required: true },
    activateNonce: { type: String, required: true },
    deactivateNonce: { type: String, required: true },
    installNonce: { type: String, required: true },
    updateNonce: { type: String, required: true },
  },
  components: {
    CTAContainer,
    MatomoGlyph,
  },
  emits: ['openDetails', 'requestTrial', 'startFreeTrial'],
  data(): PluginCardState {
    return {
      coverImageFailed: false,
    };
  },
  watch: {
    'plugin.coverImage': function onCoverImageChange() {
      this.coverImageFailed = false;
    },
  },
  computed: {
    isByMatomo(): boolean {
      return ownerLabel(this.plugin) === 'Matomo';
    },
    ownerName(): string {
      // Marketplace_CreatedBy has no placeholder, so passing the name to it drops it
      return translate('Marketplace_ByAuthor', ownerLabel(this.plugin));
    },
    categoryLabel(): string {
      if (this.plugin.isBundle) {
        return translate('Marketplace_Bundles');
      }

      // the chip has room for one, so it names the first slug the plugin is filed under
      return labelForCategory(pluginCategories(this.plugin)[0] ?? '');
    },
    bundleSeatsLabel(): string {
      // Plugins::addBundleSeats() only sets this for a bundle, and only when the seat tier the
      // card prices itself from names a number - a bundle sold as "Unlimited users." has none.
      if (!this.plugin.isBundle || !this.plugin.bundleSeats) {
        return '';
      }

      return translate('Marketplace_BundleUpToXUsers', String(this.plugin.bundleSeats));
    },
    detailsHref(): string {
      return `#?${MatomoUrl.stringify({
        ...MatomoUrl.hashParsed.value,
        showPlugin: this.plugin.name,
      })}`;
    },
    coverImageSrcset(): string {
      // covers are published at 880x480 and the Marketplace resizes to whatever is asked for, so a
      // card on a non-retina display was downloading around three times the pixels it renders
      return `${this.coverImageUrl(440, 240)} 440w, ${this.coverImageUrl(880, 480)} 880w`;
    },
  },
  methods: {
    translate,
    coverImageUrl(width: number, height: number): string {
      return `${this.plugin.coverImage}?w=${width}&h=${height}`;
    },
  },
});
</script>
