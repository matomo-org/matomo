<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="marketplaceEmptyState">
    <span class="marketplaceEmptyState__icon icon-search" aria-hidden="true" />
    <p class="marketplaceEmptyState__message">{{ translate('Marketplace_NoPluginsFound') }}</p>
    <button
      type="button"
      class="marketplaceEmptyState__reset"
      @click="$emit('reset')"
    >{{ translate('Marketplace_ResetFilters') }}</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';

export default defineComponent({
  emits: ['reset'],
  methods: {
    translate,
  },
});
</script>
