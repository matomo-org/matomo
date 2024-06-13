<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCardContainer">
    <template
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

import KPICard from './KPICard.vue';
import { KPICardData } from '../types';

export default defineComponent({
  components: {
    KPICard,
  },
  props: {
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
