<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- dataTable.js binds the period change on `dataTablePeriods` + `tableIcon`. -->
  <ul class="mtm-dropdownPanel__menu dataTablePeriods" role="menu">
    <li
      v-for="selectablePeriod in selectablePeriods"
      :key="selectablePeriod"
      class="mtm-dropdownPanel__menuItem"
      role="none"
    >
      <a
        :data-period="selectablePeriod"
        role="menuitem"
        tabindex="0"
        :aria-current="activePeriod === selectablePeriod"
        :class="`mtm-dropdownPanel__menuLink tableIcon ${activePeriod === selectablePeriod
          ? 'activeIcon' : ''}`"
        @click="$emit('pick')"
        @keydown.enter.prevent="activateItem"
        @keydown.space.prevent="activateItem"
      >
        <span class="mtm-dropdownPanel__menuLabel">
          {{ labels[selectablePeriod] || selectablePeriod }}
        </span>
        <span
          v-if="activePeriod === selectablePeriod"
          class="mtm-dropdownPanel__rightIcon"
          aria-hidden="true"
        ><span class="icon-ok" /></span>
      </a>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import activateMenuItem from './activateMenuItem';

export default defineComponent({
  props: {
    selectablePeriods: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    activePeriod: {
      type: String,
      default: '',
    },
    labels: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
  },
  emits: ['pick'],
  methods: {
    activateItem: activateMenuItem,
  },
});
</script>
