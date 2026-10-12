<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <section class="marketplaceHero">
    <h1 class="marketplaceHero__title">{{ translate('Marketplace_MatomoMarketplace') }}</h1>
    <p class="marketplaceHero__subtitle">{{ translate('Marketplace_IntroShort') }}</p>
    <div class="marketplaceHero__search">
      <SearchInput
        :model-value="modelValue"
        :show-clear="true"
        :placeholder="placeholder"
        :aria-label="placeholder"
        @update:model-value="$emit('update:modelValue', $event)"
      />
    </div>
  </section>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { SearchInput, translate } from 'CoreHome';

export default defineComponent({
  props: {
    modelValue: {
      type: String,
      required: true,
    },
    /** How many plugins the search covers. Zero while the catalogue is still on its way. */
    pluginCount: {
      type: Number,
      default: 0,
    },
  },
  components: {
    SearchInput,
  },
  emits: ['update:modelValue'],
  computed: {
    /**
     * Names the real size of the catalogue rather than a number written into the translation, and
     * leaves it out entirely until the catalogue is here - "Search 0 plugins and themes" would
     * otherwise be what the reader sees for the length of the request.
     */
    placeholder(): string {
      return this.pluginCount > 0
        ? translate('Marketplace_SearchPlaceholderWithCount', String(this.pluginCount))
        : translate('Marketplace_SearchPlaceholder');
    },
  },
  methods: {
    translate,
  },
});
</script>
