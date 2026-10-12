<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCardContainer" :class="`kpiCardContainer--cols${columnCount}`">
    <template v-if="isLoading">
      <div class="kpiCard kpiCard--loading">
        <div class="kpiCard__badge kpiCard__badge--empty" aria-hidden="true" />
        <div class="kpiCard__title">&nbsp;</div>
        <div class="kpiCard__value">
          <MatomoLoader />
        </div>
        <div class="kpiCard__evolution">&nbsp;</div>
      </div>
    </template>

    <template
      v-else
      v-for="(kpi, index) in kpis"
      :key="`kpi-card-${index}`"
    >
      <KPICard :model-value="kpi" />
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoLoader } from 'CoreHome';

import KPICard from './KPICard.vue';
import { KPICardData } from '../types';

export default defineComponent({
  components: {
    MatomoLoader,
    KPICard,
  },
  props: {
    isLoading: Boolean,
    modelValue: {
      type: Array,
      required: true,
    },
  },
  computed: {
    columnCount(): number {
      return this.isLoading ? 1 : (this.kpis.length || 1);
    },
    kpis(): KPICardData[] {
      return this.modelValue as KPICardData[];
    },
  },
});
</script>
