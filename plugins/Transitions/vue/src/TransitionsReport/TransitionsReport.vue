<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsReport">
    <ActivityIndicator :loading="isLoading" />

    <div class="transitionsReport__error" v-if="error && !isLoading">
      <p class="transitionsReport__errorTitle" v-html="$sanitize(error.title)"></p>
      <p class="transitionsReport__errorMessage" v-if="error.message">{{ error.message }}</p>
      <a
        class="transitionsReport__errorBack"
        v-if="context === 'popover'"
        href="#"
        @click.prevent="goBack"
      >{{ error.backLabel }}</a>
    </div>

    <div class="transitionsReport__grid" v-if="report && !isLoading && !error">
      <div class="transitionsReport__column" ref="incomingColumn">
        <TransitionsColumn
          side="incoming"
          :sections="incomingSections"
          :highlighted-keys="highlightedKeys"
          @open="onOpen('incoming', $event)"
          @highlight="onRowHighlight"
          @unhighlight="highlightedGroup = ''"
          @navigate="onNavigate"
        />
      </div>

      <div class="transitionsReport__ribbons">
        <TransitionsRibbons
          side="incoming"
          :rows="incomingRibbonRows"
          :column="() => $refs.incomingColumn"
          :center="centerCardElement"
          :highlighted-keys="highlightedKeys"
        />
      </div>

      <div class="transitionsReport__center">
        <TransitionsCenterCard
          ref="centerCard"
          :report="report"
          :highlighted-group="highlightedGroup"
          @open="onOpenFromCard"
          @highlight="highlightedGroup = $event"
          @unhighlight="highlightedGroup = ''"
        />
      </div>

      <div class="transitionsReport__ribbons">
        <TransitionsRibbons
          side="outgoing"
          :rows="outgoingRibbonRows"
          :column="() => $refs.outgoingColumn"
          :center="centerCardElement"
          :highlighted-keys="highlightedKeys"
        />
      </div>

      <div class="transitionsReport__column" ref="outgoingColumn">
        <TransitionsColumn
          side="outgoing"
          :sections="outgoingSections"
          :highlighted-keys="highlightedKeys"
          @open="onOpen('outgoing', $event)"
          @highlight="onRowHighlight"
          @unhighlight="highlightedGroup = ''"
          @navigate="onNavigate"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ActivityIndicator,
  Matomo,
  NumberFormatter,
  translate,
} from 'CoreHome';
import TransitionsCenterCard from './TransitionsCenterCard.vue';
import TransitionsColumn from './TransitionsColumn.vue';
import TransitionsRibbons from './TransitionsRibbons.vue';
import { useTransitionsData } from './useTransitionsData';
import { RibbonSource } from './useRibbonGeometry';
import {
  TransitionsContext,
  TransitionsGroupData,
  TransitionsSectionData,
  TransitionsSide,
} from './types';

export interface TransitionsReportState {
  openGroups: Record<TransitionsSide, string>;
  highlightedGroup: string;
}

export default defineComponent({
  props: {
    actionType: {
      type: String,
      required: true,
    },
    actionName: {
      type: String,
      required: true,
    },
    /**
     * segment/date/period/idSite the report should be fetched for, as parsed from the row action
     * link. Without these an Overlay or deep-linked popover would fetch the wrong data.
     */
    overrideParams: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
    context: {
      type: String as PropType<TransitionsContext>,
      default: 'embedded',
    },
  },
  components: {
    ActivityIndicator,
    TransitionsCenterCard,
    TransitionsColumn,
    TransitionsRibbons,
  },
  emits: ['navigate'],
  setup() {
    const { isLoading, error, report, load } = useTransitionsData();

    return {
      isLoading,
      error,
      report,
      load,
    };
  },
  data(): TransitionsReportState {
    return {
      openGroups: {
        incoming: 'previousPages',
        outgoing: 'followingPages',
      },
      highlightedGroup: '',
    };
  },
  created() {
    this.reload();
  },
  watch: {
    actionType() {
      this.reload();
    },
    actionName() {
      this.reload();
    },
    overrideParams: {
      deep: true,
      handler() {
        this.reload();
      },
    },
  },
  computed: {
    incomingSections(): TransitionsSectionData[] {
      return this.sectionsFor('incoming');
    },
    outgoingSections(): TransitionsSectionData[] {
      return this.sectionsFor('outgoing');
    },
    incomingRibbonRows(): RibbonSource[] {
      return this.ribbonRowsFor('incoming');
    },
    outgoingRibbonRows(): RibbonSource[] {
      return this.ribbonRowsFor('outgoing');
    },
    /** Ribbon keys belonging to the highlighted group, so its bands can be emphasised. */
    highlightedKeys(): string[] {
      if (!this.highlightedGroup) {
        return [];
      }

      const group = (this.report?.groups || []).find(
        (candidate) => candidate.name === this.highlightedGroup,
      );

      if (!group) {
        return [];
      }

      return this.openGroups[group.side] === group.name && group.canExpand
        ? group.rows.map((row) => row.key)
        : [group.name];
    },
  },
  methods: {
    reload() {
      this.openGroups = { incoming: 'previousPages', outgoing: 'followingPages' };
      this.highlightedGroup = '';
      this.load(this.actionType, this.actionName, this.overrideParams);
    },
    groupsFor(side: TransitionsSide): TransitionsGroupData[] {
      return (this.report?.groups || [])
        .filter((group) => group.side === side && group.nbTransitions > 0);
    },
    /**
     * A column holds at most two blocks: the open group, listed row by row under its own title,
     * and everything else on that side, one summary row per group. The incoming catch-all is
     * titled "Other sources"; the outgoing one needs no title, its rows name themselves.
     */
    sectionsFor(side: TransitionsSide): TransitionsSectionData[] {
      const groups = this.groupsFor(side);
      const openGroup = groups.find(
        (group) => group.name === this.openGroups[side] && group.canExpand,
      );

      const sections: TransitionsSectionData[] = [];

      if (openGroup) {
        sections.push({
          key: openGroup.name,
          side,
          title: openGroup.title,
          showHeading: true,
          badge: openGroup.countLabel,
          rows: openGroup.rows,
        });
      }

      const rest = groups.filter((group) => group !== openGroup);
      if (rest.length) {
        const total = rest.reduce((sum, group) => sum + group.nbTransitions, 0);

        sections.push({
          key: `${side}-other`,
          side,
          title: side === 'incoming' ? translate('Transitions_OtherSources') : '',
          showHeading: side === 'incoming',
          badge: translate('Transitions_NumPageviews', NumberFormatter.formatNumber(total)),
          rows: rest.map((group) => group.summaryRow),
        });
      }

      return sections;
    },
    ribbonRowsFor(side: TransitionsSide): RibbonSource[] {
      return this.sectionsFor(side).flatMap(
        (section) => section.rows.map((row) => ({ key: row.key, share: row.share })),
      );
    },
    /**
     * The ribbons meet the card itself, not the grid cell around it, so the band lines up with
     * what the reader sees rather than with the cell's padding.
     */
    centerCardElement(): HTMLElement|null {
      const card = this.$refs.centerCard as { $el?: HTMLElement }|undefined;
      return card?.$el ?? null;
    },
    onOpen(side: TransitionsSide, groupName: string) {
      this.openGroups = { ...this.openGroups, [side]: groupName };
      this.highlightedGroup = '';
    },
    onOpenFromCard(groupName: string) {
      const group = (this.report?.groups || []).find(
        (candidate) => candidate.name === groupName,
      );

      if (group?.canExpand) {
        this.onOpen(group.side, groupName);
      }
    },
    /** A row highlights the group it stands for, or the group its detail rows belong to. */
    onRowHighlight(row: { key: string; kind: string }) {
      if (row.kind === 'summary') {
        this.highlightedGroup = row.key;
        return;
      }

      const [groupName] = row.key.split('-');
      this.highlightedGroup = groupName;
    },
    onNavigate(url: string) {
      this.$emit('navigate', url);

      if (this.context === 'popover') {
        // Mounted through a vue-entry, so the row action cannot pass a handler in; it listens for
        // this instead and re-opens the popover, which keeps the popover URL in sync.
        Matomo.postEvent('Transitions.reloadPopover', { url });
        return;
      }

      Matomo.postEvent('Transitions.switchTransitionsUrl', { url });
    },
    /**
     * Offered in the popover only, where a history step closes the popover and lands back on the
     * report behind it. On the Transitions page the same step would navigate away from the page
     * altogether, which is why the legacy renderer left the link out of its inline error too.
     */
    goBack() {
      window.history.back();
    },
  },
});
</script>
