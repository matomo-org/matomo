<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <component
    :is="isActionable ? 'a' : 'div'"
    class="transitionsRow"
    :class="rowClasses"
    :data-ribbon-key="row.key"
    :href="href"
    :target="safeExternalUrl ? '_blank' : null"
    :rel="safeExternalUrl ? 'noreferrer noopener' : null"
    @click="onClick"
    @mouseenter="$emit('highlight')"
    @mouseleave="$emit('unhighlight')"
  >
    <span class="transitionsRow__icon" aria-hidden="true">
      <span class="transitionsRow__glyph" :class="[row.icon, glyphClass]"></span>
    </span>

    <span class="transitionsRow__body">
      <span class="transitionsRow__label" :title="row.fullLabel || row.label">{{ row.label }}</span>
      <span class="transitionsRow__count" v-if="isAction">{{ row.countLabel }}</span>
    </span>

    <span class="transitionsRow__figures">
      <span class="transitionsRow__total" v-if="!isAction">{{ row.countLabel }}</span>
      <span class="transitionsRow__pill" :class="{ 'transitionsRow__pill--muted': !isAction }">
        {{ isAction ? row.percentage : `(${row.percentage})` }}
      </span>
    </span>
  </component>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { TransitionsRowData, TransitionsSide } from './types';

export default defineComponent({
  props: {
    row: {
      type: Object as PropType<TransitionsRowData>,
      required: true,
    },
    side: {
      type: String as PropType<TransitionsSide>,
      required: true,
    },
    highlighted: Boolean,
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open'],
  computed: {
    isAction(): boolean {
      return this.row.kind === 'action';
    },
    /**
     * The icon carries its side's accent, so a row reads as belonging to its half of the report.
     */
    glyphClass(): string {
      return this.side === 'outgoing'
        ? 'transitionsRow__glyph--outgoing'
        : 'transitionsRow__glyph--incoming';
    },
    /**
     * Row labels are tracked URLs, so they can be any string. Only a value DOMPurify accepts as an
     * href reaches the link; anything else renders as a plain row.
     */
    safeExternalUrl(): string {
      return this.row.externalUrl ? this.$sanitizeUrl(this.row.externalUrl) : '';
    },
    isActionable(): boolean {
      return !!(this.safeExternalUrl || this.row.transitionUrl || this.row.opensGroup);
    },
    /**
     * Every actionable row is an anchor, so it sits in the tab order and Enter activates it. Only
     * an outlink has a real destination; the rest carry `#` because an anchor without an href is
     * not focusable, and onClick keeps that `#` from ever reaching the address bar.
     */
    href(): string|null {
      if (!this.isActionable) {
        return null;
      }

      return this.safeExternalUrl || '#';
    },
    rowClasses(): Record<string, boolean> {
      return {
        'transitionsRow--outgoing': this.side === 'outgoing',
        'transitionsRow--actionable': this.isActionable,
        'transitionsRow--highlighted': this.highlighted,
        'transitionsRow--others': this.row.isOthers,
        'transitionsRow--summary': !this.isAction,
      };
    },
  },
  methods: {
    onClick(event: MouseEvent) {
      // An outlink is a real link, so let the browser follow it.
      if (this.safeExternalUrl) {
        return;
      }

      // The others are anchors only to be focusable, so their `#` must not be followed: on a
      // reporting page the hash is the route, and following it would navigate away.
      event.preventDefault();

      if (this.row.transitionUrl) {
        this.$emit('navigate', this.row.transitionUrl);
        return;
      }

      if (this.row.opensGroup) {
        this.$emit('open', this.row.opensGroup);
      }
    },
  },
});
</script>
