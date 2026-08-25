<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- `dataTablePeriods` on the list and `tableIcon` on each entry are what dataTable.js binds the
       period change to, so they travel with this markup wherever it is rendered. -->
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
      </a>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

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
    // The item is not a link, so a key press has to click it for the delegated handler to hear.
    activateItem(event: KeyboardEvent) {
      (event.currentTarget as HTMLElement | null)?.click();
    },
  },
});
</script>
