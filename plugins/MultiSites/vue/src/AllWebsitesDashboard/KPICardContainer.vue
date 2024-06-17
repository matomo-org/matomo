<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCardContainer">
    <template v-if="isLoading">
      <div class="kpiCard kpiCardLoading">
        <div class="kpiCardTitle">&nbsp;</div>
        <div class="kpiCardValue">
          <MatomoLoader />
        </div>
        <div class="kpiCardEvolution">
          <span class="kpiCardEvolutionTrend">&nbsp;</span>
        </div>
      </div>
    </template>

    <template
        v-else
        v-for="(kpi, index) in kpis"
        :key="`kpi-card-${index}`"
    >
      <template v-if="index > 0">
        <div class="kpiCardDivider">&nbsp;</div>
      </template>

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
    kpis(): KPICardData[] {
      return this.modelValue as KPICardData[];
    },
  },
});
</script>
