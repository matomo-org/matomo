<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <button
    v-if="canShowMovePeriod"
    class="move-period move-period-prev"
    @click="movePeriod(-1)"
    :disabled="isPeriodMoveDisabled(-1)"
  >
    <span class="icon-chevron-left"></span>
  </button>

  <button
    ref="title"
    id="date"
    class="title"
    tabindex="4"
    v-tooltips
    :title="translate('General_ChooseDate', currentlyViewingText)"
  >
    <span class="icon icon-calendar" />
    {{ currentlyViewingText }}
  </button>

  <button
    v-if="canShowMovePeriod"
    class="move-period move-period-next"
    @click="movePeriod(1)"
    :disabled="isPeriodMoveDisabled(1)"
  >
    <span class="icon-chevron-right"></span>
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Periods,
  format,
  datesAreInTheSamePeriod,
} from '../Periods';
import Tooltips from '../Tooltips/Tooltips';
import { translate } from '../translate';
import {
  RANGE_PERIOD,
  getSiteMaxAllowedDate,
  getSiteMinAllowedDate,
} from './PeriodSelector.types';

export default defineComponent({
  name: 'PeriodSelectorTrigger',
  directives: {
    Tooltips,
  },
  props: {
    committedPeriod: {
      type: String,
      required: true,
    },
    committedAnchorDate: {
      type: Date as PropType<Date|null>,
      default: null,
    },
    appliedRangeStartDate: {
      type: String as PropType<string|null>,
      default: null,
    },
    appliedRangeEndDate: {
      type: String as PropType<string|null>,
      default: null,
    },
  },
  emits: ['move-period'],
  computed: {
    currentlyViewingText() {
      let selectedDateValue;
      if (this.committedPeriod === RANGE_PERIOD) {
        if (!this.appliedRangeStartDate || !this.appliedRangeEndDate) {
          return translate('General_Error');
        }

        selectedDateValue = `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`;
      } else {
        if (!this.committedAnchorDate) {
          return translate('General_Error');
        }

        selectedDateValue = format(this.committedAnchorDate);
      }

      try {
        return Periods.parse(this.committedPeriod, selectedDateValue).getPrettyString();
      } catch (e) {
        return translate('General_Error');
      }
    },
    isErrorDisplayed() {
      return this.currentlyViewingText === translate('General_Error');
    },
    isRangeSelection() {
      return this.committedPeriod === RANGE_PERIOD;
    },
    canShowMovePeriod() {
      return !this.isRangeSelection && !this.isErrorDisplayed;
    },
  },
  methods: {
    translate,
    focusTitle() {
      const titleRef = this.$refs.title as HTMLElement | undefined;
      if (titleRef) {
        titleRef.focus();
      }
    },
    movePeriod(direction: number) {
      if (!this.canMovePeriod(direction)) {
        return;
      }

      this.$emit('move-period', { direction });
    },
    isPeriodMoveDisabled(direction: number) {
      if (this.committedAnchorDate === null) {
        return this.isRangeSelection;
      }

      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction: number) {
      if (this.committedAnchorDate === null) {
        return false;
      }

      const siteMinAllowedDate = getSiteMinAllowedDate();
      const siteMaxAllowedDate = getSiteMaxAllowedDate();
      const relevantBoundaryDate = direction === -1 ? siteMinAllowedDate : siteMaxAllowedDate;
      return !datesAreInTheSamePeriod(
        this.committedAnchorDate,
        relevantBoundaryDate,
        this.committedPeriod,
      );
    },
  },
});
</script>
