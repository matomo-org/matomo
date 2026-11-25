<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCardContainer">
    <div
      class="kpiCardContainer__list"
      :class="`kpiCardContainer__list--cols${columnCount}`"
    >
      <div v-if="isLoading" class="kpiCardContainer__loading">
        <MatomoLoader />
      </div>

      <template
        v-else
        v-for="(kpi, index) in kpis"
        :key="`kpi-card-${index}`"
      >
        <div
          class="kpiCardContainer__item"
          :class="{ 'kpiCardContainer__item--separated': index > 0 }"
        >
          <KPICard :model-value="kpi" />
        </div>
      </template>
    </div>
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
