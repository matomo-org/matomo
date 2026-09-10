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
        :placeholder="translate('Marketplace_SearchPlaceholder')"
        :aria-label="translate('Marketplace_SearchPlaceholder')"
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
  },
  components: {
    SearchInput,
  },
  emits: ['update:modelValue'],
  methods: {
    translate,
  },
});
</script>
