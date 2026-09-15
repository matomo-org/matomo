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
    >{{ resetLabel }}</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';

export default defineComponent({
  props: {
    /** Whether anything was searched for. A category on its own is cleared, not "searched". */
    hasQuery: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['reset'],
  computed: {
    resetLabel(): string {
      return this.hasQuery
        ? translate('Marketplace_ResetFilters')
        : translate('Marketplace_ShowAllPlugins');
    },
  },
  methods: {
    translate,
  },
});
</script>
